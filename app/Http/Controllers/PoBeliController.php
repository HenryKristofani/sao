<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Pemasok;
use App\Models\Karyawan;
use App\Models\DraftPembelian;
use App\Models\DraftDetailPembelian;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use Illuminate\Support\Facades\DB;

class PoBeliController extends Controller
{
    public function showPoBeli()
    {
        // Get draft POs
        $draftPembelian = DraftPembelian::with(['pemasok', 'karyawan', 'detailPembelian'])
            ->orderBy('id_pembelian', 'desc')
            ->get();

        // Add 'status' attribute to each draft PO
        $draftPembelian->each(function ($item) {
            $item->status = 'draft';
        });

        // Get approved and canceled POs
        $approvedPembelian = Pembelian::with(['pemasok', 'karyawan', 'detailPembelian'])
            ->orderBy('id_pembelian', 'desc')
            ->get();

        // Ensure each PO has the correct status
        $approvedPembelian->each(function ($item) {
            if (!isset($item->status) || $item->status === null) {
                $item->status = 'approved';
            }
        });

        // Merge both collections and sort by id_pembelian descending
        $allPembelian = $draftPembelian->concat($approvedPembelian)->sortByDesc('id_pembelian');

        return view('pabrik.po-beli', [
            'pembelian' => $allPembelian
        ]);
    }

    public function createPoBeli()
    {
        // Get data for dropdowns
        $items = Item::all();
        $pemasok = Pemasok::all();
        $karyawan = Karyawan::where('id_karyawan', 2)->get();

        return view('pabrik.create-po-beli', [
            'items' => $items,
            'pemasok' => $pemasok,
            'karyawan' => $karyawan
        ]);
    }

