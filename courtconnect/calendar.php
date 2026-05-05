<?php
require_once "db_connect.php";
$conn = connectDB();

$sql = "SELECT title, event_type, `description`, event_date, start_time, end_time, `location`, max_capacity FROM events";
$result = $conn->query($sql);
$arr = $result->fetchAll();

$events = [];

foreach ($arr as $row) {
    $events[] = [
        'title' => $row['title'],
        'event_type' => $row['event_type'],
        'description' => $row['description'],
        'start' => $row['event_date'] . 'T' . $row['start_time'],
        'end'   => $row['event_date'] . 'T' . $row['end_time'],
        'location' => $row['location'],
        'capacity' => $row['max_capacity']
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
exit;
?>