<?php 
require_once 'env.php';
require_once 'db.php';
require_once 'functions.php';
require  __DIR__ .'/twilio-php-master/src/Twilio/autoload.php';
use Twilio\Rest\Client;

$client = new Client($account_sid, $auth_token);
?>