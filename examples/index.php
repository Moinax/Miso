<?php
include __DIR__ . '/settings.php';
include __DIR__.'/../src/Miso/Client.php';

use Miso\Client;
session_start();

$miso = new Client(MISO_OAUTH_TOKEN, MISO_OAUTH_TOKEN_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);

if (!isset($_SESSION['miso']['token'])) {
    $request_token_info = $miso->getRequestToken(Client::MISO_REQUEST_TOKEN_URL, MISO_OAUTH_CALLBACK);

    $_SESSION['miso']['token_secret'] = $request_token_info['oauth_token_secret'];

    header('Location: ' . Client::MISO_AUTHORIZE_URL . '?oauth_token=' . $request_token_info['oauth_token']);
    exit(0);
}

$miso->setToken($_SESSION['miso']['token'], $_SESSION['miso']['token_secret']);

$user = $miso->getUser();

$favorites = $miso->getFavorites();

$alcatraz = $miso->searchMedia('Alcatraz');