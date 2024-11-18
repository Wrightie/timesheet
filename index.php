<?php
require 'variables.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Calendar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.css" rel="stylesheet"> -->
    <style>
        #calendar {
            width: 70%;
            height: 100vh;
            float: left;
            border-right: 1px solid #ddd;
        }
        #task-list {
            width: 30%;
            height: 100vh;
            overflow-y: auto;
            float: right;
            padding: 15px;
        }
        .modal-dialog {
            max-width: 600px;
            margin: 1.75rem auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div id="calendar"></div>
        <div id="task-list">
            <h4><?php echo $user_name; ?></h4>
            <div class="accordion" id="taskAccordion">
                <!-- Example Task Group -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="group1Header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#group1Tasks" aria-expanded="true" aria-controls="group1Tasks">
                            Group 1
                        </button>
                    </h2>
                    <div id="group1Tasks" class="accordion-collapse collapse show" aria-labelledby="group1Header">
                        <div class="accordion-body">
                            <div class="task" data-id="1" draggable="true">Task Template 1</div>
                            <div class="task" data-id="2" draggable="true">Task Template 2</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><?php echo $modal_edit_event_title; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Placeholder for future content -->
                    <p>Edit form will go here in future steps.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>
    <script src="js/locales/it.global.min.js"></script>
    <!-- jQuery (optional for drag-and-drop) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let clickTimer = null; // Timer to differentiate single click from double click
            var calendarEl = document.getElementById('calendar');

            // Initialize the calendar
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'it',
                initialView: 'timeGridDay',
                editable: true, // Allows dragging within the calendar
                droppable: true, // Allows items to be dropped into the calendar
                headerToolbar: {
                    //left: 'timeGridDay,timeGridWeek,dayGridMonth',
                    left: 'timeGridDay,timeGridWeek',
                    center: 'title',
                    right: 'prev,next today'
                },
                events: '<?php echo $api_url; ?>?action=fetch_events', // Event source URL
                eventReceive: function(info) {
                    // Create event in the database
                    fetch('<?php echo $api_url; ?>?action=create_event', {
                        method: 'POST',
                        body: JSON.stringify({
                            title: info.event.title,
                            start_time: info.event.startStr,
                            end_time: info.event.endStr
                        }),
                        headers: { 'Content-Type': 'application/json' }
                    }).then(response => response.json()).then(data => {
                        info.event.setProp('id', data.id); // Set the event ID returned by the server
                    });
                },
                eventChange: function(info) {
                    // Update event in the database
                    fetch('<?php echo $api_url; ?>?action=update_event', {
                        method: 'POST',
                        body: JSON.stringify({
                            id: info.event.id,
                            title: info.event.title,
                            start_time: info.event.startStr,
                            end_time: info.event.endStr
                        }),
                        headers: { 'Content-Type': 'application/json' }
                    });
                },
                eventClick: function(info) {
                    if (clickTimer) {
                        // If timer exists, this is a double click
                        clearTimeout(clickTimer); // Clear the timer
                        clickTimer = null;

                        // Handle double click
                        var modal = new bootstrap.Modal(document.getElementById('editModal'), {});
                        modal.show();
                    } else {
                        // If no timer exists, this is a single click
                        clickTimer = setTimeout(() => {
                            clickTimer = null;

                            // Handle single click (edit title)
                            let newTitle = prompt("<?php echo $evt_single_click_change_title_msg; ?>", info.event.title);
                            if (newTitle) {
                                info.event.setProp('title', newTitle);
                                // Update the event title in the database
                                fetch('<?php echo $api_url; ?>?action=update_event', {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        id: info.event.id,
                                        title: newTitle,
                                        start_time: info.event.startStr,
                                        end_time: info.event.endStr
                                    }),
                                    headers: { 'Content-Type': 'application/json' }
                                });
                            }
                        }, 200); // Delay to differentiate single and double click (300ms)
                    }
                },
                eventRemove: function(info) {
                    // Delete event from the database
                    fetch(`<?php echo $api_url; ?>?action=delete_event&id=${info.event.id}`);
                },
                drop: function(info) {
                    //console.log('Item dropped on:', info.dateStr);

                    // Example of creating an event from a drop
                    calendar.addEvent({
                        //title: info.draggedEl.textContent, // Use the text from the dragged element
                        //start: info.dateStr
                    });
                }

            });

            calendar.render();

            // Enable dragging for tasks
            new FullCalendar.Draggable(document.getElementById('task-list'), {
                itemSelector: '.task',
                eventData: function(taskEl) {
                    return {
                        title: taskEl.textContent.trim(), // Use the task's text as the title
                        duration: '02:00' // Example duration
                    };
                }
            });

        });
    </script>
</body>
</html>
