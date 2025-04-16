<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Pelanggan;
use App\Models\Karyawan;
use App\Models\DraftPenjualan;
use App\Models\DraftDetailPenjualan;
use Illuminate\Support\Facades\DB;

class PabrikController extends Controller
{    
    public function createPoJual()
    {
        // Ambil data untuk dropdown
        $items = Item::all();
        $pelanggan = Pelanggan::all();
        $karyawan = Karyawan::all();
        
        return view('pabrik.create-po-jual', [
            'items' => $items,
            'pelanggan' => $pelanggan,
            'karyawan' => $karyawan
        ]);
    }
    
    public function storePoJual(Request $request)
    {
        // Validasi input
        $request->validate([
            'item_id' => 'required|exists:item,id_item',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'customer_id' => 'required|exists:pelanggan,id_pelanggan',
            'employee_id' => 'required|exists:karyawan,id_karyawan',
        ]);
        
        // Hitung subtotal dan total
        $subtotal = $request->quantity * $request->unit_price;
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Simpan data penjualan
            $draftPenjualan = new DraftPenjualan();
            $draftPenjualan->id_pelanggan = $request->customer_id;
            $draftPenjualan->tanggal_penjualan = date('Y-m-d');
            $draftPenjualan->total_harga_penjualan = $subtotal; // Asumsi tidak ada diskon
            $draftPenjualan->id_karyawan = $request->employee_id;
            $draftPenjualan->save();
            
            // Simpan detail penjualan
            $draftDetailPenjualan = new DraftDetailPenjualan();
            $draftDetailPenjualan->id_penjualan = $draftPenjualan->id_penjualan;
            $draftDetailPenjualan->id_item = $request->item_id;
            $draftDetailPenjualan->jumlah_jual = $request->quantity;
            $draftDetailPenjualan->harga_jual_satuan = $request->unit_price;
            $draftDetailPenjualan->subtotal_harga = $subtotal;
            $draftDetailPenjualan->save();
            
            DB::commit();
            
            return redirect()->route('pabrik.po-jual')
                ->with('success', 'PO Penjualan berhasil dibuat!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showPoJual()
    {
        $penjualan = DraftPenjualan::with(['pelanggan', 'karyawan'])
            ->orderBy('id_penjualan', 'desc')
            ->get();
        
        return view('pabrik.po-jual', [
            'penjualan' => $penjualan
        ]);
    }
    
    public function showDetailPoJual($id)
    {
        $penjualan = DraftPenjualan::with(['pelanggan', 'karyawan'])
            ->where('id_penjualan', $id)
            ->firstOrFail();
        
        $detailPenjualan = DraftDetailPenjualan::with('item')
            ->where('id_penjualan', $id)
            ->get();
        
        return view('pabrik.po-jual-detail', [
            'penjualan' => $penjualan,
            'detailPenjualan' => $detailPenjualan
        ]);
    }
}