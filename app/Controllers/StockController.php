<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class StockController extends Controller
{
    public function index(): void
    {
        $products = DB::all(
            'SELECT p.*,
                    COALESCE(SUM(CASE WHEN b.expiry_date >= CURDATE() THEN b.qty_remaining END),0) AS in_stock,
                    COALESCE(SUM(CASE WHEN b.expiry_date <  CURDATE() THEN b.qty_remaining END),0) AS expired,
                    MIN(CASE WHEN b.qty_remaining > 0 THEN b.expiry_date END) AS next_expiry
               FROM products p
               LEFT JOIN batches b ON b.product_id = p.id
              GROUP BY p.id
              ORDER BY p.type, p.name'
        );

        $batches = DB::all(
            'SELECT b.*, p.name, p.brand, p.unit, s.name AS supplier_name,
                    DATEDIFF(b.expiry_date, CURDATE()) AS days_left
               FROM batches b
               JOIN products p ON p.id = b.product_id
               LEFT JOIN suppliers s ON s.id = b.supplier_id
              ORDER BY b.expiry_date ASC'
        );

        $this->view('stock/index', [
            'title'    => 'Stock & vials',
            'products' => $products,
            'batches'  => $batches,
        ]);
    }

    public function createProduct(): void
    {
        $this->view('stock/product_form', ['title' => 'New product']);
    }

    public function storeProduct(): void
    {
        Csrf::verify();
        $v = $this->validate(['name' => 'required|max:140'], ['name' => 'Product name']);
        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/stock/products/create');
        }

        $id = DB::insert('products', [
            'name'          => $this->input('name'),
            'brand'         => $this->input('brand') ?: null,
            'type'          => $this->input('type', 'consumable'),
            'unit'          => $this->input('unit', 'unit'),
            'unit_cost'     => $this->floatInput('unit_cost'),
            'reorder_level' => $this->floatInput('reorder_level'),
        ]);
        Audit::log('create', 'product', $id);
        Flash::add('success', 'Product added.');
        $this->redirect('/stock');
    }

    public function createBatch(): void
    {
        $this->view('stock/batch_form', [
            'title'     => 'Receive stock',
            'products'  => DB::all('SELECT * FROM products WHERE is_active = 1 ORDER BY name'),
            'suppliers' => DB::all('SELECT * FROM suppliers ORDER BY name'),
        ]);
    }

    public function storeBatch(): void
    {
        Csrf::verify();
        $v = $this->validate([
            'product_id'   => 'required|numeric',
            'lot_number'   => 'required|max:60',
            'expiry_date'  => 'required|date',
            'qty_received' => 'required|numeric',
        ], ['lot_number' => 'Lot number', 'qty_received' => 'Quantity']);

        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/stock/batches/create');
        }

        $qty = $this->floatInput('qty_received');
        $id = DB::insert('batches', [
            'product_id'    => $this->intInput('product_id'),
            'supplier_id'   => $this->intInput('supplier_id') ?: null,
            'lot_number'    => $this->input('lot_number'),
            'expiry_date'   => $this->input('expiry_date'),
            'qty_received'  => $qty,
            'qty_remaining' => $qty,
            'received_at'   => $this->input('received_at') ?: date('Y-m-d'),
        ]);
        DB::insert('stock_movements', [
            'batch_id' => $id, 'type' => 'in', 'quantity' => $qty,
            'note' => 'Stock received', 'user_id' => Auth::id(),
        ]);

        Audit::log('receive', 'batch', $id, $this->input('lot_number'));
        Flash::add('success', 'Batch received and added to stock.');
        $this->redirect('/stock');
    }

    public function waste(): void
    {
        Csrf::verify();
        $batchId = $this->intInput('batch_id');
        $qty     = $this->floatInput('quantity');

        if (!$batchId || $qty <= 0) {
            Flash::add('error', 'Choose a batch and a quantity.');
            $this->redirect('/stock');
        }

        DB::transaction(function () use ($batchId, $qty) {
            DB::run('UPDATE batches SET qty_remaining = GREATEST(qty_remaining - ?, 0) WHERE id = ?',
                [$qty, $batchId]);
            DB::insert('stock_movements', [
                'batch_id' => $batchId, 'type' => 'waste', 'quantity' => $qty,
                'note' => $this->input('note') ?: 'Wastage', 'user_id' => Auth::id(),
            ]);
        });

        Audit::log('waste', 'batch', $batchId, $qty . ' units');
        Flash::add('success', 'Wastage recorded.');
        $this->redirect('/stock');
    }

    public function movements(): void
    {
        $this->view('stock/movements', [
            'title' => 'Stock movements',
            'movements' => DB::all(
                'SELECT m.*, b.lot_number, p.name, p.brand, p.unit, u.full_name AS user_name
                   FROM stock_movements m
                   JOIN batches b ON b.id = m.batch_id
                   JOIN products p ON p.id = b.product_id
                   LEFT JOIN users u ON u.id = m.user_id
                  ORDER BY m.created_at DESC, m.id DESC LIMIT 200'
            ),
        ]);
    }
}
