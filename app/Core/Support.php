<?php

namespace App\Core;

/** CSRF tokens for every state-changing form. */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function verify(): void
    {
        $sent = $_POST['_csrf'] ?? '';
        if (!is_string($sent) || !hash_equals(self::token(), $sent)) {
            http_response_code(419);
            exit('Your session expired. Please go back, reload the page and try again.');
        }
    }
}

/** One-shot messages shown after a redirect. */
final class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /** @return array<int,array{type:string,message:string}> */
    public static function take(): array
    {
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }
}

/** Minimal validation: rules are 'required', 'email', 'numeric', 'date', 'min:n', 'max:n'. */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data) {}

    public function check(array $rules, array $labels = []): self
    {
        foreach ($rules as $field => $ruleSet) {
            $value = trim((string) ($this->data[$field] ?? ''));
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            foreach (explode('|', $ruleSet) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

                $fails = match ($name) {
                    'required' => $value === '',
                    'email'    => $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
                    'numeric'  => $value !== '' && !is_numeric($value),
                    'date'     => $value !== '' && strtotime($value) === false,
                    'min'      => $value !== '' && mb_strlen($value) < (int) $arg,
                    'max'      => $value !== '' && mb_strlen($value) > (int) $arg,
                    default    => false,
                };

                if ($fails) {
                    $this->errors[$field] = match ($name) {
                        'required' => "$label is required.",
                        'email'    => "$label must be a valid email address.",
                        'numeric'  => "$label must be a number.",
                        'date'     => "$label must be a valid date.",
                        'min'      => "$label must be at least $arg characters.",
                        'max'      => "$label must be no more than $arg characters.",
                        default    => "$label is invalid.",
                    };
                    break;
                }
            }
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors ? reset($this->errors) : null;
    }
}

/** Records who did what, for clinical accountability. */
final class Audit
{
    public static function log(string $action, string $entity, ?int $entityId = null, ?string $detail = null): void
    {
        try {
            Database::insert('audit_log', [
                'user_id'    => Auth::id(),
                'action'     => $action,
                'entity'     => $entity,
                'entity_id'  => $entityId,
                'detail'     => $detail,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable) {
            // auditing must never break the request
        }
    }
}
