<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
return (object)[
    'db' => [
        'host' => 'localhost',
        'user' => '[insert db user here]',
        'pass' => '[insert db password here]',
        'name' => 'booking_sys'
    ],
    'site' => [
        'from_email' => '[insert email used for email confirmations here]',
        'from_name' => '[insert email subject here]'
    ],
    //SMTP settings using Gmail
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '[insert email used for email confirmations here]',
        //get past Google's 2FA
        'password' => '[insert generated app password here]',
        'secure' => 'tls'
    ]
];

