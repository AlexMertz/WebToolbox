<?php

session_start();

$config = parse_ini_file("config.ini", true);

if (empty($_SESSION["user"])) {
  header($config["this"]["login_url"]);
  exit(0);
}

// Require Pug
require __DIR__ . '/vendor/autoload.php';
use Pug\Pug;
http://assos.utc.fr/onveutdurable/webtoolbox2/login.php

// Start Pug
$pug = new Pug(array(
  'cache' => $config["this"]["cache_folder"]
));

// Compile the Pug template & diplay it !
echo $pug->render('views/index.pug', array(
  'title' => $config["this"]["title"],
  "user" => $_SESSION["user"]["login"]
));