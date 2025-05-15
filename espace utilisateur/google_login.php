<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('273604060688-eqm5ck65mdjtcns10qn35mqm2ua9gk7a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/web/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
