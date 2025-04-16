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
    public function showPoJual()
    {
        $penjualan = DraftPenjualan::with(['pelanggan', 'karyawan'])
            ->orderBy('id_penjualan', 'desc')
            ->get();
        
        return view('pabrik.po-jual', [
            'penjualan' => $penjualan
        ]);
    }
    
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
            'customer_id' => 'required|exists:pelanggan,id_pelanggan',
            'employee_id' => 'required|exists:karyawan,id_karyawan',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item,id_item',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Hitung total harga penjualan
            $totalPrice = 0;
            
            // Verifikasi dan hitung total harga dari setiap item
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                $totalPrice += $subtotal;
            }
            
            // Simpan data penjualan
            $draftPenjualan = new DraftPenjualan();
            $draftPenjualan->id_pelanggan = $request->customer_id;
            $draftPenjualan->tanggal_penjualan = date('Y-m-d');
            $draftPenjualan->total_harga_penjualan = $totalPrice;
            $draftPenjualan->id_karyawan = $request->employee_id;
            $draftPenjualan->save();
            
            // Simpan detail penjualan untuk setiap item
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                
                $draftDetailPenjualan = new DraftDetailPenjualan();
                $draftDetailPenjualan->id_penjualan = $draftPenjualan->id_penjualan;
                $draftDetailPenjualan->id_item = $item['item_id'];
                $draftDetailPenjualan->jumlah_jual = $quantity;
                $draftDetailPenjualan->harga_jual_satuan = $price;
                $draftDetailPenjualan->subtotal_harga = $subtotal;
                $draftDetailPenjualan->save();
            }
            
            DB::commit();
            
            return redirect()->route('pabrik.po-jual')
                ->with('success', 'PO Penjualan berhasil dibuat!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    public function cancelPoJual($id)
    {
        try {
            $draftPenjualan = DraftPenjualan::findOrFail($id);
    
            // Delete related draft details
            DraftDetailPenjualan::where('id_penjualan', $id)->delete();
    
            // Delete the draft penjualan (PO)
            $draftPenjualan->delete();
    
            return redirect()->route('pabrik.po-jual')->with('success', 'PO Penjualan berhasil dibatalkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
}