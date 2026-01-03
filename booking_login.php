<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/crud_functions.php';
require_once __DIR__ . '/auth.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
Auth::start();

if (!empty($_SESSION['userid'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $attempt = Auth::login($email, $password);
        if ($attempt['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = $attempt['error'] ?? 'Login failed';
        }
    } elseif ($action === 'guest') {
        $name = trim($_POST['guest_name'] ?? '');
        $email = trim($_POST['guest_email'] ?? '');
        if (!$name || !$email) {
            $errors[] = "Please provide name and email for guest booking.";
        } else {
            $parts = explode(' ', $name, 2);
            $firstname = $parts[0] ?: 'Guest';
            $lastname = $parts[1] ?? '';

            $new = User::createGuest($firstname, $email);
            if ($new && isset($new['userid'])) {
                $_SESSION['userid'] = $new['userid'];
                $_SESSION['is_admin'] = 0;
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "Unable to create guest session. Try again.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Booking Login &mdash; Natural Look Aveda</title>
  <link rel="icon" type="image/jpeg" href="assets/logo_favicon.jpg">
  <link rel="stylesheet" href="assets/styles2.css">
  <style>
    body {
            margin: 0;
            background: hsl(129, 14%, 50%);
            font-family: "Raleway", sans-serif;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 150px;
        }

        footer {
            text-align: center;
        }

        footer p a:link {
            text-decoration: none;
            color:black;
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
        }

        nav ul li a:hover {
            border-bottom: 3px solid white;
        }

        /* Dropdown menu under SERVICES */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: hsl(124, 20%, 50%);
            min-width: 220px;
            padding: 8px 0;
            border-radius: 4px;
        }

        .dropdown-menu li {
            margin: 0;
        }

        .dropdown-menu li a {
            padding: 8px 15px;
            border-bottom: none;
            white-space: nowrap;
        }

        .dropdown-menu li a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-bottom: none;
        }

        /* Show menu on hover */
        li.dropdown:hover .dropdown-menu {
            display: block;
        }
    .center-wrap{max-width:960px;margin:40px auto;display:flex;gap:24px}
    .card{background:#fff;padding:20px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.08);flex:1}
    .card h2{margin-top:0}
    .error{color:#c00;margin-bottom:8px}
    .guest-note{font-size:0.9rem;color:#555}
  </style>
</head>
<body>
  <header>
        <a href="index.html">
            <img src="assets/logo.png" alt="Natural Look Aveda" class="logo">
        </a>

        <nav>
            <ul>
                <li><a href="about.html">ABOUT</a></li>
                <li><a href="our_work.html">OUR WORK</a></li>
                <li class="dropdown">
                    <a href="services.html">SERVICES</a>
                    <ul class="dropdown-menu">
                        <li><a href="services.html#haircut-style">Haircut/Style</a></li>
                        <li><a href="services.html#color">Color</a></li>
                        <li><a href="services.html#magic-sleek">Magic Sleek Smoothing Treatment</a></li>
                        <li><a href="services.html#hair-treatment">Treatments</a></li>
                    </ul>
                </li>
                <li><a href="contact.html">CONTACT</a></li>
                <li><a href="booking_login.php">BOOK NOW</a></li>
            </ul>
        </nav>
    </header>

  <div class="center-wrap">
    <div class="card">
      <h2>Login</h2>
      <?php foreach ($errors as $e) echo "<div class='error'>" . htmlspecialchars($e) . "</div>"; ?>
      <form method="post" action="booking_login.php">
        <input type="hidden" name="action" value="login">
        <div><label>Email<br><input name="email" type="email" required></label></div>
        <div><label>Password<br><input name="password" type="password" required></label></div>
        <div style="margin-top:12px"><button type="submit">Login</button></div>
      </form>
      <p style="margin-top:12px;font-size:.9rem;">
        Don't have an account? <a href="booking_register.php">Create one</a>
      </p>
    </div>

    <div class="card">
      <h2>Guest Booking</h2>
      <p class="guest-note">Book without creating an account. A temporary guest session will be created.</p>

      <form method="post" action="booking_login.php">
        <input type="hidden" name="action" value="guest">
        <div><label>Full name<br><input name="guest_name" type="text" required></label></div>
        <div><label>Email<br><input name="guest_email" type="email" required></label></div>
        <div style="margin-top:12px"><button type="submit">Continue as Guest</button></div>
      </form>
    </div>
  </div>

  <footer>
        <address>320 Bedford Avenue Brooklyn, NY 11249</address>
        <p><a href = "tel:347-384-2116">347-384-2116</a></p>
        <p>&copy; Elise Poonai 2025</p>
    </footer>
</body>
</html>
