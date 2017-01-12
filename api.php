<?php

session_start();

require "functions.php";

$config = parse_ini_file("config.ini", true);

if (empty($_SESSION["user"])) {
	header("Location: " . $config["this"]["main_url"]); 
	exit();
}

function myDie ($msg) {
	header('HTTP/1.0 403 Forbidden');
	echo $msg;
	exit(1);
}

try {
  $bdd = new PDO("mysql:host=" . $config["bdd"]["server"] .";dbname=" . $config["bdd"]["name"] .";charset=utf8", $config["bdd"]["login"], $config["bdd"]["password"]);
} catch (Exception $e) {
  myDie("Erreur lors de la connection à la BDD : " . $e->getMessage());
}
if ($_GET["action"] == "propose") {
	$toGet = array( "direction", "dayOfTheWeek", "time", "recurrency", "ponctual_date" );
	$values = array();
	foreach ($toGet as $key) {
		$values[$key] = $_POST[$key];
	}
	if (!in_array($values["direction"], getDirections($bdd)))
		myDie("Invalid direction");
	if (!in_array($values["time"], getTimes($bdd)))
		myDie("Invalid time");
	if (!is_numeric($values["dayOfTheWeek"]) || $values["dayOfTheWeek"] < 0 || $values["dayOfTheWeek"] > 7)
		myDie("Invalid day of the week");
	$days = array();
	if ($values["recurrency"] == "all")
		$days = getDateForSpecificDayBetweenDates(time(), getDateEndCurrentSemester(), $values["dayOfTheWeek"]);
	else if ($values["recurrency"] == "ponctual") {
		$days[0] = strtotime($values["ponctual_date"]);
	}
	else
		myDie("No valid recurrency");
	foreach ($days as $day)
		if (duplicateDetected($bdd, $_SESSION["user"]["login"], $values["time"], $values["direction"], $day))
			myDie("Vous avez déjà enregistré ce trajet !");
	$bdd->beginTransaction();
	// Insert the Proposal
	insertProposal($bdd, $values["direction"], $values["time"], $_SESSION["user"]["login"]);
	$lastId = $bdd->lastInsertId();
	// And the rides
	foreach ($days as $day)
		insertRide($bdd, $lastId, $day);
	$bdd->commit();
	echo "OK";
}