<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;

$serviceAccountPath = __DIR__ . '/firebase-key.json'; // your json file name

$factory = (new Factory)
    ->withServiceAccount($serviceAccountPath);

$messaging = $factory->createMessaging();

return $messaging;