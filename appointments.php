<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/crud_functions.php';
require_once __DIR__ . '/mailer.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = require __DIR__ . '/config.php';
Auth::start();

$action = $_REQUEST['action'] ?? '';

header('Content-Type: application/json');

switch($action) {

    case 'fetch':
        $date = $_GET['date'] ?? date('Y-m-d');
        $stylists = Stylist::getAll();
        $aps = Appointment::getForDate($date);
        echo json_encode(['success'=>true, 'stylists'=>$stylists, 'appointments'=>$aps]);
        break;

    case 'admin_search':
        if (!$isAdmin) {
            echo json_encode(['success'=>false, 'error'=>'Unauthorized']);
            break;
        }

        $term = $_GET['term'] ?? '';
        $term = "%$term%";

        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT userid, firstname, lastname, email, phone
            FROM users
            WHERE firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR phone LIKE ?
            LIMIT 30
        ");
        $stmt->bind_param("ssss", $term, $term, $term, $term);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['success'=>true, 'results'=>$res]);
        break;


    case 'create':
        $userid = $_POST['userid'] ?: null;
        $guest_name = trim($_POST['guest_name'] ?? '');
        $guest_email = trim($_POST['guest_email'] ?? '');
        $stylistid = intval($_POST['stylistid']);
        $serviceid = intval($_POST['serviceid']);
        $datetime = $_POST['datetime'];

        if (!$userid) {
            $newUser = \User::createGuest($guest_name, $guest_email);
            $userid = $newUser['userid'];
        }

        $create = Appointment::create($userid, $stylistid, $serviceid, $datetime);
        if (!$create['success']) {
            echo json_encode(['success'=>false, 'error'=>$create['error'] ?? 'Unable to create']);
            break;
        }

        // send confirmation email
        $user = User::getById($userid);
        $mailer = new Mailer($config);
        $subject = "Appointment confirmation &mdash; " . date('M j, Y g:ia', strtotime($datetime));
        $html = "<p>Hi " . htmlspecialchars($user['firstname'] ?? $guest_name) . ",</p>";
        $html .= "<p>Your appointment is confirmed with stylist ID {$stylistid} on " . date('M j, Y g:ia', strtotime($datetime)) . ".</p>";
        $html .= "<p>Thank you,<br/>NLA Salon</p>";
        $mailRes = $mailer->send(($user['email'] ?? $guest_email), ($user['firstname'] ?? $guest_name), $subject, $html);

        echo json_encode(['success'=>true, 'mail'=>$mailRes]);
        break;

    case 'cancel':
        $apptid = intval($_POST['apptid']);

        // Get appointment details BEFORE deleting it
        $appt = Appointment::getById($apptid);
        if (!$appt) {
            echo json_encode(['success'=>false, 'error'=>'Appointment not found']);
            break;
        }

        // Perform cancellation
        $res = Appointment::cancel($apptid);

        if ($res) {
            // Send cancellation email
            $user = User::getById($appt['userid']);
            $mailer = new Mailer($config);
            $subject = "Appointment Cancelled — " . date('M j, Y g:ia', strtotime($appt['appt_datetime']));
            $html  = "<p>Hi " . htmlspecialchars($user['firstname'] ?? $guest_name) . ",</p>";
            $html .= "<p>Your appointment on " . date('M j, Y g:ia', strtotime($appt['appt_datetime'])) . " has been successfully cancelled.</p>";
            $html .= "<p>If this was an error, feel free to log back in and rebook any available time.</p>";
            $html .= "<p>Thank you,<br>NLA Salon</p>";
            $mailRes = $mailer->send(($user['email'] ?? $guest_email), ($user['firstname'] ?? $guest_name),$subject,$html);

            echo json_encode(['success' => true, 'mail' => $mailRes]);

        } else {
            echo json_encode(['success'=>false, 'error'=>'Unable to cancel.']);
        }

        break;

    default:
        echo json_encode(['success'=>false, 'error'=>'Invalid action']);
}

