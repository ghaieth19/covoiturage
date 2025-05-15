<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$fb = new \League\OAuth2\Client\Provider\Facebook([
    'clientId'          => '2127706074339171',
    'clientSecret'      => 'f595a7b667466859490bf04f033718f6',
    'redirectUri'       => 'http://localhost/web/espace%20utilisateur/facebook_callback.php',
    'graphApiVersion'   => 'v17.0',
]);

$authUrl = $fb->getAuthorizationUrl([
    'scope' => ['email'],
]);

$_SESSION['oauth2state'] = $fb->getState();
header('Location: ' . $authUrl);
exit;
