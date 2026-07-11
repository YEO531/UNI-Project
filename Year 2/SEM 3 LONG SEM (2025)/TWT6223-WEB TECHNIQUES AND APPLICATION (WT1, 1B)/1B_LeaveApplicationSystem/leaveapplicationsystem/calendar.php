<?php
require_once 'includes/config.php';

if (!is_logged_in()) {
    redirect_with_message('login.php', 'Please log in to view your leave calendar.', 'error');
}

$user = get_logged_in_user();

// Fetch all leave applications for the user
$leaves = [];
$sql = "SELECT la.id, lt.name AS leave_type, la.start_date, la.end_date, la.status, la.reason
        FROM leave_applications la
        JOIN leave_types lt ON la.leave_type_id = lt.id
        WHERE la.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $leaves[] = $row;
}
$stmt->close();

// Fetch all notes for the user
$notes = [];
$sql = "SELECT id, note_date, note FROM calendar_notes WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}
$stmt->close();

// Fetch all public holidays
$public_holidays = [];
$sql = "SELECT * FROM public_holidays";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $public_holidays[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Calendar - LeaveTrack</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        #calendar {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }
    </style>
</head>
<body>
<?php include 'includes/taskbar.php'; ?>

    <div id='calendar'></div>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'refreshBtn dayGridMonth,timeGridWeek,timeGridDay'
                },
                customButtons: {
                    refreshBtn: {
                        text: 'Refresh',
                        click: function() {
                            location.reload();
                        }
                    }
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day'
                },
                events: [
                    // Leave events
                    <?php foreach ($leaves as $leave): ?>
                    {
                        title: '<?php echo addslashes($leave['leave_type'] . " (" . ucfirst($leave['status']) . ")"); ?>',
                        start: '<?php echo $leave['start_date']; ?>',
                        end: '<?php echo date('Y-m-d', strtotime($leave['end_date'] . ' +1 day')); ?>',
                        description: '<?php echo addslashes($leave['reason']); ?>',
                        color: '<?php echo $leave['status'] === "approved" ? "#4caf50" : ($leave['status'] === "rejected" ? "#f44336" : "#ff9800"); ?>'
                    },
                    <?php endforeach; ?>

                    // Public holiday events
                    <?php foreach ($public_holidays as $holiday): ?>
                    {
                        title: '<?php echo addslashes($holiday['name']); ?>',
                        start: '<?php echo $holiday['date']; ?>',
                        end: '<?php echo date('Y-m-d', strtotime($holiday['date'] . ' +1 day')); ?>',
                        description: 'Public Holiday',
                        color: '#43d16e',
                        textColor: '#fff',
                        allDay: true
                    },
                    <?php endforeach; ?>

                    // Note events
                    <?php foreach ($notes as $note): ?>
                    {
                        id: '<?php echo $note['id']; ?>',
                        title: 'Note',
                        start: '<?php echo $note['note_date']; ?>',
                        end: '<?php echo date('Y-m-d', strtotime($note['note_date'] . ' +1 day')); ?>',
                        description: '<?php echo addslashes($note['note']); ?>',
                        color: '#2196f3',
                        allDay: true
                    },
                    <?php endforeach; ?>
                ],
                eventDidMount: function(info) {
                    if (info.event.extendedProps.description) {
                        var tooltip = document.createElement('div');
                        tooltip.innerHTML = info.event.extendedProps.description;
                        tooltip.style.position = 'absolute';
                        tooltip.style.background = '#fff';
                        tooltip.style.border = '1px solid #ccc';
                        tooltip.style.padding = '6px 10px';
                        tooltip.style.borderRadius = '6px';
                        tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                        tooltip.style.display = 'none';
                        tooltip.style.zIndex = 1000;
                        document.body.appendChild(tooltip);
                        info.el.addEventListener('mouseenter', function(e) {
                            tooltip.style.display = 'block';
                            tooltip.style.left = e.pageX + 10 + 'px';
                            tooltip.style.top = e.pageY + 10 + 'px';
                        });
                        info.el.addEventListener('mousemove', function(e) {
                            tooltip.style.left = e.pageX + 10 + 'px';
                            tooltip.style.top = e.pageY + 10 + 'px';
                        });
                        info.el.addEventListener('mouseleave', function() {
                            tooltip.style.display = 'none';
                        });
                    }
                },
                dateClick: function(info) {
                    var note = prompt('Add a note for ' + info.dateStr + ':');
                    if (note && note.trim() !== '') {
                        // Save note via AJAX
                        fetch('api/add_note.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ date: info.dateStr, note: note })
                        })
                        .then(response => response.json())
                        .then(data => {
                            location.reload();
                        });
                    }
                },
                eventClick: function(info) {
                    // Only allow editing/deleting for notes
                    if (info.event.title === 'Note') {
                        const currentText = info.event.extendedProps.description || '';
                        const newText = prompt('Edit note text (leave blank and press OK to delete):', currentText);

                        if (newText === null) {
                            // User cancelled
                            return;
                        }

                        if (newText === '') {
                            // If empty, ask to delete
                            if (confirm('Delete this note?')) {
                                fetch('api/add_note.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        action: 'delete',
                                        note_id: info.event.id
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    alert(data.message);
                                    if (data.success) location.reload();
                                });
                            }
                        } else if (newText !== currentText) {
                            // Edit note
                            fetch('api/add_note.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: 'edit',
                                    note_id: info.event.id,
                                    note: newText
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                alert(data.message);
                                if (data.success) location.reload();
                            });
                        }
                    }
                }
            });
            calendar.render();
        });
    </script>
</body>
</html> 