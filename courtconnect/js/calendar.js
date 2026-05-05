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
            alert(
            event.title + "\n" +
            "Description: " + (event.extendedProps.description || "None") + "\n" +
            "Start: " + event.start.toLocaleString() + "\n" +
            "End: " + event.end.toLocaleString() + "\n" +
            "Location: " + (event.extendedProps.location || "TBA") + "\n" +
            "Capacity: " + (event.extendedProps.capacity || "Unlimited")
            );
        }
    });

    calendar.render();
});