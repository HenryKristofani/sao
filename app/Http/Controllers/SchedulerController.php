<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PoJualSchedule; // Assuming you have this model
use Illuminate\Support\Facades\DB;

class SchedulerController extends Controller
{
    public function autoUpdateScheduleStatus()
    {
        $today = date('Y-m-d');
        $schedules = DB::table('jadwal_produksi')->orderBy('tanggal_mulai')->get();

        foreach ($schedules as $schedule) {
            $mulai = substr($schedule->tanggal_mulai, 0, 10);
            $selesai = substr($schedule->tanggal_selesai, 0, 10);
            if ($today < $mulai) {
                $newStatus = 'dijadwalkan';
            } elseif ($today >= $mulai && $today <= $selesai) {
                $newStatus = 'berlangsung';
            } elseif ($today > $selesai) {
                $newStatus = 'selesai';
            } else {
                $newStatus = $schedule->status;
            }
            if ($schedule->status !== $newStatus) {
                DB::table('jadwal_produksi')
                    ->where('id_jadwal', $schedule->id_jadwal)
                    ->update(['status' => $newStatus]);
            }
        }
    }

    public function index()
    {
        $this->autoUpdateScheduleStatus();
        // Fetch schedule data directly from jadwal_produksi, joining with penjualan to get PO details
        $schedules = DB::table('jadwal_produksi')
            ->join('penjualan', 'jadwal_produksi.id_penjualan', '=', 'penjualan.id_penjualan')
            ->select('jadwal_produksi.*' , 'penjualan.id_pelanggan', 'penjualan.tanggal_penjualan', 'penjualan.total_harga_penjualan', 'penjualan.id_karyawan')
            ->orderBy('jadwal_produksi.id_penjualan')
            ->orderBy('jadwal_produksi.tanggal_mulai')
            ->get();

        // Group schedules by PO Jual ID for easier display in the view
        $groupedSchedules = $schedules->groupBy('id_penjualan');

        return view('pabrik.scheduler', ['groupedSchedules' => $groupedSchedules]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dijadwalkan,berlangsung,selesai,ditunda',
        ]);

        try {
            DB::table('jadwal_produksi')
                ->where('id_jadwal', $id) // Assuming 'id_jadwal' is the primary key
                ->update(['status' => $request->status, 'updated_at' => now()]);

            return redirect()->route('pabrik.scheduler')->with('success', 'Status jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memperbarui status jadwal: ' . $e->getMessage());
        }
    }
} 