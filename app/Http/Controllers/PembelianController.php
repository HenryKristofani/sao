<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Pemasok;
use App\Models\Karyawan;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function edit($id)
    {
        $pembelian = Pembelian::with(['detailPembelian', 'pemasok', 'karyawan'])->findOrFail($id);
        
        if ($pembelian->status !== 'draft') {
            return redirect()->route('pabrik.po-beli.show', $id)
                ->with('error', 'Hanya PO dengan status draft yang dapat diedit.');
        }

        $pemasok = Pemasok::all();
        $karyawan = Karyawan::where('id_karyawan', 2)->get();
        $items = Item::all();
        $detailPembelian = $pembelian->detailPembelian;

        return view('pabrik.edit-po-beli', compact('pembelian', 'pemasok', 'karyawan', 'items', 'detailPembelian'));
    }

    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        
        if ($pembelian->status !== 'draft') {
            return redirect()->route('pabrik.po-beli.show', $id)
                ->with('error', 'Hanya PO dengan status draft yang dapat diedit.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:pemasok,id_pemasok',
            'employee_id' => 'required|exists:karyawan,id_karyawan',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item,id_item',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Update pembelian header
            $pembelian->update([
                'id_pemasok' => $request->supplier_id,
                'id_karyawan' => $request->employee_id,
                'tanggal_pembelian' => now()
            ]);

            // Delete existing detail
            $pembelian->detailPembelian()->delete();

            // Insert new detail
            $totalHarga = 0;
            foreach ($request->items as $item) {
                $itemData = Item::find($item['item_id']);
                $subtotal = $itemData->harga_per_item * $item['quantity'];
                $totalHarga += $subtotal;

                $pembelian->detailPembelian()->create([
                    'id_item' => $item['item_id'],
                    'jumlah_beli' => $item['quantity'],
                    'harga_beli_satuan' => $itemData->harga_per_item,
                    'subtotal_harga' => $subtotal
                ]);
            }

            // Update total harga
            $pembelian->update(['total_harga_pembelian' => $totalHarga]);

            DB::commit();
            return redirect()->route('pabrik.po-beli.show', $id)
                ->with('success', 'PO berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function editApproved($id)
    {
        $pembelian = Pembelian::with(['detailPembelian', 'pemasok', 'karyawan'])->findOrFail($id);
        
        if ($pembelian->status !== 'approved') {
            return redirect()->route('pabrik.po-beli.show', $id)
                ->with('error', 'Hanya PO dengan status approved yang dapat diedit.');
        }

        $pemasok = Pemasok::all();
        $karyawan = Karyawan::where('id_karyawan', 2)->get();
        $items = Item::all();
        $detailPembelian = $pembelian->detailPembelian;

        return view('pabrik.edit-po-beli-approved', compact('pembelian', 'pemasok', 'karyawan', 'items', 'detailPembelian'));
    }

    public function updateApproved(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        
        if ($pembelian->status !== 'approved') {
            return redirect()->route('pabrik.po-beli.show', $id)
                ->with('error', 'Hanya PO dengan status approved yang dapat diedit.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:pemasok,id_pemasok',
            'employee_id' => 'required|exists:karyawan,id_karyawan',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item,id_item',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Update pembelian header
            $pembelian->update([
                'id_pemasok' => $request->supplier_id,
                'id_karyawan' => $request->employee_id,
                'tanggal_pembelian' => now(),
                'status' => 'amended' // Change status to amended
            ]);

            // Delete existing detail
            $pembelian->detailPembelian()->delete();

            // Insert new detail
            $totalHarga = 0;
            foreach ($request->items as $item) {
                $itemData = Item::find($item['item_id']);
                $subtotal = $itemData->harga_per_item * $item['quantity'];
                $totalHarga += $subtotal;

                $pembelian->detailPembelian()->create([
                    'id_item' => $item['item_id'],
                    'jumlah_beli' => $item['quantity'],
                    'harga_beli_satuan' => $itemData->harga_per_item,
                    'subtotal_harga' => $subtotal
                ]);
            }

            // Update total harga
            $pembelian->update(['total_harga_pembelian' => $totalHarga]);

            DB::commit();
            return redirect()->route('pabrik.po-beli.show', $id)
                ->with('success', 'PO berhasil diperbarui dan status diubah menjadi amended.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 