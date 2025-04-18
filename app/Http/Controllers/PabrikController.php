<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Pelanggan;
use App\Models\Karyawan;
use App\Models\DraftPenjualan;
use App\Models\DraftDetailPenjualan;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Illuminate\Support\Facades\DB;

class PabrikController extends Controller
{
    public function showPoJual()
    {
        // Get draft POs
        $draftPenjualan = DraftPenjualan::with(['pelanggan', 'karyawan', 'detailPenjualan'])
            ->orderBy('id_penjualan', 'desc')
            ->get();
        
        // Add 'status' attribute to each draft PO
        $draftPenjualan->each(function ($item) {
            $item->status = 'draft';
            // No PO number for drafts
        });
        
        // Get approved and canceled POs
        $approvedPenjualan = Penjualan::with(['pelanggan', 'karyawan', 'detailPenjualan'])
            ->orderBy('id_penjualan', 'desc')
            ->get();
        
        // Ensure each PO has the correct status (don't override if already set)
        $approvedPenjualan->each(function ($item) {
            if (!isset($item->status) || $item->status === null) {
                $item->status = 'approved';
            }
            // Keep existing status if it's already set in the database
        });
        
        // Merge both collections and sort by id_penjualan descending
        $allPenjualan = $draftPenjualan->concat($approvedPenjualan)->sortByDesc('id_penjualan');
        
        return view('pabrik.po-jual', [
            'penjualan' => $allPenjualan
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
        // Try to find in draft first
        $penjualan = DraftPenjualan::with(['pelanggan', 'karyawan'])
            ->where('id_penjualan', $id)
            ->first();
        
        $isApproved = false;
        
        // If not found in draft, check in approved penjualan
        if (!$penjualan) {
            $penjualan = Penjualan::with(['pelanggan', 'karyawan'])
                ->where('id_penjualan', $id)
                ->firstOrFail();
            
            $detailPenjualan = DetailPenjualan::with('item')
                ->where('id_penjualan', $id)
                ->get();
                
            $isApproved = true;
        } else {
            $detailPenjualan = DraftDetailPenjualan::with('item')
                ->where('id_penjualan', $id)
                ->get();
        }
        
        return view('pabrik.po-jual-detail', [
            'penjualan' => $penjualan,
            'detailPenjualan' => $detailPenjualan,
            'isApproved' => $isApproved
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
    
    public function editPoJual($id)
    {
        // Get the PO data
        $penjualan = DraftPenjualan::with(['pelanggan', 'karyawan'])->findOrFail($id);
        $detailPenjualan = DraftDetailPenjualan::with('item')->where('id_penjualan', $id)->get();
        
        // Get data for dropdowns
        $items = Item::all();
        $pelanggan = Pelanggan::all();
        $karyawan = Karyawan::all();
        
        return view('pabrik.edit-po-jual', [
            'penjualan' => $penjualan,
            'detailPenjualan' => $detailPenjualan,
            'items' => $items,
            'pelanggan' => $pelanggan,
            'karyawan' => $karyawan
        ]);
    }
    
    public function updatePoJual(Request $request, $id)
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
            
            // Update data penjualan
            $draftPenjualan = DraftPenjualan::findOrFail($id);
            $draftPenjualan->id_pelanggan = $request->customer_id;
            $draftPenjualan->total_harga_penjualan = $totalPrice;
            $draftPenjualan->id_karyawan = $request->employee_id;
            $draftPenjualan->save();
            
            // Hapus semua detail penjualan lama
            DraftDetailPenjualan::where('id_penjualan', $id)->delete();
            
            // Buat detail penjualan baru
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                
                $draftDetailPenjualan = new DraftDetailPenjualan();
                $draftDetailPenjualan->id_penjualan = $id;
                $draftDetailPenjualan->id_item = $item['item_id'];
                $draftDetailPenjualan->jumlah_jual = $quantity;
                $draftDetailPenjualan->harga_jual_satuan = $price;
                $draftDetailPenjualan->subtotal_harga = $subtotal;
                $draftDetailPenjualan->save();
            }
            
            DB::commit();
            
            return redirect()->route('pabrik.po-jual')
                ->with('success', 'PO Penjualan berhasil diperbarui!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approvePoJual($id)
    {
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Find the draft PO
            $draftPenjualan = DraftPenjualan::with('detailPenjualan')->findOrFail($id);
            
            // Generate PO number
            // Format: POJ-YYYYMMDD-XXX (XXX is a sequential number)
            $today = date('Ymd');
            $prefix = "POJ-" . date('Ymd') . "-";
            
            // Get the last PO number with today's date
            $lastPo = DetailPenjualan::where('no_po_jual', 'like', $prefix . '%')
                ->orderBy('no_po_jual', 'desc')
                ->first();
                
            if ($lastPo) {
                // Extract the numeric part and increment
                $lastNumber = (int)substr($lastPo->no_po_jual, -3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            // Format with leading zeros
            $poNumber = $prefix . sprintf('%03d', $newNumber);
            
            // Create new Penjualan record
            $penjualan = new Penjualan();
            $penjualan->id_pelanggan = $draftPenjualan->id_pelanggan;
            $penjualan->tanggal_penjualan = $draftPenjualan->tanggal_penjualan;
            $penjualan->total_harga_penjualan = $draftPenjualan->total_harga_penjualan;
            $penjualan->id_karyawan = $draftPenjualan->id_karyawan;
            $penjualan->save();
            
            // Get all detail items
            $detailItems = DraftDetailPenjualan::where('id_penjualan', $id)->get();
            
            // Create detail records
            foreach ($detailItems as $item) {
                $detailPenjualan = new DetailPenjualan();
                $detailPenjualan->id_penjualan = $penjualan->id_penjualan;
                $detailPenjualan->no_po_jual = $poNumber; // Add PO number
                $detailPenjualan->id_item = $item->id_item;
                $detailPenjualan->jumlah_jual = $item->jumlah_jual;
                $detailPenjualan->harga_jual_satuan = $item->harga_jual_satuan;
                $detailPenjualan->subtotal_harga = $item->subtotal_harga;
                $detailPenjualan->save();
            }
            
            // Delete draft records after successful transfer
            DraftDetailPenjualan::where('id_penjualan', $id)->delete();
            $draftPenjualan->delete();
            
            DB::commit();
            
            return redirect()->route('pabrik.po-jual')->with('success', 'PO Penjualan berhasil diapprove!');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat approve: ' . $e->getMessage());
        }
    }

    public function generateSuratJalan($id)
    {
        // Find the approved PO
        $penjualan = Penjualan::with(['pelanggan', 'karyawan', 'detailPenjualan.item'])
            ->where('id_penjualan', $id)
            ->firstOrFail();
            
        // Get the PO number from the first detail
        $poNumber = $penjualan->getNoPoJual();
        
        if (!$poNumber) {
            return back()->with('error', 'Tidak dapat menemukan nomor PO untuk penjualan ini.');
        }
        
        // Prepare data for PDF
        $data = [
            'penjualan' => $penjualan,
            'poNumber' => $poNumber,
            'tanggal' => date('d-m-Y'),
            'nomor' => 'SJ/' . date('Ymd') . '/' . sprintf('%04d', $id),
            'details' => $penjualan->detailPenjualan
        ];
        
        // Load PDF view
        $pdf = \PDF::loadView('pabrik.surat-jalan-pdf', $data);
        
        // Set paper size
        $pdf->setPaper('a4', 'portrait');
        
        // Download PDF file with nice filename
        return $pdf->stream('surat-jalan-' . $poNumber . '.pdf');
    }

    public function cancelApprovedPoJual($id)
    {
        try {
            // Find the approved PO
            $penjualan = Penjualan::findOrFail($id);
            
            // Update status to "canceled" by adding a status column
            $penjualan->status = 'canceled';
            $penjualan->save();
            
            return redirect()->route('pabrik.po-jual')->with('success', 'PO Penjualan berhasil dibatalkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}