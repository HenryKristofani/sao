import './bootstrap';

// Import FullCalendar libraries
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction'; // for clickable events

// Import Bootstrap CSS and JS (for tooltips)
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
// import $ from 'jquery'; // Assuming jQuery is available for Bootstrap tooltips

// Initialize FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        // Get schedules data from data attribute
        var schedulesData = calendarEl.dataset.schedules;
        var groupedSchedules = JSON.parse(schedulesData);

        var events = [];

        // Convert grouped schedules to FullCalendar events format
        for (var poId in groupedSchedules) {
            if (groupedSchedules.hasOwnProperty(poId)) {
                groupedSchedules[poId].forEach(function(schedule) {
                    var color = '#BDC3C7'; // Default Silver

                    // Check schedule.catatan for keywords to determine color
                    if (schedule.catatan) {
                        var catatanLower = schedule.catatan.toLowerCase();
                        if (catatanLower.includes('beli')) {
                            color = '#C0392B'; // Dark Red
                        } else if (catatanLower.includes('produksi')) {
                            color = '#3498DB'; // Bright Blue
                        } else if (catatanLower.includes('packing') || catatanLower.includes('pengemasan')) {
                            color = '#27AE60'; // Emerald Green
                        } else if (catatanLower.includes('kirim') || catatanLower.includes('pengiriman')) {
                            color = '#8E44AD'; // Purple
                        }
                    }

                    events.push({
                        title: 'PO ' + poId + ' - ' + schedule.catatan,
                        start: schedule.tanggal_mulai,
                        // end: schedule.tanggal_selesai, // FullCalendar end is exclusive, often needs adjustment
                        // Let's use the end date as is for now, may need adjustment later
                        end: schedule.tanggal_selesai,
                        backgroundColor: color,
                        borderColor: 'transparent',
                        textColor: '#fff',
                        extendedProps: {
                            id: schedule.id_jadwal,
                            status: schedule.status,
                            machine: schedule.nama_mesin,
                            duration: schedule.estimasi_durasi,
                            priority: schedule.prioritas
                        }
                    });
                });
            }
        }

        var calendar = new Calendar(calendarEl, {
            plugins: [ dayGridPlugin, interactionPlugin ],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'id', // Set locale to Indonesian
            events: events,
            eventClick: function(info) {
                // Tampilkan detail event dalam alert (bisa diganti modal)
                alert(
                    'PO: ' + info.event.title + '\n' +
                    'Status: ' + info.event.extendedProps.status + '\n' +
                    'Mesin: ' + info.event.extendedProps.machine + '\n' +
                    'Durasi: ' + info.event.extendedProps.duration + ' hari\n' +
                    'Prioritas: ' + info.event.extendedProps.priority
                );
            },
            eventDidMount: function(info) {
                // Tambahkan tooltip (membutuhkan jQuery dan Bootstrap Tooltip) - Removed jQuery dependency
                 // $(info.el).tooltip({
                 //     title: info.event.title,
                 //     placement: 'top',
                 //     trigger: 'hover',
                 //     container: 'body'
                 // });

                 // You might need to initialize Bootstrap tooltips differently without jQuery
                 // Or use a different tooltip library
            }
        });

        calendar.render();
    }
});
