<?php

session_start();

if (empty($_SESSION["user"])) {
  header("Location: http://assos.utc.fr/onveutdurable/webtoolbox2/login.php");
  exit(0);
}

// Require Pug
require __DIR__ . '/vendor/autoload.php';
use Pug\Pug;
http://assos.utc.fr/onveutdurable/webtoolbox2/login.php

// Start Pug
$pug = new Pug(array(
  'cache' => '/sites/onveutdurable/webtoolbox2/cache'
));

// Compile the Pug template & diplay it !
echo $pug->render('views/index.pug', array(
  'title' => 'Web-Toolbox Durable',
  "user" => $_SESSION["user"]["login"]
));