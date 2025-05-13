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
        $karyawan = Karyawan::all();

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
            $draftPembelian = DraftPembelian::findOrFail($id);

            // Delete related draft details
            DraftDetailPembelian::where('id_pembelian', $id)->delete();

            // Delete the draft pembelian (PO)
            $draftPembelian->delete();

            return redirect()->route('pabrik.po-beli')->with('success', 'PO Pembelian berhasil dibatalkan!');
        } catch (\Exception $e) {
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
        $karyawan = Karyawan::all();

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
                
                // Similar logic to PO Jual for amendment numbering
                $date = $parts[2] ?? date('Ymd');
                $sequence = $parts[3] ?? "1";
                
                if ($parts[1] === "001") {
                    $poNumber = "POB-002-" . $date . "-" . $sequence;
                } else if ($parts[1] === "002") {
                    $poNumber = "POB-012-" . $date . "-" . $sequence;
                } else if ($parts[1] === "012") {
                    $poNumber = "POB-022-" . $date . "-" . $sequence;
                } else if ($parts[1] === "022") {
                    $poNumber = "POB-032-" . $date . "-" . $sequence;
                } else {
                    $poNumber = "POB-002-" . $date . "-" . $sequence;
                }
            } else {
                // New PO numbering
                $today = date('Ymd');
                $lastPo = DetailPembelian::where('no_po_beli', 'like', 'POB-001-' . $today . '-%')
                    ->orderBy('no_po_beli', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastPo) {
                    $parts = explode('-', $lastPo->no_po_beli);
                    if (count($parts) >= 4) {
                        $sequence = (int)$parts[3] + 1;
                    }
                }

                $poNumber = "POB-001-" . $today . "-" . $sequence;
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
                // Update PO number for canceled items
                $currentPoNumber = $detail->no_po_beli;
                $parts = explode('-', $currentPoNumber);
                
                if (count($parts) >= 4) {
                    $newPoNumber = "POB-003-" . $parts[2] . "-" . $parts[3];
                } else if (count($parts) === 3) {
                    $newPoNumber = "POB-003-" . $parts[1] . "-" . $parts[2];
                } else {
                    $newPoNumber = "POB-003-" . date('Ymd') . "-1";
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
            DB::beginTransaction();

            // Find the approved PO
            $pembelian = Pembelian::findOrFail($id);

            // Get the details
            $detailPembelian = DetailPembelian::where('id_pembelian', $id)->get();

            // Change status to "amended"
            $pembelian->status = 'amended';
            $pembelian->save();

            // Remove items from inventory
            foreach ($detailPembelian as $detail) {
                $this->removeItemFromInventory($detail->id_item, $detail->jumlah_beli);
            }

            // Create a draft copy
            $draftPembelian = new DraftPembelian();
            $draftPembelian->id_pemasok = $pembelian->id_pemasok;
            $draftPembelian->tanggal_pembelian = date('Y-m-d');
            $draftPembelian->total_harga_pembelian = $pembelian->total_harga_pembelian;
            $draftPembelian->id_karyawan = $pembelian->id_karyawan;
            $draftPembelian->original_po_id = $id;
            $draftPembelian->save();

            // Create draft details
            foreach ($detailPembelian as $item) {
                $draftDetailPembelian = new DraftDetailPembelian();
                $draftDetailPembelian->id_pembelian = $draftPembelian->id_pembelian;
                $draftDetailPembelian->id_item = $item->id_item;
                $draftDetailPembelian->jumlah_beli = $item->jumlah_beli;
                $draftDetailPembelian->harga_beli_satuan = $item->harga_beli_satuan;
                $draftDetailPembelian->subtotal_harga = $item->subtotal_harga;
                $draftDetailPembelian->original_po_detail_id = $item->id_detail_pembelian;
                $draftDetailPembelian->save();
            }

            DB::commit();

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
} 