<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/ru.global.min.js"></script>

<script>
window.__calendarScreenInstance = window.__calendarScreenInstance || null;

function initCalendarScreen() {
    var calendarEl = document.getElementById('calendar');

    if (!calendarEl || typeof FullCalendar === 'undefined') {
        return;
    }

    if (window.__calendarScreenInstance) {
        window.__calendarScreenInstance.destroy();
        window.__calendarScreenInstance = null;
    }

    window.__calendarScreenInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ru',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Сегодня',
            month: 'Месяц',
            week: 'Неделя',
            day: 'День'
        },
        events: function(info, successCallback, failureCallback) {
            fetch('/admin/api/calendar/events?start=' + info.startStr + '&end=' + info.endStr)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            var eventId = info.event.id;
            window.location.href = '/admin/actual-events/' + eventId + '/edit';
        },
        dateClick: function(info) {
            var confirmed = confirm('Создать мероприятие на ' + info.dateStr + '?');
            if (confirmed) {
                window.location.href = '/admin/actual-events/create?date=' + info.dateStr;
            }
        },
        height: 'auto',
        eventColor: '#0d6efd',
        eventDisplay: 'block'
    });

    window.__calendarScreenInstance.render();
}

document.addEventListener('DOMContentLoaded', initCalendarScreen);
document.addEventListener('turbo:load', initCalendarScreen);
document.addEventListener('turbo:before-cache', function() {
    if (window.__calendarScreenInstance) {
        window.__calendarScreenInstance.destroy();
        window.__calendarScreenInstance = null;
    }
});
</script>

<style>
#calendar {
    max-width: 100%;
    margin: 0 auto;
}

.fc-event {
    cursor: pointer;
}

.fc-event:hover {
    opacity: 0.8;
}
</style>
