<?php
// public/booking_register.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crud_functions.php';
require_once __DIR__ . '/auth.php';

Auth::start();

// If already logged in, send to dashboard
if (!empty($_SESSION['userid'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic CSRF
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $csrf) {
        $errors[] = 'Invalid request.';
    } else {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname  = trim($_POST['lastname'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        // Validation
        if (!$firstname || !$lastname) $errors[] = 'Please provide your first and last name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        // Check email exists
        if (empty($errors)) {
            $existing = User::getByEmail($email);
            if ($existing) {
                $errors[] = 'An account with that email already exists. You may login instead.';
            }
        }

        // Create account
        if (empty($errors)) {
            $ok = User::create($firstname, $lastname, $email, $password);
            if ($ok) {
                // Fetch user to get userid
                $user = User::getByEmail($email);
                if ($user && !empty($user['userid'])) {
                    // Log user in
                    $_SESSION['userid'] = $user['userid'];
                    $_SESSION['is_admin'] = !empty($user['is_admin']) ? 1 : 0;
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $errors[] = 'Account created but login failed. Please try to login.';
                }
            } else {
                $errors[] = 'Unable to create account. Please try again later.';
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create account — NLA Salon</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body{background:#f5f7fb;font-family:Arial,Helvetica,sans-serif}
    .wrap{max-width:700px;margin:36px auto}
    .card{background:#fff;padding:18px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.06)}
    input[type="text"],input[type="email"],input[type="password"]{width:100%;padding:8px;margin-top:6px;border:1px solid #ddd;border-radius:4px}
    button{padding:10px 14px;border-radius:6px;border:none;background:#2e7db8;color:#fff;cursor:pointer}
    .error{color:#b00020;margin-bottom:8px}
    .note{font-size:.9rem;color:#555}
    a.link{color:#2e7db8;text-decoration:none}
  </style>
</head>
<body>
  <header style="background:#2e7db8;color:#fff;padding:14px">
    <div style="max-width:980px;margin:0 auto">NLA Salon — Create account</div>
  </header>

  <div class="wrap">
    <div class="card">
      <h2>Create an account</h2>

      <?php if ($success) echo "<div style='color:green;margin-bottom:8px'>" . htmlspecialchars($success) . "</div>"; ?>
      <?php foreach ($errors as $e) echo "<div class='error'>" . htmlspecialchars($e) . "</div>"; ?>

      <form method="post" action="booking_register.php" novalidate>
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">

        <label>First name<br>
          <input name="firstname" type="text" required value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>">
        </label><br><br>

        <label>Last name<br>
          <input name="lastname" type="text" required value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>">
        </label><br><br>

        <label>Email<br>
          <input name="email" type="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </label><br><br>

        <label>Password (min 8 chars)<br>
          <input name="password" type="password" required>
        </label><br><br>

        <label>Confirm password<br>
          <input name="confirm_password" type="password" required>
        </label><br><br>

        <div style="display:flex;gap:8px;align-items:center">
          <button type="submit">Create account</button>
          <div class="note">Or <a class="link" href="booking_login.php">return to login</a></div>
        </div>
      </form>

      <hr style="margin:16px 0">

      <p class="note">Prefer not to create an account? Use <a class="link" href="booking_login.php">guest booking</a> instead — you'll still receive confirmation emails.</p>
    </div>
  </div>
</body>
</html>
