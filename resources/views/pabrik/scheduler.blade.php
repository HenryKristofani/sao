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

        <div class="card shadow-sm">
            <div class="card-body">
                {{-- FullCalendar will be rendered here --}}
                <div id="calendar" data-schedules="{{ json_encode($groupedSchedules) }}"></div>
            </div>
        </div>

        {{-- Detail Table View --}}
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Detail Jadwal Produksi</h5>
                @if($groupedSchedules->isEmpty())
                    <p class="text-muted">Tidak ada detail jadwal produksi saat ini.</p>
                @else
                    @foreach($groupedSchedules as $poId => $poSchedules)
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">PO Jual #{{ $poId }}</h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
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
                                    <i class="fas fa-check-circle me-2"></i>
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
        /* Calendar Container */
        #calendar {
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            padding: 20px;
        }

        /* Calendar Header */
        .fc .fc-toolbar {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px !important;
        }

        .fc .fc-toolbar-title {
            font-size: 1.5em !important;
            font-weight: 600;
            color: #2c3e50;
        }

        .fc .fc-button {
            padding: 8px 16px;
            font-weight: 500;
            text-transform: capitalize;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .fc .fc-button-primary {
            background-color: #3498db;
            border-color: #3498db;
        }

        .fc .fc-button-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        /* Calendar Grid */
        .fc .fc-daygrid-day {
            transition: background-color 0.2s ease;
        }

        .fc .fc-daygrid-day:hover {
            background-color: #f8f9fa;
        }

        .fc .fc-day-today {
            background-color: #e8f4f8 !important;
        }

        /* Events */
        .fc-event {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            color: white !important;
            border: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .fc-event-title {
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
        }

        .fc-event-time {
            font-size: 0.8em;
            opacity: 0.9;
            display: block;
        }

        /* Custom Modal */
        .event-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .event-modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .event-modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .event-modal-header h5 {
            margin: 0;
            color: #2c3e50;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .event-modal-body {
            padding: 20px;
        }

        .event-modal-body p {
            margin: 10px 0;
            color: #444;
        }

        /* Custom Tooltip */
        .custom-tooltip {
            position: fixed;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .tooltip-content {
            white-space: nowrap;
        }

        .tooltip-content strong {
            display: block;
            margin-bottom: 4px;
            color: #fff;
        }

        .tooltip-content p {
            margin: 2px 0;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #2c3e50;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 6px 10px;
            font-weight: 500;
        }

        .form-select-sm {
            min-width: 120px;
        }
    </style>
    @endpush
@endsection 