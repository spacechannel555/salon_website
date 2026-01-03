<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
return (object)[
    'db' => [
        'host' => 'localhost',
        'user' => 'adminuser',
        'pass' => 'Monkey2868!!',
        'name' => 'booking_sys'
    ],
    'site' => [
        'from_email' => 'nlasalon.booking@gmail.com',
        'from_name' => 'Natural Look Aveda Bedford Salon Booking'
    ],
    //SMTP settings
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'nlasalon.booking@gmail.com',
        //get past Google's 2FA
        'password' => 'qhaq wvgc kwkw gyuc',
        'secure' => 'tls'
    ]
];
