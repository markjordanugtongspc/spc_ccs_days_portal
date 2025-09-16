<?php
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../config.php';

try {
    $pdo = getDbConnection();
    
    if (!isset($_GET['event_id'])) {
        echo json_encode(['success' => false, 'message' => 'Event ID required']);
        exit;
    }
    
    $eventId = $_GET['event_id'];
    
    // Get event time windows
    $stmtEvent = $pdo->prepare("SELECT signin_start, signin_end, signout_start, signout_end FROM events WHERE id = ?");
    $stmtEvent->execute([$eventId]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    
    $currentTime = date('H:i:s');
    $triggerAction = null;
    
    // Check if current time is within sign-in window
    if ($event['signin_start'] && $event['signin_end']) {
        if ($currentTime >= $event['signin_start'] && $currentTime <= $event['signin_end']) {
            $triggerAction = 'signin';
        }
    }
    
    // Check if current time is within sign-out window
    if ($event['signout_start'] && $event['signout_end']) {
        if ($currentTime >= $event['signout_start'] && $currentTime <= $event['signout_end']) {
            $triggerAction = 'signout';
        }
    }
    
    echo json_encode([
        'success' => true,
        'trigger_action' => $triggerAction,
        'current_time' => $currentTime
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>