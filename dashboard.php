<?php
// /dashboard.php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crud_functions.php';

if (!isset($_SESSION['userid'])) {
    header('Location: booking_login.php');
    exit;
}

$userid = $_SESSION['userid'];
$user   = User::getById($userid);
$isAdmin = $userid === 1;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard &mdash; Natural Look Aveda</title>
<link rel="icon" type="image/jpeg" href="assets/logo_favicon.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=PT+Sans+Narrow:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/styles2.css">

<style>
  body { margin: 0;
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
  .header {color:#fff; padding:15px; text-align:center; font-family: Verdana, Geneva, Tahoma, sans-serif;}
  a { color:#fff; text-decoration:none; }

  .content-wrap { max-width:1200px; margin:20px auto; padding:0 15px; display:flex; gap:20px; }

  .calendar-area { flex:3; }
  .side-panel { flex:1; }

  .card {
    background:#fff; padding:15px; border-radius:8px;
    box-shadow:0 3px 8px rgba(0,0,0,.1); margin-bottom:20px;
  }

  .grid-header {
    display: grid;
    grid-auto-columns: 1fr;
    grid-auto-flow: column;
    align-items: center;
    text-align: center;
    font-weight: bold;
}
.grid-header > .time-column {
    width: 120px !important;
    grid-column: 1 / 2;
}

  .grid-body{display:flex;}
  .time-column{width:120px;}
  .time-slot{border-bottom:1px solid #f0f0f0;box-sizing:border-box;padding:2px 8px;height:20px;font-size:12px;color:#666;}
  .stylist-column{flex:1;border-left:1px solid #eee;position:relative;}
  .slot-tile{width:100%;height:20px;cursor:pointer;}
  .appt-block{
    background:linear-gradient(180deg,#ffd7f0,#bb3e9a);
    color:#fff;padding:4px;border-radius:5px;
    font-size:12px;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,.2);
  }

  .modal-wrap{position:fixed;left:0;top:0;right:0;bottom:0;z-index:9999;}
  .modal-backdrop{position:absolute;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.45);}
  .modal {
    position:relative;
    margin:80px auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
    max-width:450px;
    max-height:80vh;
    overflow-y:auto;
    box-shadow:0 10px 20px rgba(0,0,0,.25);
    z-index:10000;
}

  .modal-close{position:absolute;right:10px;top:10px;background:none;border:none;font-size:22px;cursor:pointer;}

  .services-list{display:flex;flex-direction:column;gap:8px;margin-top:15px;}
  .svc-btn{
    padding:10px;border:1px solid #ddd;background:#f8f8f8;border-radius:5px;
    cursor:pointer;text-align:left;font-size:14px;
  }
  .svc-btn:hover{background:#fff;}
</style>

</head>
<body>
<header>
    <img src="assets/logo.png" alt="Natural Look Aveda" class="logo">
    <nav>
        <ul>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>
<div class="header">
    <h2>Welcome, <?php echo htmlspecialchars($user['firstname']); ?></h2>
</div>

<div class="content-wrap">

  <div class="calendar-area">
    <div class="card">
      <h2>Book an Appointment</h2>
      <label>Date: </label>
      <input type="date" id="datePicker" value="<?php echo date('Y-m-d'); ?>">
    </div>

    <div class="card">
      <div id="calendarGrid"></div>
    </div>
  </div>

  <div class="side-panel">
    <div class="card">
      <h3>My Appointments</h3>
      <div id="myAppointments">Loading…</div>
    </div>

    <!--<//?php if ($isAdmin): ?>
    <div class="card">
            <h3>Admin — Search Users</h3>
            <input type="text" id="adminSearchTerm" placeholder="Name / Email / Phone" style="width:100%;padding:8px;">
            <button onclick="adminSearchUsers()" style="margin-top:10px;">Search</button>
        <div id="adminSearchResults" style="margin-top:10px;"></div>
    </div>
    <//?php endif; ?>-->

  </div>

</div>

<!-- Make PHP user ID available to JS -->
<script>
  const CURRENT_USER_ID = <?php echo json_encode($userid); ?>;
</script>

<!--<script>
function adminSearchUsers() {
    const term = document.getElementById('adminSearchTerm').value.trim();
    if (!term) {
        document.getElementById('adminSearchResults').innerHTML = 'Enter a name/email/phone.';
        return;
    }

    fetch(`/appointments.php?action=admin_search&term=${encodeURIComponent(term)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('adminSearchResults').innerHTML = "Error searching.";
                return;
            }
            if (data.results.length === 0) {
                document.getElementById('adminSearchResults').innerHTML = "No users found.";
                return;
            }

            let html = '<ul>';
            data.results.forEach(u => {
                html += `<li>${u.firstname} ${u.lastname} — ${u.email}</li>`;
            });
            html += '</ul>';
            document.getElementById('adminSearchResults').innerHTML = html;
        });
}
</script>-->

<!-- Load your modal timeslot system -->
<script src="/assets/calendar.js"></script>

</body>
</html>

