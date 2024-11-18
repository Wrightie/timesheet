<?php
//show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetch_events':
        fetchEvents($pdo);
        break;
    case 'create_event':
        createEvent($pdo);
        break;
    case 'update_event':
        updateEvent($pdo);
        break;
    case 'delete_event':
        deleteEvent($pdo);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function fetchEvents($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM calendar_events");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map database fields to FullCalendar's format
        $mappedEvents = array_map(function($event) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_time'],
                'end' => $event['end_time'],
                'notes' => $event['notes'],  // Optional custom field
                'color' => '#ff9f89' // Default color; update based on category_id if needed
            ];
        }, $events);

        echo json_encode($mappedEvents);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function createEvent($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO calendar_events (task_id, title, start_time, end_time, category_id, notes) 
                           VALUES (:task_id, :title, :start_time, :end_time, :category_id, :notes)");
    $stmt->execute([
        ':task_id' => $data['task_id'] ?? null,
        ':title' => $data['title'],
        ':start_time' => $data['start_time'],
        ':end_time' => $data['end_time'],
        ':category_id' => $data['category_id'] ?? null,
        ':notes' => $data['notes']
    ]);
    echo json_encode(['id' => $pdo->lastInsertId()]);
}

function updateEvent($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE calendar_events 
                           SET title = :title, start_time = :start_time, end_time = :end_time, 
                               category_id = :category_id, notes = :notes 
                           WHERE id = :id");
    $stmt->execute([
        ':title' => $data['title'],
        ':start_time' => $data['start_time'],
        ':end_time' => $data['end_time'],
        ':category_id' => $data['category_id'],
        ':notes' => $data['notes'],
        ':id' => $data['id']
    ]);
    echo json_encode(['status' => 'success']);
}

function deleteEvent($pdo) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['status' => 'success']);
}
?>