    public function storePoBeli(Request $request)
    {
        // Validate input
        $request->validate([
            'supplier_id' => 'required|exists:pemasok,id_pemasok',
            'employee_id' => 'required|exists:karyawan,id_karyawan',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item,id_item',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total price
            $totalPrice = 0;

            // Verify and calculate total price for each item
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                $totalPrice += $subtotal;
            }

            // Save purchase data
            $draftPembelian = new DraftPembelian();
            $draftPembelian->id_pemasok = $request->supplier_id;
            $draftPembelian->tanggal_pembelian = date('Y-m-d');
            $draftPembelian->total_harga_pembelian = $totalPrice;
            $draftPembelian->id_karyawan = $request->employee_id;
            $draftPembelian->save();

            // Save purchase details for each item
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                $draftDetailPembelian = new DraftDetailPembelian();
                $draftDetailPembelian->id_pembelian = $draftPembelian->id_pembelian;
                $draftDetailPembelian->id_item = $item['item_id'];
                $draftDetailPembelian->jumlah_beli = $quantity;
                $draftDetailPembelian->harga_beli_satuan = $price;
                $draftDetailPembelian->subtotal_harga = $subtotal;
                $draftDetailPembelian->save();
            }

            DB::commit();

            return redirect()->route('pabrik.po-beli')
                ->with('success', 'PO Pembelian berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showDetailPoBeli($id)
    {
        // Try to find in draft first
        $pembelian = DraftPembelian::with(['pemasok', 'karyawan'])
            ->where('id_pembelian', $id)
            ->first();

        $isApproved = false;

        // If not found in draft, check in approved pembelian
        if (!$pembelian) {
            $pembelian = Pembelian::with(['pemasok', 'karyawan'])
                ->where('id_pembelian', $id)
                ->firstOrFail();

            $detailPembelian = DetailPembelian::with('item')
                ->where('id_pembelian', $id)
                ->get();

            $isApproved = true;
        } else {
            $detailPembelian = DraftDetailPembelian::with('item')
                ->where('id_pembelian', $id)
                ->get();
        }

        return view('pabrik.po-beli-detail', [
            'pembelian' => $pembelian,
            'detailPembelian' => $detailPembelian,
            'isApproved' => $isApproved
        ]);
    }

    public function cancelPoBeli($id)
    {
        try {
            DB::beginTransaction();

            $draftPembelian = DraftPembelian::findOrFail($id);

            // Check if this draft PO is from editing an approved PO
            if (!empty($draftPembelian->original_po_id)) {
                // This is a draft created from editing an approved PO
                // Restore the original PO status back to 'approved'
                $originalPo = Pembelian::findOrFail($draftPembelian->original_po_id);
                $originalPo->status = 'approved';
                $originalPo->save();
            }

            // Delete related draft details
            DraftDetailPembelian::where('id_pembelian', $id)->delete();

            // Delete the draft pembelian (PO)
            $draftPembelian->delete();

            DB::commit();

            return redirect()->route('pabrik.po-beli')->with('success', 'PO Pembelian berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function editPoBeli($id)
    {
        // Get the PO data
        $pembelian = DraftPembelian::with(['pemasok', 'karyawan'])->findOrFail($id);
        $detailPembelian = DraftDetailPembelian::with('item')->where('id_pembelian', $id)->get();

        // Get data for dropdowns
        $items = Item::all();
        $pemasok = Pemasok::all();
        $karyawan = Karyawan::where('id_karyawan', 2)->get();

        return view('pabrik.edit-po-beli', [
            'pembelian' => $pembelian,
            'detailPembelian' => $detailPembelian,
            'items' => $items,
            'pemasok' => $pemasok,
            'karyawan' => $karyawan
        ]);
    }

    public function updatePoBeli(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'supplier_id' => 'required|exists:pemasok,id_pemasok',
            'employee_id' => 'required|exists:karyawan,id_karyawan',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item,id_item',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total price
            $totalPrice = 0;

            // Verify and calculate total price for each item
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                $totalPrice += $subtotal;
            }

            // Update purchase data
            $draftPembelian = DraftPembelian::findOrFail($id);
            $draftPembelian->id_pemasok = $request->supplier_id;
            $draftPembelian->total_harga_pembelian = $totalPrice;
            $draftPembelian->id_karyawan = $request->employee_id;
            $draftPembelian->save();

            // Delete all old purchase details
            DraftDetailPembelian::where('id_pembelian', $id)->delete();

            // Create new purchase details
            foreach ($request->items as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $price = $itemRecord->harga_per_item;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                $draftDetailPembelian = new DraftDetailPembelian();
                $draftDetailPembelian->id_pembelian = $id;
                $draftDetailPembelian->id_item = $item['item_id'];
                $draftDetailPembelian->jumlah_beli = $quantity;
                $draftDetailPembelian->harga_beli_satuan = $price;
                $draftDetailPembelian->subtotal_harga = $subtotal;
                $draftDetailPembelian->save();
            }

            DB::commit();

            return redirect()->route('pabrik.po-beli')
                ->with('success', 'PO Pembelian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approvePoBeli($id)
    {
        DB::beginTransaction();

        try {
            // Find the draft PO
            $draftPembelian = DraftPembelian::with('detailPembelian')->findOrFail($id);

            // Check if this is an amended PO
            $isAmendedPo = !empty($draftPembelian->original_po_id);

            // Generate PO number
            if ($isAmendedPo) {
                $originalPo = Pembelian::findOrFail($draftPembelian->original_po_id);
                $originalPoNumber = $originalPo->getNoPoBeli();
                $parts = explode('-', $originalPoNumber);
                
                // Get the current amendment number from the original PO number
                $date = $parts[2] ?? date('Ymd');
                $sequence = $parts[3] ?? "1";
                
                // Extract the current amendment number
                if (isset($parts[1])) {
                    $currentCode = $parts[1];
                    if ($currentCode === "100") {
                        $newCode = "200";
                    } else if ($currentCode === "200") {
                        // Get the amendment count and increment
                        $amendmentNumber = (int)substr($currentCode, 1) + 1;
                        if ($amendmentNumber < 10) {
                            $amendmentNumber = '0' . $amendmentNumber;
                        }
                        $newCode = "2" . $amendmentNumber;
                    } else if (strpos($currentCode, "2") === 0 && strlen($currentCode) === 3) {
                        // Already an amended PO (2XX format)
                        $amendmentNumber = (int)substr($currentCode, 1) + 1;
                        if ($amendmentNumber < 10) {
                            $amendmentNumber = '0' . $amendmentNumber;
                        }
                        $newCode = "2" . $amendmentNumber;
                    } else {
                        $newCode = "200";
                    }
                } else {
                    $newCode = "200";
                }
                
                $poNumber = "POB-" . $newCode . "-" . $date . "-" . $sequence;
            } else {
                // New PO numbering - starts with 100
                $today = date('Ymd');
                $lastPo = DetailPembelian::where('no_po_beli', 'like', 'POB-100-' . $today . '-%')
                    ->orderBy('no_po_beli', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastPo) {
                    $parts = explode('-', $lastPo->no_po_beli);
                    if (count($parts) >= 4) {
                        $sequence = (int)$parts[3] + 1;
                    }
                }

                $poNumber = "POB-100-" . $today . "-" . $sequence;
            }

            // Create new Pembelian record
            $pembelian = new Pembelian();
            $pembelian->id_pemasok = $draftPembelian->id_pemasok;
            $pembelian->tanggal_pembelian = $draftPembelian->tanggal_pembelian;
            $pembelian->total_harga_pembelian = $draftPembelian->total_harga_pembelian;
            $pembelian->id_karyawan = $draftPembelian->id_karyawan;
            if ($isAmendedPo) {
                $pembelian->original_po_id = $draftPembelian->original_po_id;
            }
            $pembelian->save();

            // Create detail records
            foreach ($draftPembelian->detailPembelian as $item) {
                $detailPembelian = new DetailPembelian();
                $detailPembelian->id_pembelian = $pembelian->id_pembelian;
                $detailPembelian->no_po_beli = $poNumber;
                $detailPembelian->id_item = $item->id_item;
                $detailPembelian->jumlah_beli = $item->jumlah_beli;
                $detailPembelian->harga_beli_satuan = $item->harga_beli_satuan;
                $detailPembelian->subtotal_harga = $item->subtotal_harga;
                $detailPembelian->save();
            }

            // Delete draft records
            DraftDetailPembelian::where('id_pembelian', $id)->delete();
            $draftPembelian->delete();

            DB::commit();

            return redirect()->route('pabrik.po-beli')->with('success', 'PO Pembelian berhasil diapprove!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat approve: ' . $e->getMessage());
        }
    }

    public function completePoBeli($id)
    {
        try {
            DB::beginTransaction();

            $pembelian = Pembelian::findOrFail($id);

            // Check if PO is approved
            if ($pembelian->status !== 'approved') {
                throw new \Exception('Hanya PO yang sudah disetujui yang dapat diselesaikan');
            }

            // Update PO status to completed
            $pembelian->status = 'completed';
            $pembelian->save();

            // Add items to inventory
            foreach ($pembelian->detailPembelian as $detail) {
                $item = $detail->item;
                $item->jumlah_item += $detail->jumlah_beli;
                $item->save();
            }

            DB::commit();
            return redirect()->route('pabrik.po-beli.show', $id)
                           ->with('success', 'PO Pembelian berhasil diselesaikan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pabrik.po-beli.show', $id)
                           ->with('error', 'Gagal menyelesaikan PO Pembelian: ' . $e->getMessage());
        }
    }

    public function cancelApprovedPoBeli($id)
    {
        try {
            DB::beginTransaction();

            // Find the approved PO
            $pembelian = Pembelian::findOrFail($id);

            // Update status to "canceled"
            $pembelian->status = 'canceled';
            $pembelian->save();

            // Get all detail items
            $detailPembelian = DetailPembelian::where('id_pembelian', $id)->get();

            foreach ($detailPembelian as $detail) {
                // Update PO number for canceled items - change to 300 format
                $currentPoNumber = $detail->no_po_beli;
                $parts = explode('-', $currentPoNumber);
                
                if (count($parts) >= 4) {
                    $newPoNumber = "POB-300-" . $parts[2] . "-" . $parts[3];
                } else if (count($parts) === 3) {
                    $newPoNumber = "POB-300-" . $parts[1] . "-" . $parts[2];
                } else {
                    $newPoNumber = "POB-300-" . date('Ymd') . "-1";
                }

                $detail->no_po_beli = $newPoNumber;
                $detail->save();

                // Remove items from inventory
                $this->removeItemFromInventory($detail->id_item, $detail->jumlah_beli);
            }

            DB::commit();

            return redirect()->route('pabrik.po-beli')->with('success', 'PO Pembelian berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function removeItemFromInventory($itemId, $quantity)
    {
        try {
            $item = Item::findOrFail($itemId);
            if ($item->jumlah_item < $quantity) {
                throw new \Exception("Stok tidak mencukupi untuk item: " . $item->nama_item);
            }
            $item->jumlah_item -= $quantity;
            $item->save();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function editApprovedPoBeli($id)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Find the approved PO
            $pembelian = Pembelian::findOrFail($id);

            // Get the details
            $detailPembelian = DetailPembelian::where('id_pembelian', $id)->get();

            // 1. Change the status to "amended"
            $pembelian->status = 'amended';
            $pembelian->save();

            // 2. Create a draft copy of the PO
            $draftPembelian = new DraftPembelian();
            $draftPembelian->id_pemasok = $pembelian->id_pemasok;
            $draftPembelian->tanggal_pembelian = date('Y-m-d'); // Current date for the new draft
            $draftPembelian->total_harga_pembelian = $pembelian->total_harga_pembelian;
            $draftPembelian->id_karyawan = $pembelian->id_karyawan;
            $draftPembelian->original_po_id = $id; // Reference to the original PO
            $draftPembelian->save();

            // Get all detail items from the approved PO
            $detailItems = $detailPembelian;

            // Create draft detail records
            foreach ($detailItems as $item) {
                $draftDetailPembelian = new DraftDetailPembelian();
                $draftDetailPembelian->id_pembelian = $draftPembelian->id_pembelian;
                $draftDetailPembelian->id_item = $item->id_item;
                $draftDetailPembelian->jumlah_beli = $item->jumlah_beli;
                $draftDetailPembelian->harga_beli_satuan = $item->harga_beli_satuan;
                $draftDetailPembelian->subtotal_harga = $item->subtotal_harga;
                $draftDetailPembelian->original_po_detail_id = $item->id_detail_pembelian; // Reference to original detail
                $draftDetailPembelian->save();
            }

            DB::commit();

            // Redirect to edit page with the new draft ID
            return redirect()->route('pabrik.po-beli.edit', $draftPembelian->id_pembelian)
                ->with('success', 'PO Pembelian yang sudah diapprove sedang diedit. Perubahan akan disimpan sebagai PO Draft baru.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat mengedit PO yang sudah diapprove: ' . $e->getMessage());
        }
    }

    public function printPoBeliDetail($id)
    {
        // Try to find in draft first
        $pembelian = DraftPembelian::with(['pemasok', 'karyawan'])
            ->where('id_pembelian', $id)
            ->first();
        
        $isApproved = false;
        
        // If not found in draft, check in approved pembelian
        if (!$pembelian) {
            $pembelian = Pembelian::with(['pemasok', 'karyawan'])
                ->where('id_pembelian', $id)
                ->firstOrFail();
            
            $detailPembelian = DetailPembelian::with('item')
                ->where('id_pembelian', $id)
                ->get();
                
            $isApproved = true;
            
            // Get the PO number from the first detail
            $poNumber = $pembelian->getNoPoBeli();
        } else {
            $detailPembelian = DraftDetailPembelian::with('item')
                ->where('id_pembelian', $id)
                ->get();
                
            $poNumber = 'DRAFT-PO-' . $id;
        }
        
        // Prepare data for PDF
        $data = [
            'pembelian' => $pembelian,
            'detailPembelian' => $detailPembelian,
            'isApproved' => $isApproved,
            'poNumber' => $poNumber ?? 'DRAFT-PO-' . $id,
            'tanggal' => date('d-m-Y'),
            'judul' => 'Detail PO Pembelian #' . $id
        ];
        
        // Load PDF view
        $pdf = \PDF::loadView('pabrik.po-beli-detail-pdf', $data);
        
        // Set paper size
        $pdf->setPaper('a4', 'portrait');
        
        // Download PDF file
        return $pdf->stream('detail-po-beli-' . $id . '.pdf');
    }

    public function updateApprovedPoBeli(Request $request, $id)
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