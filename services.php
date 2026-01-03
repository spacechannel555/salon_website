<?php
require_once __DIR__ . '/crud_functions.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
if ($action === 'for_stylist' && isset($_GET['stylistid'])) {
    $sid = intval($_GET['stylistid']);
    $services = StylistService::getServicesByStylist($sid);
    echo json_encode(['success' => true, 'services' => $services]);
} else {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
}
?>

