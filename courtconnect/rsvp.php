<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in to RSVP."
    ]);
    exit;
}

require_once "db_connect.php";

$conn = connectDB();
$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'];
$status = $_POST['status'];
$sql = "
INSERT INTO rsvps (user_id, event_id, status)
VALUES (?, ?, ?)

ON DUPLICATE KEY UPDATE
status = VALUES(status)
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $user_id,
    $event_id,
    $status
]);

echo json_encode([
    "success" => true,
    "message" => "RSVP Saved"
]);

?>