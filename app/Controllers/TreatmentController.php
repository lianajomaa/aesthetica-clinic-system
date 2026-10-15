<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class TreatmentController extends Controller
{
    public function index(): void
    {
        $this->view('treatments/index', [
            'title' => 'Treatment menu',
            'treatments' => DB::all(
                'SELECT t.*, ac.title AS aftercare_title,
                        (SELECT COUNT(*) FROM treatment_records r WHERE r.treatment_id = t.id) AS performed
                   FROM treatments t
                   LEFT JOIN aftercare_templates ac ON ac.id = t.aftercare_id
                  ORDER BY t.category, t.name'
            ),
        ]);
    }

    public function create(): void
    {
        $this->view('treatments/form', [
            'title' => 'New treatment', 'treatment' => null, 'aftercare' => $this->aftercare(),
        ]);
    }

    public function store(): void
    {
        Csrf::verify();
        $v = $this->validate(
            ['name' => 'required|max:140', 'duration_min' => 'required|numeric', 'price' => 'numeric'],
            ['duration_min' => 'Duration']
        );
        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/treatments/create');
        }

        $id = DB::insert('treatments', $this->fields());
        Audit::log('create', 'treatment', $id, $this->input('name'));
        Flash::add('success', 'Treatment added to the menu.');
        $this->redirect('/treatments');
    }

    public function edit(int $id): void
    {
        $treatment = DB::one('SELECT * FROM treatments WHERE id = ?', [$id]);
        if (!$treatment) {
            $this->notFound('Treatment');
        }
        $this->view('treatments/form', [
            'title' => 'Edit treatment', 'treatment' => $treatment, 'aftercare' => $this->aftercare(),
        ]);
    }

    public function update(int $id): void
    {
        Csrf::verify();
        DB::update('treatments', $id, $this->fields());
        Audit::log('update', 'treatment', $id);
        Flash::add('success', 'Treatment updated.');
        $this->redirect('/treatments');
    }

    private function fields(): array
    {
        return [
            'name'             => $this->input('name'),
            'category'         => $this->input('category', 'other'),
            'duration_min'     => $this->intInput('duration_min', 30),
            'price'            => $this->floatInput('price'),
            'price_type'       => $this->input('price_type', 'fixed'),
            'requires_consent' => isset($_POST['requires_consent']) ? 1 : 0,
            'followup_days'    => $this->intInput('followup_days') ?: null,
            'recall_days'      => $this->intInput('recall_days') ?: null,
            'aftercare_id'     => $this->intInput('aftercare_id') ?: null,
            'description'      => $this->input('description') ?: null,
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function aftercare(): array
    {
        return DB::all('SELECT id, title FROM aftercare_templates ORDER BY title');
    }
}
