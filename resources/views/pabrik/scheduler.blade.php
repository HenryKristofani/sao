@extends('layouts.app')

@section('content')
    @include('layouts.SidebarPabrik')

    <div class="content p-4" style="margin-top: 60px;">
        <h4 class="fw-bold mb-4">Jadwal Produksi</h4>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                {{-- FullCalendar will be rendered here --}}
                <div id="calendar" data-schedules="{{ json_encode($groupedSchedules) }}"></div>
            </div>
        </div>

        {{-- Detail Table View --}}
        <div class="card mt-4">
            <div class="card-body">
                <h5>Detail Jadwal Produksi</h5>
                @if($groupedSchedules->isEmpty())
                    <p>Tidak ada detail jadwal produksi saat ini.</p>
                @else
                    @foreach($groupedSchedules as $poId => $poSchedules)
                        <div class="mb-4">
                            <h6>PO Jual #{{ $poId }}</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tahap</th>
                                            <th>Nama Mesin</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                            <th>Estimasi Durasi (Hari)</th>
                                            <th>Prioritas</th>
                                            <th>Status</th>
                                            <th>Catatan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($poSchedules as $schedule)
                                            <tr>
                                                <td>{{ $schedule->catatan ?? 'N/A' }}</td>
                                                <td>{{ $schedule->nama_mesin }}</td>
                                                <td>{{ date('d/m/Y', strtotime($schedule->tanggal_mulai)) }}</td>
                                                <td>{{ date('d/m/Y', strtotime($schedule->tanggal_selesai)) }}</td>
                                                <td>{{ $schedule->estimasi_durasi }}</td>
                                                <td>{{ $schedule->prioritas }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = '';
                                                        switch($schedule->status) {
                                                            case 'dijadwalkan': $statusClass = 'badge bg-secondary'; break;
                                                            case 'berlangsung': $statusClass = 'badge bg-primary'; break;
                                                            case 'selesai': $statusClass = 'badge bg-success'; break;
                                                            case 'ditunda': $statusClass = 'badge bg-warning'; break;
                                                            default: $statusClass = 'badge bg-secondary'; break;
                                                        }
                                                    @endphp
                                                    <span class="{{ $statusClass }}">{{ $schedule->status }}</span>
                                                </td>
                                                <td>{{ $schedule->catatan }}</td>
                                                <td>
                                                    <form action="{{ route('pabrik.scheduler.updateStatus', $schedule->id_jadwal) }}" method="POST">
                                                        @csrf
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="dijadwalkan" {{ $schedule->status == 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                                                            <option value="berlangsung" {{ $schedule->status == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                                            <option value="selesai" {{ $schedule->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                            <option value="ditunda" {{ $schedule->status == 'ditunda' ? 'selected' : '' }}>Ditunda</option>
                                                        </select>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @php
                                $allStagesCompleted = $poSchedules->every(function ($schedule) {
                                    return $schedule->status === 'selesai';
                                });
                            @endphp
                            @if($allStagesCompleted)
                                <div class="alert alert-success">
                                    Pesanan PO Jual #{{ $poId }} siap dikirim!
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        #calendar {
            margin: 20px 0; /* Tambahkan margin atas dan bawah */
        }

        .fc-event {
            padding: 2px 5px; /* Tambahkan padding di dalam event */
            border-radius: 3px; /* Sudut membulat */
            font-size: 0.85em; /* Ukuran font sedikit lebih kecil */
            color: white !important; /* Pastikan teks berwarna putih */
            border: none !important; /* Hapus border default jika ada */
        }

        .fc-event-title {
            font-weight: bold; /* Judul event lebih tebal */
            display: block; /* Pastikan judul mengambil baris baru jika perlu */
        }

        .fc-event-time {
            font-size: 0.8em; /* Ukuran font waktu lebih kecil */
            opacity: 0.9; /* Sedikit transparan */
            display: block; /* Pastikan waktu mengambil baris baru jika perlu */
        }

        /* Memastikan warna latar belakang event diterapkan */
        .fc-event.fc-event-start.fc-event-end {
             /* Aturan ini membantu menarget event harian */
             /* Latar belakang diatur melalui JavaScript, tapi ini bisa membantu */
        }

        /* Contoh styling berdasarkan warna jika diperlukan, tapi saat ini warna dari JS */
        /* .fc-event[style*="background-color: #C0392B"] { } */

    </style>
    @endpush
@endsection 