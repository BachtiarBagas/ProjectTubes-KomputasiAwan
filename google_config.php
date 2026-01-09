<?php
require_once 'vendor/autoload.php';

$clientID = '889173134800-v3a3gvg12u85oops6gbkvjqf5kihpb93.apps.googleusercontent.com';
$clientSecret = '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';
$redirectURI = 'http://localhost/Project%20Komwan%20+%20midtrans/ProjectTubes-KomputasiAwan/google_callback.php';

// CREATE CLIENT REQUEST TO GOOGLE
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURI);
$client->addScope('profile');
$client->addScope('email');