<?php

require __DIR__ . '/vendor/autoload.php';

use Ekapusta\OAuth2Esia\Provider\EsiaProvider;
use Ekapusta\OAuth2Esia\Security\Signer\OpensslPkcs7;

$config = require __DIR__ . '/config.php';

$provider = new EsiaProvider([
  'clientId'      => $config['clientId'],
  'redirectUri'   => $config['redirectUri'],
  'defaultScopes' => ['openid', 'fullname'],
  // For work with test portal version
  'remoteUrl' => 'https://esia-portal1.test.gosuslugi.ru',
  'remoteCertificatePath' => EsiaProvider::RESOURCES . 'esia.test.cer',
], [
  'signer' => new OpensslPkcs7($config['sertificatePath'], $config['privateKeyPath'])
]);

// https://your-system.domain/auth/start/
$authUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth2.esia.state'] = $provider->getState();
header('Location: '.$authUrl);
exit;

// https://your-system.domain/auth/finish/?state=...&code=...
if ($_SESSION['oauth2.esia.state'] !== $_GET['state']) {
    exit('The guard unravels the crossword.');
}

$accessToken = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
$esiaPersonData = $provider->getResourceOwner($accessToken);
var_export($esiaPersonData->toArray());
?>