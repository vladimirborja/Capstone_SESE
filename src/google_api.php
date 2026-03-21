<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Detect if running on localhost
$isLocal = ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1');

$google_config = [
    'client_id'     => '',
    'client_secret' => '',
    'redirect_uri'  => $isLocal 
        ? 'http://localhost/Capstone_SESE/src/google_callback.php' 
        : 'https://sese.works/google_callback.php'
];

$client = new Google\Client();
$client->setClientId($google_config['client_id']);
$client->setClientSecret($google_config['client_secret']);
$client->setRedirectUri($google_config['redirect_uri']);
$client->addScope("email");
$client->addScope("profile");