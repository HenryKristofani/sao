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
        POJualDraft::create([
            'nomor_po' => $request->nomor_po,
            'customer' => $request->customer,
            'total_harga' => $request->total_harga,
            'is_amendment' => 0, // ✅ Buat PO baru biasa → bukan hasil amend
        ]);
    
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
    
        POJualDraft::create([
            'nomor_po' => $this->generateNomorPO($po->nomor_po),
            'customer' => $request->customer,
            'total_harga' => $request->total_harga,
            'is_amendment' => 1, // ✅ TANDAI sebagai hasil amend
        ]);
    
        return redirect()->route('po-jual.index')->with('success', 'PO lama diubah ke Amended dan draft baru dibuat.');
    }
    
    
    

    public function approve($id)
    {
        $draft = POJualDraft::findOrFail($id);
    
        // Simpan ke tabel po_jual (final)
        POJual::create([
            'nomor_po' => $draft->nomor_po,
            'customer' => $draft->customer,
            'total_harga' => $draft->total_harga,
            'status' => 'Approved',
        ]);
    
        // Hapus dari tabel draft
        $draft->delete();
    
        return redirect()->route('po-jual.index')->with('success', 'PO berhasil di-approve.');
    }
    

    public function cancel($id)
    {
        // Jika PO masih draft → hapus
        $draft = POJualDraft::find($id);
        if ($draft) {
            $draft->delete();
            return redirect()->route('po-jual.index')->with('success', 'PO Draft berhasil dihapus.');
        }

        // Jika bukan draft → update status jadi canceled
        $po = POJual::findOrFail($id);
        $po->status = 'Canceled';
        $po->save();

        return redirect()->route('po-jual.index')->with('success', 'PO berhasil dibatalkan.');
    }

    private function generateNomorPO($originalNo = null)
    {
        $today = now()->format('Ymd');
        $prefix = 'POJ-' . $today . '-';

        if (!$originalNo) {
            $countDraft = \DB::table('po_jual_draft')->whereDate('created_at', now()->toDateString())->count();
            $countFinal = \DB::table('po_jual')->whereDate('created_at', now()->toDateString())->count();
            $urutan = $countDraft + $countFinal + 1;

            return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);
        } else {
            // PO hasil amandemen → tambahkan suffix -A, -B, dst
            $base = $originalNo;

            $latest = \DB::table('po_jual_draft')
                ->where('nomor_po', 'like', $base . '-%')
                ->orderByDesc('nomor_po')
                ->first();

            if ($latest) {
                $lastSuffix = strtoupper(substr($latest->nomor_po, strrpos($latest->nomor_po, '-') + 1));
                $nextSuffix = chr(ord($lastSuffix) + 1); // naik 1 huruf
            } else {
                $nextSuffix = 'A';
            }

            return $base . '-' . $nextSuffix;
        }
    }
}
