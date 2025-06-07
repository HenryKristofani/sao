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

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nama_mesin' => 'nullable|string',
            'estimasi_durasi' => 'required|integer|min:1',
            'prioritas' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        try {
            // Update the current schedule
            $current = DB::table('jadwal_produksi')->where('id_jadwal', $id)->first();

            // Daftar tanggal merah (samakan dengan yang di PabrikController)
            $holidays = [
                // '2025-06-17', // contoh tanggal merah
                // Tambahkan tanggal merah lain di sini
            ];
            // Helper function
            $addWorkingDays = function($date, $days, $holidays = []) {
                $current = strtotime($date);
                $added = 0;
                while ($added < $days) {
                    $current = strtotime('+1 day', $current);
                    $dayOfWeek = date('N', $current); // 6=Saturday, 7=Sunday
                    $currentDateStr = date('Y-m-d', $current);
                    if ($dayOfWeek < 6 && !in_array($currentDateStr, $holidays)) {
                        $added++;
                    }
                }
                return date('Y-m-d', $current);
            };

            // Ambil task sebelumnya
            $prevTask = DB::table('jadwal_produksi')
                ->where('id_penjualan', $current->id_penjualan)
                ->where('id_jadwal', '<', $id)
                ->orderBy('id_jadwal', 'desc')
                ->first();

            if ($prevTask) {
                $prevEndNext = $addWorkingDays($prevTask->tanggal_selesai, 1, $holidays);
                if ($request->tanggal_mulai < $prevEndNext) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak bisa memajukan jadwal karena masih ada task sebelumnya pada tanggal tersebut.'
                    ]);
                }
            }

            // Update the current schedule
            DB::table('jadwal_produksi')
                ->where('id_jadwal', $id)
                ->update([
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'nama_mesin' => $request->nama_mesin,
                    'estimasi_durasi' => $request->estimasi_durasi,
                    'prioritas' => $request->prioritas,
                    'catatan' => $request->catatan,
                ]);

            // Get all next schedules for the same PO
            $nextTasks = DB::table('jadwal_produksi')
                ->where('id_penjualan', $current->id_penjualan)
                ->where('id_jadwal', '>', $id)
                ->orderBy('id_jadwal')
                ->get();

            $prevEndDate = $request->tanggal_selesai;
            foreach ($nextTasks as $task) {
                $newStart = $addWorkingDays($prevEndDate, 1, $holidays);
                $newEnd = $addWorkingDays($newStart, $task->estimasi_durasi - 1, $holidays);
                DB::table('jadwal_produksi')->where('id_jadwal', $task->id_jadwal)
                    ->update([
                        'tanggal_mulai' => $newStart,
                        'tanggal_selesai' => $newEnd,
                    ]);
                $prevEndDate = $newEnd;
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
} 