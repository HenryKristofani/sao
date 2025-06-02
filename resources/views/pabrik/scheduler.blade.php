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
                {{-- Simplified Calendar View --}}
                <h5>Visualisasi Kalender</h5>
                <div class="calendar-container mb-4">
                    @php
                        // Determine date range (e.g., current week + next 3 weeks)
                        $startDate = \Carbon\Carbon::now()->startOfWeek();
                        $endDate = $startDate->copy()->addWeeks(3)->endOfWeek();
                        $dates = collect();
                        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                            $dates->push($date->copy());
                        }

                        // Prepare schedules for easy lookup by date
                        $schedulesByDate = [];
                        foreach ($groupedSchedules as $poId => $poSchedules) {
                             foreach ($poSchedules as $schedule) {
                                // Assuming schedule spans from start to end date inclusive
                                $current = \Carbon\Carbon::parse($schedule->tanggal_mulai);
                                $end = \Carbon\Carbon::parse($schedule->tanggal_selesai);
                                while ($current->lte($end)) {
                                    $schedulesByDate[$current->toDateString()][] = ['schedule' => $schedule, 'poId' => $poId];
                                    $current->addDay();
                                }
                             }
                        }
                    @endphp

                    <div class="calendar-header">
                        @foreach($dates->take(7) as $date)
                            <div class="day-header">{{ $date->format('D') }}<br>{{ $date->format('M d') }}</div>
                        @endforeach
                    </div>

                    <div class="calendar-body">
                         @foreach($dates->chunk(7) as $week)
                            <div class="calendar-week">
                                @foreach($week as $date)
                                    <div class="calendar-day">
                                        <div class="day-number {{ $date->isToday() ? 'today' : '' }}">{{ $date->day }}</div>
                                        <div class="day-content">
                                            {{-- Display schedules for this date --}}
                                            @if(isset($schedulesByDate[$date->toDateString()]))
                                                @foreach($schedulesByDate[$date->toDateString()] as $item)
                                                    @php
                                                        $schedule = $item['schedule'];
                                                        $poId = $item['poId'];
                                                        // Simple color based on stage
                                                        $color = '#ccc'; // Default gray
                                                        switch($schedule->catatan) {
                                                            case 'Tahap Beli': $color = '#ff6347'; break; // Tomato
                                                            case 'Tahap Produksi': $color = '#ffd700'; break; // Gold
                                                            case 'Tahap Packing': $color = '#6495ed'; break; // CornflowerBlue
                                                            case 'Tahap Kirim': $color = '#32cd32'; break; // LimeGreen
                                                        }
                                                    @endphp
                                                    <div class="schedule-block" style="background-color: {{ $color }};" title="PO {{ $poId }} - {{ $schedule->catatan }}">
                                                        <small>PO {{ $poId }}</small><br>
                                                        {{ $schedule->catatan }}
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                         @endforeach
                    </div>
                </div>

                <style>
                    .calendar-container {
                        border: 1px solid #e0e0e0;
                        border-radius: 5px;
                        overflow: hidden;
                        font-size: 0.8em;
                    }
                    .calendar-header, .calendar-week {
                        display: grid;
                        grid-template-columns: repeat(7, 1fr);
                        border-bottom: 1px solid #e0e0e0;
                    }
                    .calendar-header .day-header {
                        text-align: center;
                        padding: 10px 0;
                        font-weight: bold;
                    }
                    .calendar-day {
                        border-right: 1px solid #e0e0e0;
                        padding: 5px;
                        min-height: 100px; /* Adjust as needed */
                        position: relative;
                    }
                     .calendar-day:last-child {
                        border-right: none;
                    }
                    .day-number {
                        font-size: 1.2em;
                        font-weight: bold;
                        text-align: right;
                        margin-bottom: 5px;
                    }
                     .day-number.today {
                        color: blue; /* Highlight today */
                    }
                    .day-content {
                        /* Flex or grid could be used here for better alignment */
                    }
                    .schedule-block {
                        padding: 3px;
                        margin-bottom: 3px;
                        border-radius: 3px;
                        color: white; /* Adjust text color for readability */
                        overflow: hidden; /* Hide overflowing text */
                         text-overflow: ellipsis;
                         white-space: nowrap;
                    }
                     .schedule-block small {
                         font-size: 0.7em;
                     }
                </style>

                {{-- Existing Table View --}}
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
                                                <td>{{ $schedule->catatan ?? 'N/A' }}</td> {{-- Using 'catatan' for stage name --}}
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
                                                <td>{{ $schedule->catatan }}</td> {{-- Displaying original catatan as well --}}
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
@endsection 