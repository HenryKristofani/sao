<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\JenisPelanggan;
use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{
    /**
     * Display a list of pelanggan (clients)
     */
    public function index()
    {
        $pelanggans = Pelanggan::with('jenisPelanggan')->get();
        $jenisPelanggans = JenisPelanggan::all();
        return view('pabrik.pelanggan-list', compact('pelanggans', 'jenisPelanggans'));
    }

    /**
     * Store a new pelanggan
     */
    public function store(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'nama_pelanggan' => 'required|max:255',
            'alamat_pelanggan' => 'nullable|max:255',
            'no_telp_pelanggan' => 'nullable|max:20',
            'email_pelanggan' => 'nullable|email|max:255',
            'id_jenis' => 'required|exists:jenis_pelanggan,id_jenis'
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->route('pabrik.pelanggan')
                ->withErrors($validator)
                ->withInput();
        }

        // Create new pelanggan
        Pelanggan::create($request->all());

        // Redirect with success message
        return redirect()->route('pabrik.pelanggan')
            ->with('success', 'Pelanggan berhasil ditambahkan');
    }

    /**
     * Show edit form for a pelanggan
     */
    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggans = Pelanggan::with('jenisPelanggan')->get();
        $jenisPelanggans = JenisPelanggan::all();
        return view('pabrik.pelanggan-list', compact('pelanggan', 'pelanggans', 'jenisPelanggans'));
    }

    /**
     * Update an existing pelanggan
     */
    public function update(Request $request, $id)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'nama_pelanggan' => 'required|max:255',
            'alamat_pelanggan' => 'nullable|max:255',
            'no_telp_pelanggan' => 'nullable|max:20',
            'email_pelanggan' => 'nullable|email|max:255',
            'id_jenis' => 'required|exists:jenis_pelanggan,id_jenis'
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->route('pabrik.pelanggan')
                ->withErrors($validator)
                ->withInput();
        }

        // Find and update the pelanggan
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->update($request->all());

        // Redirect with success message
        return redirect()->route('pabrik.pelanggan')
            ->with('success', 'Pelanggan berhasil diperbarui');
    }

    /**
     * Delete a pelanggan
     */
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return redirect()->route('pabrik.pelanggan')
            ->with('success', 'Pelanggan berhasil dihapus');
    }
}