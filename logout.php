<?php
require_once __DIR__ . '/auth.php';

Auth::logout();

header('Location: booking_login.php');
exit;
