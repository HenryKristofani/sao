<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\POJual;
use App\Models\POJualDraft;

class POJualController extends Controller
{
    public function index()
    {
        $drafts = POJualDraft::all();
        $poList = POJual::all();

        return view('pabrik.po-jual', compact('drafts', 'poList'));
    }

    public function create()
    {
        $nomor_po = $this->generateNomorPO();
        return view('pabrik.po-jual-create', compact('nomor_po'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['nomor_po'] = null; // kosongin dulu
        $data['status'] = 'Draft';
        $data['is_amendment'] = 0;
    
        POJualDraft::create($data);
    
        return redirect()->route('po-jual.index')->with('success', 'PO Draft berhasil dibuat.');
    }

    public function edit($id)
    {
        // Cek di draft dulu
        $draft = POJualDraft::find($id);
        if ($draft) {
            return view('pabrik.po-jual-edit', ['po' => $draft, 'isDraft' => true]);
        }

        // Kalau nggak ada di draft, cek di PO utama (approved)
        $po = POJual::findOrFail($id);
        return view('pabrik.po-jual-edit', ['po' => $po, 'isDraft' => false]);
    }

    public function update(Request $request, $id)
    {
        // Kalau draft → tinggal update biasa
        $draft = POJualDraft::find($id);
        if ($draft) {
            $draft->update([
                'customer' => $request->customer,
                'total_harga' => $request->total_harga,
            ]);
            return redirect()->route('po-jual.index')->with('success', 'PO Draft diperbarui.');
        }
    
        // Kalau yang diedit adalah PO Approved
        $po = POJual::findOrFail($id);
        $po->status = 'Amended';
        $po->save();
    
        // Buat nomor PO amend dengan menambahkan suffix -A
        $amendedNomorPO = $po->nomor_po . '-A';
        
        // Cek apakah sudah ada amend dengan suffix tertentu
        $latestAmend = POJualDraft::where('nomor_po', 'like', $po->nomor_po . '-%')
            ->orderByDesc('nomor_po')
            ->first();
            
        if ($latestAmend) {
            // Jika sudah ada amend sebelumnya, naikkan suffixnya (A→B, B→C, dst)
            $lastSuffix = substr($latestAmend->nomor_po, -1); // Ambil karakter terakhir
            $nextSuffix = chr(ord($lastSuffix) + 1); // Naikkan 1 huruf (ASCII)
            $amendedNomorPO = $po->nomor_po . '-' . $nextSuffix;
        }
    
        POJualDraft::create([
            'nomor_po' => $amendedNomorPO,
            'customer' => $request->customer,
            'total_harga' => $request->total_harga,
            'is_amendment' => 1, // ✅ TANDAI sebagai hasil amend
        ]);
    
        return redirect()->route('po-jual.index')->with('success', 'PO lama diubah ke Amended dan draft baru dibuat.');
    }

    public function approve($id)
    {
        $draft = POJualDraft::findOrFail($id);
    
        // Gunakan nomor PO dari draft jika sudah ada (untuk kasus amend)
        // atau generate baru jika tidak ada
        $nomor_po = $draft->nomor_po ?: $this->generateNomorPO();
    
        POJual::create([
            'nomor_po' => $nomor_po,
            'customer' => $draft->customer,
            'total_harga' => $draft->total_harga,
            'status' => 'Approved',
            'is_amendment' => $draft->is_amendment,
        ]);
    
        $draft->delete();
    
        return redirect()->route('po-jual.index')->with('success', 'PO berhasil di-approve.');
    }
    
    /**
     * Cancel PO sesuai ketentuan
     * 1. Draft dengan is_amendment=0: Hapus dari database
     * 2. Lainnya: Ubah status menjadi 'Canceled'
     */
    public function cancel($id)
    {
        // Cek di tabel draft terlebih dahulu
        $draft = POJualDraft::find($id);
        if ($draft) {
            // Kondisi 1: Jika status 'Draft' dan is_amendment = 0, hapus dari database
            if ($draft->is_amendment == 0) {
                $draft->delete();
                return redirect()->route('po-jual.index')->with('success', 'PO Draft berhasil dihapus.');
            } else {
                // Kondisi 2: Jika Draft hasil amendment (is_amendment = 1), ubah status menjadi Canceled
                $draft->status = 'Canceled';
                $draft->save();
                return redirect()->route('po-jual.index')->with('success', 'PO Draft hasil amendment telah diubah menjadi Canceled.');
            }
        }
        
        // Cek di tabel PO utama
        $po = POJual::findOrFail($id);
        
        // Jika status Approved, ubah menjadi Canceled
        if ($po->status == 'Approved') {
            $po->status = 'Canceled';
            $po->save();
            return redirect()->route('po-jual.index')->with('success', 'PO Approved telah diubah menjadi Canceled.');
        } else {
            // Jika status sudah Amended atau Canceled, tidak bisa di-cancel
            return redirect()->route('po-jual.index')->with('error', 'PO dengan status ' . $po->status . ' tidak dapat di-cancel.');
        }
    }
    
    private function generateNomorPO($originalNo = null)
    {
        $today = now()->format('Ymd');
        $prefix = 'POJ-' . $today . '-';
    
        if (!$originalNo) {
            // Membuat nomor PO baru untuk status Draft
            $countDraft = \DB::table('po_jual_draft')->whereDate('created_at', now()->toDateString())->count();
            $countFinal = \DB::table('po_jual')->whereDate('created_at', now()->toDateString())->count();
            $urutan = $countDraft + $countFinal + 1;
    
            return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT); // Format PO: POJ-20250412-001
        } else {
            // PO hasil amend → tambahkan suffix -A, -B, dst
            $base = substr($originalNo, 0, strrpos($originalNo, '-')); // Ambil nomor dasar tanpa suffix
            $suffix = substr($originalNo, strrpos($originalNo, '-') + 1); // Ambil suffix, jika ada
    
            // Cek apakah ada PO Draft sebelumnya yang memiliki suffix yang sama
            $latest = \DB::table('po_jual_draft')
                ->where('nomor_po', 'like', $base . '-%')
                ->orderByDesc('nomor_po')
                ->first();
    
            // Jika sudah ada suffix, naikkan urutannya (A → B, B → C, dst)
            if ($latest) {
                $lastSuffix = strtoupper(substr($latest->nomor_po, strrpos($latest->nomor_po, '-') + 1));
                $nextSuffix = chr(ord($lastSuffix) + 1); // naik 1 huruf
            } else {
                // Jika belum ada amend, gunakan suffix "-A"
                $nextSuffix = 'A';
            }
    
            // Kembalikan nomor PO dengan suffix baru
            return $base . '-' . $nextSuffix;  // Format PO: POJ-20250412-008-A
        }
    }
}