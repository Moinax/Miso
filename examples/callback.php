<?php
include __DIR__ . '/settings.php';
include __DIR__ . '/../src/Moinax/Miso/Client.php';

use Moinax\Miso\Client;
session_start();

$miso = new Client(MISO_OAUTH_TOKEN, MISO_OAUTH_TOKEN_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);

if (($oauthToken = $_GET['oauth_token'])) {
    $miso->setToken($oauthToken, $_SESSION['miso']['token_secrect']);
    $access_token_info = $miso->getAccessToken(Client::MISO_ACCESS_TOKEN_URL);
    $_SESSION['miso']['token'] = $access_token_info['oauth_token'];
    $_SESSION['miso']['token_secret'] = $access_token_info['oauth_token_secret'];

    header('Location: http://localhost/Miso/examples/index.php');
    exit(0);
}

exit(1);
