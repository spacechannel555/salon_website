<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/crud_functions.php';
require_once __DIR__ . '/config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
Auth::requireLogin();

$userId = Auth::currentUserId();
$user = User::getById($userId);
$errors = [];
$success = '';

Auth::start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF simple token (session)
    if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf_token'] ?? '')) {
        $errors[] = "Invalid request.";
    } else {
        if (isset($_POST['update_profile'])) {
            $firstname = trim($_POST['firstname'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            // Basic validation
            if (!$firstname || !$lastname || !$email) {
                $errors[] = "Please provide first name, last name and email.";
            } else {
                $ok = User::update($userId, $firstname, $lastname, $email, $phone);
                if ($ok) {
                    $success = "Profile updated.";
                    $user = User::getById($userId); // refresh
                } else $errors[] = "Unable to update profile.";
            }
        }

        if (isset($_POST['change_password'])) {
            $old = $_POST['old_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($new !== $confirm) $errors[] = "New passwords do not match.";
            else {
                $ok = User::changePassword($userId, $old, $new);
                if ($ok['success']) $success = "Password changed.";
                else $errors[] = $ok['error'] ?? "Unable to change password.";
            }
        }
    }
}

// create csrf token
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf_token'];

$appointments = Appointment::getByUser($userId);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Profile &mdash; Natural Look Aveda</title>
  <link rel="icon" type="image/jpeg" href="assets/logo_favicon.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans+Narrow:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/styles2.css">
  <style>
    body {
            margin: 0;
            background: hsl(129, 14%, 50%);
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 150px;
        }

    .logo {
            height: 275px;
            display: block;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            position: relative;
            margin-left: 20px;
        }

        nav ul li a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 0;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        nav ul li a:hover {
            border-bottom: 3px solid white;
        }
    .wrap{max-width:900px;margin:18px auto;padding:12px}
    .card{background:#fff;padding:12px;border-radius:6px;margin-bottom:12px}
  </style>
</head>
<body>
    <header>
    <img src="assets/logo.png" alt="Natural Look Aveda" class="logo">
    <nav>
        <ul>
            <li><a href="dashboard.php" style="color:white">Back to Dashboard</a></li>
        </ul>
    </nav>
</header>
  <div style="padding:12px;background:hsl(129, 14%, 50%);color:white">
    <h1 style="text-align:center; font-family: Verdana, Geneva, Tahoma, sans-serif;">Profile</h1>
  </div>

  <div class="wrap">
    <?php if ($success) echo "<div style='color:white'>{$success}</div>"; ?>
    <?php foreach ($errors as $e) echo "<div style='color:#c00'>" . htmlspecialchars($e) . "</div>"; ?>

    <div class="card">
      <h3>Account Info</h3>
      <form method="post" action="profile.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="update_profile" value="1">
        <div><label>First name<br><input name="firstname" value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" required></label></div>
        <div><label>Last name<br><input name="lastname" value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" required></label></div>
        <div><label>Email<br><input name="email" type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required></label></div>
        <div><label>Phone<br><input name="phone" type= "tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"></label></div>
        <div style="margin-top:8px"><button type="submit">Save profile</button></div>
      </form>
    </div>

    <div class="card">
      <h3>Change Password</h3>
      <form method="post" action="profile.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="change_password" value="1">
        <div><label>Old password<br><input name="old_password" type="password" required></label></div>
        <div><label>New password<br><input name="new_password" type="password" required></label></div>
        <div><label>Confirm new<br><input name="confirm_password" type="password" required></label></div>
        <div style="margin-top:8px"><button type="submit">Change password</button></div>
      </form>
    </div>

    <div class="card">
      <h3>Your Appointments</h3>
      <?php if (empty($appointments)) { echo "<div>No upcoming appointments.</div>"; } else { ?>
        <table style="width:100%;border-collapse:collapse">
          <thead><tr><th>Date / Time</th><th>Service</th><th>Stylist</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($appointments as $a): ?>
            <tr>
              <td><?php echo htmlspecialchars($a['appt_datetime']); ?></td>
              <td><?php echo htmlspecialchars($a['service_name']); ?></td>
              <td><?php echo htmlspecialchars($a['stylist_firstname'] . ' ' . $a['stylist_lastname']); ?></td>
              <td><?php echo htmlspecialchars($a['status']); ?></td>
              <td>
                <?php if ($a['status'] === 'booked'): ?>
                  <form method="post" action="/appointments.php?action=cancel" style="display:inline">
                    <input type="hidden" name="apptid" value="<?php echo (int)$a['apptid']; ?>">
                    <button type="submit">Cancel</button>
                  </form>
                <?php else: echo '-'; endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php } ?>
    </div>
  </div>
</body>
</html>
