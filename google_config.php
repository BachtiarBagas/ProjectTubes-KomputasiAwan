<?php
require_once 'vendor/autoload.php';

$clientID = '610900154866-i2u0cmiclf1d1132a3ag2kjd5ptm30d2.apps.googleusercontent.com';
$clientSecret = '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';
$redirectURI = 'https://foodsite.azurewebsites.net/google_callback.php';

// CREATE CLIENT REQUEST TO GOOGLE
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURI);
$client->addScope('profile');
$client->addScope('email');