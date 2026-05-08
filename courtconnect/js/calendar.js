document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        height: 'auto',

        headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'timeGridDay,timeGridWeek',
        },

        buttonText: {
        today: 'Today',
        day: 'Day',
        week: 'Week'
        },

        slotMinTime: "06:00:00", // start time
        slotMaxTime: "22:00:00", // end time

        nowIndicator: true, // red current-time line

        editable: true, // drag events
        selectable: true, // click & drag to create
        
        events: 'calendar.php',

        eventClick: function(info) {
            const event = info.event;
            currentEventId = event.id;
            document.getElementById('modalTitle').innerText =
                event.title;
            document.getElementById('modalDescription').innerText =
                event.extendedProps.description || "None";
            document.getElementById('modalStart').innerText =
                event.start.toLocaleString();
            document.getElementById('modalEnd').innerText =
                event.end.toLocaleString();
            document.getElementById('modalLocation').innerText =
                event.extendedProps.location || "TBA";
            document.getElementById('modalCapacity').innerText =
                event.extendedProps.capacity || "Unlimited";
            document.getElementById('eventModal').style.display = 'block';
        }
    });

    calendar.render();

    let currentEventId = null;
    const modal = document.getElementById('eventModal');
    document.querySelector('.close').onclick = function() {
        modal.style.display = 'none';
        document.getElementById('rsvpMessage').innerText = '';
    };
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            document.getElementById('rsvpMessage').innerText = '';
        }
    };
    function sendRSVP(status) {
    fetch('rsvp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body:
            'event_id=' + encodeURIComponent(currentEventId) +
            '&status=' + encodeURIComponent(status)
    })
    .then(response => response.json())
    .then(data => {
        const msg = document.getElementById('rsvpMessage');
        msg.innerText = data.message;
        if (data.success) {
            msg.style.color = "green";
        } else {
            msg.style.color = "red";
        }
    })
    .catch(error => {
        console.error(error);
    });
    }
    document.getElementById('yesBtn').onclick = function() {
        sendRSVP('Yes');
    };
    document.getElementById('maybeBtn').onclick = function() {
        sendRSVP('Maybe');
    };
    document.getElementById('noBtn').onclick = function() {
        sendRSVP('No');
    };
});