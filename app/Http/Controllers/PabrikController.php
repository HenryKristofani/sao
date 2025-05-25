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

            // Check if this is a draft amendment (has original_po_id)
            if (!empty($draftPenjualan->original_po_id)) {
                // Find the original PO
                $originalPo = Penjualan::findOrFail($draftPenjualan->original_po_id);
                
                // Restore original PO status to approved
                $originalPo->status = 'approved';
                $originalPo->save();
            }

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

            // Check if this is an amended PO (has original_po_id)
            $isAmendedPo = !empty($draftPenjualan->original_po_id);

            // If it's an amended PO, handle amendment numbering
            if ($isAmendedPo) {
                $originalPo = Penjualan::findOrFail($draftPenjualan->original_po_id);
                $originalPoNumber = $originalPo->getNoPoJual();

                // Extract the parts of the original PO number
                $parts = explode('-', $originalPoNumber);

                // Get date and sequence from original PO
                $date = "";
                $sequence = "";

                // Extract date and sequence based on PO format
                if (count($parts) >= 3) {
                    if ($parts[0] === "POJ") {
                        // Handle different PO formats
                        if (
                            $parts[1] === "100" || $parts[1] === "200" || $parts[1] === "300" ||
                            preg_match('/^2\d{2}$/', $parts[1]) // Matches 2xx format for amendments
                        ) {
                            // Format is POJ-XXX-YYYYMMDD-N
                            $date = $parts[2];
                            $sequence = $parts[3];
                        } else {
                            // Format is POJ-YYYYMMDD-N
                            $date = $parts[1];
                            $sequence = $parts[2];
                        }
                    }
                }

                // If we couldn't extract date/sequence, use default values
                if (empty($date) || empty($sequence)) {
                    $date = date('Ymd');
                    $sequence = "1";
                }

                // Determine next amendment number based on current PO number
                if ($parts[0] === "POJ") {
                    if ($parts[1] === "100") {
                        // First original PO -> first amendment
                        $poNumber = "POJ-200-" . $date . "-" . $sequence;
                    } else if ($parts[1] === "200") {
                        // First amendment -> second amendment
                        $poNumber = "POJ-201-" . $date . "-" . $sequence;
                    } else if (preg_match('/^2\d{2}$/', $parts[1])) {
                        // For any amendment number, increment the last two digits
                        $currentAmendNum = intval(substr($parts[1], 1));
                        $nextAmendNum = $currentAmendNum + 1;
                        $poNumber = "POJ-2" . sprintf("%02d", $nextAmendNum) . "-" . $date . "-" . $sequence;
                    } else {
                        // Default to first amendment if pattern doesn't match
                        $poNumber = "POJ-200-" . $date . "-" . $sequence;
                    }
                } else {
                    // If format is completely different, use default
                    $poNumber = "POJ-200-" . $date . "-" . $sequence;
                }
            } else {
                // This is a new PO, not an amendment
                $today = date('Ymd');

                // Get the last PO number with today's date
                $lastPo = DetailPenjualan::where('no_po_jual', 'like', 'POJ-100-' . $today . '-%')
                    ->orderBy('no_po_jual', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastPo) {
                    // Extract the sequence number and increment
                    $parts = explode('-', $lastPo->no_po_jual);
                    if (count($parts) >= 4) {
                        $sequence = (int)$parts[3] + 1;
                    }
                }

                // Format as POJ-100-YYYYMMDD-N
                $poNumber = "POJ-100-" . $today . "-" . $sequence;
            }

            // Create new Penjualan record
            $penjualan = new Penjualan();
            $penjualan->id_pelanggan = $draftPenjualan->id_pelanggan;
            $penjualan->tanggal_penjualan = $draftPenjualan->tanggal_penjualan;
            $penjualan->total_harga_penjualan = $draftPenjualan->total_harga_penjualan;
            $penjualan->id_karyawan = $draftPenjualan->id_karyawan;
            // If this is an amended PO, set reference to original
            if ($isAmendedPo) {
                $penjualan->original_po_id = $draftPenjualan->original_po_id;
            }
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

                // Move items to location 5 (Gudang Perjalanan)
                $this->moveItemToLocation($item->id_item, $item->jumlah_jual, 5);
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

    private function moveItemToLocation($itemId, $quantity, $targetLocationId)
    {
        try {
            // Get the item
            $item = Item::findOrFail($itemId);

            // Get current location item record, assuming default location is stored in item table
            $originalLocationId = $item->id_lokasi_item;

            // Check if there is enough stock in original location
            if ($item->jumlah_item < $quantity) {
                throw new \Exception("Stok tidak mencukupi untuk item: " . $item->nama_item);
            }

            // Reduce quantity from original location
            $item->jumlah_item -= $quantity;
            $item->save();

            // Check if item already exists in target location
            $targetItem = Item::where('id_jenis', $item->id_jenis)
                ->where('id_lokasi_item', $targetLocationId)
                ->first();

            if ($targetItem) {
                // Update existing item in target location
                $targetItem->jumlah_item += $quantity;
                $targetItem->save();
            } else {
                // Create new item record for target location
                $newItem = new Item();
                $newItem->id_jenis = $item->id_jenis;
                $newItem->id_lokasi_item = $targetLocationId;
                $newItem->nama_item = $item->nama_item;
                $newItem->jumlah_item = $quantity;
                $newItem->harga_per_item = $item->harga_per_item;
                $newItem->masa_item = $item->masa_item;
                $newItem->save();
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Helper function to update item quantity
     * 
     * @param int $itemId The item ID
     * @param int $quantity The quantity to update
     * @param string $operation 'increase' or 'decrease'
     * @return bool Success status
     */
    private function updateItemQuantity($itemId, $quantity, $operation = 'decrease')
    {
        try {
            $item = Item::findOrFail($itemId);

            if ($operation === 'decrease') {
                // Check if we have enough stock
                if ($item->jumlah_item < $quantity) {
                    throw new \Exception("Stok tidak mencukupi untuk item: " . $item->nama_item);
                }

                $item->jumlah_item -= $quantity;
            } else {
                // Increase quantity
                $item->jumlah_item += $quantity;
            }

            $item->save();
            return true;
        } catch (\Exception $e) {
            throw $e;
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

    public function generateInvoice($id)
    {
        // Find the approved PO with relations
        $penjualan = Penjualan::with(['pelanggan', 'karyawan', 'detailPenjualan.item'])
            ->where('id_penjualan', $id)
            ->firstOrFail();

        // Check if the PO is approved - only generate invoice for approved POs
        if ($penjualan->status !== 'approved') {
            return back()->with('error', 'Invoice hanya dapat dibuat untuk PO yang sudah diapprove.');
        }

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
            'nomor' => 'INV/' . date('Ymd') . '/' . sprintf('%04d', $id),
            'details' => $penjualan->detailPenjualan,
            'subtotal' => $penjualan->total_harga_penjualan,
            'ppn' => $penjualan->total_harga_penjualan * 0.11, // 11% PPN
            'total' => $penjualan->total_harga_penjualan * 1.11 // Total + PPN
        ];

        // Load PDF view (you'll need to create this view)
        $pdf = \PDF::loadView('pabrik.invoice-pdf', $data);

        // Set paper size
        $pdf->setPaper('a4', 'portrait');

        // Download PDF file with nice filename
        return $pdf->stream('invoice-' . $poNumber . '.pdf');
    }

    public function cancelApprovedPoJual($id)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Find the approved PO
            $penjualan = Penjualan::findOrFail($id);

            // Update status to "canceled"
            $penjualan->status = 'canceled';
            $penjualan->save();

            // Get all detail items and update the PO number
            $detailPenjualan = DetailPenjualan::where('id_penjualan', $id)->get();

            foreach ($detailPenjualan as $detail) {
                // Get current PO number
                $currentPoNumber = $detail->no_po_jual;

                // Parse PO number parts
                $parts = explode('-', $currentPoNumber);

                // Create canceled PO number using format POJ-003-YYYYMMDD-N
                if (count($parts) >= 4) {
                    // Standard format: POJ-XXX-YYYYMMDD-N
                    $newPoNumber = "POJ-003-" . $parts[2] . "-" . $parts[3];
                } else if (count($parts) === 3) {
                    // Old format: POJ-YYYYMMDD-N
                    $newPoNumber = "POJ-003-" . $parts[1] . "-" . $parts[2];
                } else {
                    // Fallback if format is unexpected
                    $newPoNumber = "POJ-003-" . date('Ymd') . "-1";
                }

                // Update PO number
                $detail->no_po_jual = $newPoNumber;
                $detail->save();

                // Return items from location 5 to original location
                $this->returnItemToOriginalLocation($detail->id_item, $detail->jumlah_jual);
            }

            DB::commit();

            return redirect()->route('pabrik.po-jual')->with('success', 'PO Penjualan berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function returnItemToOriginalLocation($itemId, $quantity)
    {
        try {
            // Get original item from inventory
            $originalItem = Item::findOrFail($itemId);
            $id_jenis = $originalItem->id_jenis;

            // Find item in Gudang Perjalanan (location 5)
            $transitItem = Item::where('id_jenis', $id_jenis)
                ->where('id_lokasi_item', 5)
                ->first();

            if (!$transitItem || $transitItem->jumlah_item < $quantity) {
                throw new \Exception("Stok di Gudang Perjalanan tidak mencukupi untuk dikembalikan.");
            }

            // Reduce from Gudang Perjalanan
            $transitItem->jumlah_item -= $quantity;

            // If no items left in transit, delete the record
            if ($transitItem->jumlah_item <= 0) {
                $transitItem->delete();
            } else {
                $transitItem->save();
            }

            // Return the original item record
            $originalItem = Item::where('id_jenis', $id_jenis)
                ->where('id_lokasi_item', '!=', 5)
                ->first();

            if ($originalItem) {
                // Increase quantity at original location
                $originalItem->jumlah_item += $quantity;
                $originalItem->save();
            } else {
                // Create new item record at original location if none exists
                $newItem = new Item();
                $newItem->id_jenis = $id_jenis;
                $newItem->id_lokasi_item = 3; // Default to location 3 if original not found
                $newItem->nama_item = $transitItem->nama_item;
                $newItem->jumlah_item = $quantity;
                $newItem->harga_per_item = $transitItem->harga_per_item;
                $newItem->masa_item = $transitItem->masa_item;
                $newItem->save();
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function editApprovedPoJual($id)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Find the approved PO
            $penjualan = Penjualan::findOrFail($id);

            // Get the details to restore item quantities
            $detailPenjualan = DetailPenjualan::where('id_penjualan', $id)->get();

            // 1. Change the status to "amended"
            $penjualan->status = 'amended';
            $penjualan->save();

            // Restore item quantities for the amended PO
            foreach ($detailPenjualan as $detail) {
                $this->updateItemQuantity($detail->id_item, $detail->jumlah_jual, 'increase');
            }

            // 2. Create a draft copy of the PO
            $draftPenjualan = new DraftPenjualan();
            $draftPenjualan->id_pelanggan = $penjualan->id_pelanggan;
            $draftPenjualan->tanggal_penjualan = date('Y-m-d'); // Current date for the new draft
            $draftPenjualan->total_harga_penjualan = $penjualan->total_harga_penjualan;
            $draftPenjualan->id_karyawan = $penjualan->id_karyawan;
            $draftPenjualan->original_po_id = $id; // Reference to the original PO
            $draftPenjualan->save();

            // Get all detail items from the approved PO
            $detailItems = $detailPenjualan;

            // Create draft detail records
            foreach ($detailItems as $item) {
                $draftDetailPenjualan = new DraftDetailPenjualan();
                $draftDetailPenjualan->id_penjualan = $draftPenjualan->id_penjualan;
                $draftDetailPenjualan->id_item = $item->id_item;
                $draftDetailPenjualan->jumlah_jual = $item->jumlah_jual;
                $draftDetailPenjualan->harga_jual_satuan = $item->harga_jual_satuan;
                $draftDetailPenjualan->subtotal_harga = $item->subtotal_harga;
                $draftDetailPenjualan->original_po_detail_id = $item->id_detail_penjualan; // Reference to original detail
                $draftDetailPenjualan->save();
            }

            DB::commit();

            // Redirect to edit page with the new draft ID
            return redirect()->route('pabrik.po-jual.edit', $draftPenjualan->id_penjualan)
                ->with('success', 'PO Penjualan yang sudah diapprove sedang diedit. Perubahan akan disimpan sebagai PO Draft baru.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat mengedit PO yang sudah diapprove: ' . $e->getMessage());
        }
    }

    /**
     * Complete an approved PO, permanently removing items from inventory
     */
    public function completePoJual($id)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Find the approved PO
            $penjualan = Penjualan::findOrFail($id);

            // Only allow completing approved POs
            if ($penjualan->status !== 'approved') {
                return back()->with('error', 'Hanya PO dengan status approved yang dapat diselesaikan.');
            }

            // Update status to "completed"
            $penjualan->status = 'completed';
            $penjualan->save();

            // Get all detail items 
            $detailPenjualan = DetailPenjualan::where('id_penjualan', $id)->get();

            foreach ($detailPenjualan as $detail) {
                // Remove items from Gudang Perjalanan (location 5)
                $this->removeItemsFromTransit($detail->id_item, $detail->jumlah_jual);
            }

            DB::commit();

            return redirect()->route('pabrik.po-jual')->with('success', 'PO Penjualan berhasil diselesaikan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function removeItemsFromTransit($itemId, $quantity)
    {
        try {
            // Get item details
            $item = Item::findOrFail($itemId);
            $id_jenis = $item->id_jenis;

            // Find item in Gudang Perjalanan (location 5)
            $transitItem = Item::where('id_jenis', $id_jenis)
                ->where('id_lokasi_item', 5)
                ->first();

            if (!$transitItem) {
                throw new \Exception("Item tidak ditemukan di Gudang Perjalanan.");
            }

            if ($transitItem->jumlah_item < $quantity) {
                throw new \Exception("Stok di Gudang Perjalanan tidak mencukupi.");
            }

            // Reduce from Gudang Perjalanan
            $transitItem->jumlah_item -= $quantity;

            // If no items left in transit, delete the record
            if ($transitItem->jumlah_item <= 0) {
                $transitItem->delete();
            } else {
                $transitItem->save();
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

/**
 * Generate a PDF with the PO details
 * 
 * @param int $id The PO ID
 * @return \Illuminate\Http\Response PDF stream
 */
public function printPoJualDetail($id)
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
        
        // Get the PO number from the first detail
        $poNumber = $penjualan->getNoPoJual();
    } else {
        $detailPenjualan = DraftDetailPenjualan::with('item')
            ->where('id_penjualan', $id)
            ->get();
            
        $poNumber = 'DRAFT-PO-' . $id;
    }
    
    // Prepare data for PDF
    $data = [
        'penjualan' => $penjualan,
        'detailPenjualan' => $detailPenjualan,
        'isApproved' => $isApproved,
        'poNumber' => $poNumber ?? 'DRAFT-PO-' . $id,
        'tanggal' => date('d-m-Y'),
        'judul' => 'Detail PO Penjualan #' . $id
    ];
    
    // Load PDF view
    $pdf = \PDF::loadView('pabrik.po-jual-detail-pdf', $data);
    
    // Set paper size
    $pdf->setPaper('a4', 'portrait');
    
    // Download PDF file with nice filename
    return $pdf->stream('detail-po-jual-' . $id . '.pdf');
}
}
