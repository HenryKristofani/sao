import './bootstrap';

// Import FullCalendar libraries
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction'; // for clickable events

// Import Bootstrap CSS and JS (for tooltips)
import 'bootstrap/dist/css/bootstrap.min.css';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
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

        function addOneDay(dateTimeStr) {
            var dateStr = dateTimeStr.split(' ')[0];
            var parts = dateStr.split('-');
            var dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
            dateObj.setDate(dateObj.getDate() + 1);
            var month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
            var day = dateObj.getDate().toString().padStart(2, '0');
            return dateObj.getFullYear() + '-' + month + '-' + day;
        }

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

                    // Calculate end date (+1 day for FullCalendar exclusivity)
                    var endDateStr = addOneDay(schedule.tanggal_selesai);

                    events.push({
                        title: 'PO ' + poId + ' - ' + schedule.catatan,
                        start: schedule.tanggal_mulai.split(' ')[0],
                        end: endDateStr,
                        allDay: true,
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
            locale: 'id',
            events: events,
            height: 'auto',
            contentHeight: 600,
            aspectRatio: 1.8,
            expandRows: true,
            stickyHeaderDates: true,
            dayMaxEvents: true,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            eventDisplay: 'block',
            eventClick: function(info) {
                // Create a custom modal instead of using alert
                const modal = document.createElement('div');
                modal.className = 'event-modal';
                modal.innerHTML = `
                    <div class="event-modal-content">
                        <div class="event-modal-header">
                            <h5>${info.event.title}</h5>
                            <button class="close-modal">&times;</button>
                        </div>
                        <div class="event-modal-body">
                            <p><strong>Status:</strong> ${info.event.extendedProps.status}</p>
                            <p><strong>Mesin:</strong> ${info.event.extendedProps.machine}</p>
                            <p><strong>Durasi:</strong> ${info.event.extendedProps.duration} hari</p>
                            <p><strong>Prioritas:</strong> ${info.event.extendedProps.priority}</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                
                // Add event listener to close modal
                modal.querySelector('.close-modal').addEventListener('click', () => {
                    modal.remove();
                });
                
                // Close modal when clicking outside
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
            },
            eventDidMount: function(info) {
                // Add custom tooltip
                const tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.innerHTML = `
                    <div class="tooltip-content">
                        <strong>${info.event.title}</strong>
                        <p>Status: ${info.event.extendedProps.status}</p>
                        <p>Mesin: ${info.event.extendedProps.machine}</p>
                    </div>
                `;
                
                info.el.addEventListener('mouseenter', () => {
                    document.body.appendChild(tooltip);
                    const rect = info.el.getBoundingClientRect();
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                });
                
                info.el.addEventListener('mouseleave', () => {
                    tooltip.remove();
                });
            }
        });

        calendar.render();
    }
});
