# Aesthetica — Clinic Management System

A management system for **aesthetic and injectable clinics** — botox, dermal fillers, PDO threads
and skin boosters. Built in PHP 8 and MySQL, without a framework.

Generic booking software stops at *"an appointment happened"*. An injectables clinic cannot work
that way: it has to know **which product, from which vial, at which injection site, in what dose** —
and it has to prove consent was taken before any of it.

That is what this system is built around.

---

## Screens

| | |
|---|---|
| **Dashboard** | Today's list, revenue, toxin units used this month, and an alerts panel: unsigned consents, batches expiring, low stock, overdue reviews |
| **Diary** | Day view per practitioner, with slot conflict prevention |
| **Patients** | Record, medical history, treatment history, photos, consents, invoices |
| **Treatment records** | Dose per injection site, traced to a batch and lot number |
| **Consent forms** | Signed, versioned, and stored with the exact wording agreed |
| **Follow-ups** | Two-week reviews and recalls, raised automatically |
| **Adverse events** | Complications linked to the treatment and the batch used |
| **Stock & vials** | Batches, lot numbers, expiry dates, wastage, movement history |
| **Invoices** | Line items, part payments, printable |
| **Reports** | Revenue, margin after product cost, practitioner performance, retention, patient source |
| **Staff, settings, audit log** | Roles, clinic details, and a record of who did what |

---

## What it does that generic booking software doesn't

**Dose recorded per injection site.** A record is not "Botox, 1 session". It is
*8 units at the glabella, 9 at the left frontalis, 10 at the right — all from lot BX-4471-A,
expiring December 2027.* If a product is ever recalled, one query finds every patient who received it.

**Stock comes off the exact vial.** Recording a treatment deducts from that specific batch and writes
a stock movement. Wastage is recorded separately, because wasted toxin is a real cost.

**Consent is enforced, not just stored.** A treatment flagged as requiring consent cannot be recorded
until a signed form exists for that patient and treatment. The form stores the wording the patient
agreed to, so the record always shows what was actually said.

**Double-booking is impossible.** Before a slot is accepted the system checks the practitioner's
working days, their hours, their booked leave, and any overlapping appointment — with the duration
taken from the treatment itself.

**Follow-ups raise themselves.** Each treatment carries a review interval and a recall interval. Record
a botox treatment and the two-week review and the four-month recall are created automatically.

**A receptionist cannot read clinical notes.** Permissions are checked per route *and* per section of
a page, so the front desk can book, check in and take payment without ever seeing a medical history
or a before-and-after photograph.

---

## Stack

- **PHP 8.1+** — no framework, no Composer, no build step
- **MySQL 8 / MariaDB 10.4+** — InnoDB, `utf8mb4`, full foreign keys
- Hand-written MVC: a router, controllers, a thin PDO layer, and plain PHP templates

Everything is a prepared statement, passwords use `password_hash()`, every state-changing form carries
a CSRF token, and every output is escaped.

---

## Installing it locally

**1 — Create the database**

```sql
CREATE DATABASE aesthetica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**2 — Import the data**

In phpMyAdmin: select the `aesthetica` database → **Import** → choose
`database/aesthetica_full.sql` → **Go**.

Or on the command line:

```bash
mysql -u root -p aesthetica < database/aesthetica_full.sql
```

That file contains the structure *and* a fully populated demo clinic: 5 staff, 3 practitioners,
14 patients, 10 treatments, 8 stock batches, 81 appointments, 45 treatment records, invoices
and payments.

**3 — Point the app at your database**

Open `config/config.php` and edit the four values under `db`:

```php
'host' => '127.0.0.1',
'name' => 'aesthetica',
'user' => 'root',
'pass' => '',
```

**4 — Run it**

```bash
php -S localhost:8000 -t public
```

Open `http://localhost:8000`.

### Sign in

| Role | Email | Password |
|---|---|---|
| Owner | `owner@aesthetica.test` | `password` |
| Doctor | `karim@aesthetica.test` | `password` |
| Nurse | `yara@aesthetica.test` | `password` |
| Receptionist | `reception@aesthetica.test` | `password` |

Sign in as the receptionist to see the permission system working — the clinical sections disappear.

---

## Putting it on shared hosting

Works on any cPanel-style host with PHP 8 and MySQL.

1. Create a MySQL database and user in cPanel, and note the credentials
2. Import `database/aesthetica_full.sql` through phpMyAdmin
3. Edit `config/config.php` with those credentials
4. Upload the whole folder
5. **Point the domain's document root at `/public`**, not at the project root

If the host won't let you change the document root, move everything inside `public/` up one level and
change the two `require` paths at the top of `index.php` from `../app/` to `app/`.

`public/.htaccess` handles the URL rewriting. Make sure `public/uploads/photos` is writable (755).

**Before going live:** change every password, and set `'debug' => false` in `config/config.php`.

---

## Project structure

```
app/
  Core/         Router, Database (PDO), Auth, Controller, View, CSRF, Validator, Audit
  Controllers/  one per module
  Views/        plain PHP templates, layouts/ and one folder per module
  routes.php    every route, with the permission it requires
  helpers.php   escaping, money, dates, status pills
config/         database and app settings
database/
  schema.sql            structure only
  seed.php              regenerates the demo data
  aesthetica_full.sql   structure + data, ready to import
public/         document root — index.php, .htaccess, assets, uploads
```

Routes declare their own permission, and the router refuses the request before the controller is ever
constructed:

```php
$router->get('/records', 'RecordController@index', 'records.view');
```

---

## Regenerating the demo data

```bash
mysql -u root -p aesthetica < database/schema.sql
php database/seed.php
```

Dates are generated relative to today, so the dashboard always shows a live-looking week.

---

Built by **Liana Zouher Jomaa** — [GitHub](https://github.com/lianajomaa) ·
[lianajomaa.vercel.app](https://lianajomaa.vercel.app)
