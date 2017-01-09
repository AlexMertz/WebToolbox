<?php

session_start();

$config = parse_ini_file("config.ini", true);

if (empty($_SESSION["user"])) {
	header("Location: " . $config["this"]["main_url"]); 
	exit();
}

try
  $bdd = new PDO("mysql:host=" . $config["bdd"]["server"] .";dbname=" . $config["bdd"]["name"] .";charset=utf8", $config["bdd"]["login"], $config["bdd"]["password"]);
catch (Exception $e)
   die('Erreur lors de la connection à la BDD : ' . $e->getMessage());

if ($_GET["action"] == "propose") {
	$toGet = [ "direction", "dayOfTheWeek", "time", "recurrency"];
}