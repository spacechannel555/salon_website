<?php
require_once __DIR__ . '/database.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
class User {

    public static function create($firstname, $lastname, $email, $password, $phone=null) {
        $conn = Database::connect();

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (firstname, lastname, email, password_hash, phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $firstname, $lastname, $email, $hash, $phone);
        return $stmt->execute();
    }

    public static function createGuest($firstname, $email) {
    $conn = Database::connect();
    $fake = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        INSERT INTO users (firstname, lastname, email, password_hash)
        VALUES (?, '', ?, ?)
    ");
    $stmt->bind_param("sss", $firstname, $email, $fake);
    $ok = $stmt->execute();
    if ($ok) {
        return ['userid' => $conn->insert_id];
    }
    return false;
}

    public static function getByEmail($email) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function getById($userid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function update($userid, $firstname, $lastname, $email, $phone) {
    $conn = Database::connect();
    $stmt = $conn->prepare("
        UPDATE users
        SET firstname = ?, lastname = ?, email = ?, phone = ?
        WHERE userid = ?
    ");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $phone, $userid);
    return $stmt->execute();
    }


    public static function changePassword($userid, $oldPassword, $newPassword) {
    $conn = Database::connect();

    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE userid = ? LIMIT 1");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $stmt->bind_result($hash);
    if (!$stmt->fetch()) {
        return ['success' => false, 'error' => 'User not found.'];
    }
    $stmt->close();

    if (!password_verify($oldPassword, $hash)) {
        return ['success' => false, 'error' => 'Old password incorrect.'];
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE userid = ?");
    $stmt->bind_param("si", $newHash, $userid);
    $ok = $stmt->execute();
    return ['success' => (bool)$ok];
    }

    public static function delete($userid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM users WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        return $stmt->execute();
    }
}

class Stylist {

    public static function create($firstname, $lastname) {
        $conn = Database::connect();
        $stmt = $conn->prepare("INSERT INTO stylists (firstname, lastname) VALUES (?, ?)");
        $stmt->bind_param("ss", $firstname, $lastname);
        return $stmt->execute();
    }

    public static function get($stylistid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM stylists WHERE stylistid = ?");
        $stmt->bind_param("i", $stylistid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function getAll() {
        $conn = Database::connect();
        $result = $conn->query("SELECT * FROM stylists ORDER BY firstname");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function getWorkingToday($date) {
    $conn = Database::connect();
    $result = $conn->query("SELECT stylistid, firstname, lastname FROM stylists ORDER BY firstname");
    return $result->fetch_all(MYSQLI_ASSOC);
    }


    public static function update($stylistid, $firstname, $lastname) {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE stylists SET firstname=?, lastname=? WHERE stylistid=?");
        $stmt->bind_param("ssi", $firstname, $lastname, $stylistid);
        return $stmt->execute();
    }

    public static function delete($stylistid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM stylists WHERE stylistid=?");
        $stmt->bind_param("i", $stylistid);
        return $stmt->execute();
    }
}

class Service {

    public static function create($name, $duration) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT INTO services (service_name, duration)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("si", $name, $duration);
        return $stmt->execute();
    }

    public static function get($serviceid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM services WHERE serviceid = ?");
        $stmt->bind_param("i", $serviceid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function getAll() {
        $conn = Database::connect();
        $result = $conn->query("SELECT * FROM services ORDER BY service_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function getByStylist($stylistid) {
    $conn = Database::connect();
    $stmt = $conn->prepare("
        SELECT s.serviceid, s.service_name, s.duration, s.price
        FROM services s
        JOIN stylist_service ss ON ss.serviceid = s.serviceid
        WHERE ss.stylistid = ?
        ORDER BY s.service_name
    ");
    $stmt->bind_param("i", $stylistid);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public static function update($serviceid, $name, $duration) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            UPDATE services SET service_name=?, duration=? WHERE serviceid=?
        ");
        $stmt->bind_param("sii", $name, $duration, $serviceid);
        return $stmt->execute();
    }

    public static function delete($serviceid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM services WHERE serviceid=?");
        $stmt->bind_param("i", $serviceid);
        return $stmt->execute();
    }
}

class StylistService {

    public static function assign($stylistid, $serviceid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT IGNORE INTO stylist_service (stylistid, serviceid)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $stylistid, $serviceid);
        return $stmt->execute();
    }

    public static function remove($stylistid, $serviceid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            DELETE FROM stylist_service 
            WHERE stylistid=? AND serviceid=?
        ");
        $stmt->bind_param("ii", $stylistid, $serviceid);
        return $stmt->execute();
    }

    public static function getServicesByStylist($stylistid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT s.* 
            FROM services s
            JOIN stylist_service ss ON ss.serviceid = s.serviceid
            WHERE ss.stylistid = ?
        ");
        $stmt->bind_param("i", $stylistid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

class Appointment {

    public static function isAvailable($stylistid, $serviceid, $datetime) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT duration FROM services WHERE serviceid=?");
        $stmt->bind_param("i", $serviceid);
        $stmt->execute();
        $stmt->bind_result($duration);
        $stmt->fetch();
        $stmt->close();

        $start = new DateTime($datetime);
        $end   = (clone $start)->modify("+{$duration} minutes");

        $stmt = $conn->prepare("
            SELECT a.appt_datetime, s.duration
            FROM appointments a
            JOIN services s ON a.serviceid = s.serviceid
            WHERE a.stylistid = ?
            AND a.status = 'booked'
        ");
        $stmt->bind_param("i", $stylistid);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $existingStart = new DateTime($row['appt_datetime']);
            $existingEnd   = (clone $existingStart)->modify("+{$row['duration']} minutes");

            if ($start < $existingEnd && $end > $existingStart) {
                return false;
            }
        }

        return true;
    }

    public static function create($userid, $stylistid, $serviceid, $datetime) {
        if (!self::isAvailable($stylistid, $serviceid, $datetime)) {
            return ["success" => false, "error" => "Stylist is unavailable for that time slot."];
        }

        $conn = Database::connect();

        $stmt = $conn->prepare("
            INSERT INTO appointments (userid, stylistid, serviceid, appt_datetime)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiis", $userid, $stylistid, $serviceid, $datetime);

        return ["success" => $stmt->execute()];
    }

    public static function getByUser($userid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT a.*, s.service_name, st.firstname AS stylist_firstname, st.lastname AS stylist_lastname
            FROM appointments a
            JOIN services s ON a.serviceid = s.serviceid
            JOIN stylists st ON a.stylistid = st.stylistid
            WHERE a.userid = ?
            ORDER BY a.appt_datetime
        ");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function cancel($apptid) {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE apptid=?");
        $stmt->bind_param("i", $apptid);
        return $stmt->execute();
    }

    public static function getById($apptid) {
    $conn = Database::connect();
    $stmt = $conn->prepare("
        SELECT a.*, s.service_name, s.duration, st.firstname AS stylist_firstname, st.lastname AS stylist_lastname
        FROM appointments a
        JOIN services s ON a.serviceid = s.serviceid
        JOIN stylists st ON st.stylistid = a.stylistid
        WHERE a.apptid = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $apptid);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
    }

    public static function getByStylistAndDay($stylistid, $date) {
    $conn = Database::connect();
    $stmt = $conn->prepare("
        SELECT a.*, s.service_name, s.duration, u.firstname AS user_firstname, u.lastname AS user_lastname
        FROM appointments a
        JOIN services s ON a.serviceid = s.serviceid
        JOIN users u ON u.userid = a.userid
        WHERE a.stylistid = ?
        AND DATE(a.appt_datetime) = ?
        ORDER BY a.appt_datetime
    ");
    $stmt->bind_param("is", $stylistid, $date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getForDate($date) {
        $conn = Database::connect();
        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';
        $stmt = $conn->prepare("
        SELECT a.*, s.service_name, s.duration, u.firstname AS user_firstname, u.email AS user_email, st.firstname AS stylist_firstname, st.lastname AS stylist_lastname
        FROM appointments a
        JOIN services s ON a.serviceid = s.serviceid
        JOIN stylists st ON a.stylistid = st.stylistid
        LEFT JOIN users u ON a.userid = u.userid
        WHERE a.appt_datetime BETWEEN ? AND ? AND a.status='booked'
        ");
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $byStylist = [];
        foreach ($rows as $r) {
            $byStylist[$r['stylistid']][] = $r;
        }
        return $byStylist;
    }

}

?>