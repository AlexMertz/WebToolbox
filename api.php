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
  $bdd->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
} catch (Exception $e) {
  myDie("Erreur lors de la connection à la BDD : " . $e->getMessage());
}
if ($_GET["action"] == "propose") {
	$values = extractPostParameters(array("direction", "dayOfTheWeek", "time", "recurrency", "ponctual_date"));
	if (!in_array($values["direction"], getDirections($bdd)))
		myDie("Invalid direction");
	if (!in_array($values["time"], getTimes($bdd)))
		myDie("Invalid time");
	if (!is_numeric($values["dayOfTheWeek"]) || $values["dayOfTheWeek"] < 0 || $values["dayOfTheWeek"] > 7)
		myDie("Invalid day of the week");
	$days = getDaysForReccurency($values["recurrency"], $values["dayOfTheWeek"], $values["ponctual_date"]);
	foreach ($days as $day)
		if (duplicateDetected($bdd, $_SESSION["user"]["login"], $values["time"], $values["direction"], $day))
			myDie("Vous avez déjà enregistré ce trajet !");
	$bdd->beginTransaction();
	// Insert the Proposal
	try  {
		insertProposal($bdd, $values["direction"], $values["time"], $values["dayOfTheWeek"], $_SESSION["user"]["login"]);
		$lastId = $bdd->lastInsertId();
		// And the rides
		foreach ($days as $day)
			insertRide($bdd, $lastId, $day);
		$bdd->commit();
		echo "OK";
	} catch (Exception $e) {
		$bdd->rollBack();
	}
} else if ($_GET["action"] == "search") {
	$values = extractPostParameters(array("direction", "dayOfTheWeek", "time", "recurrency", "ponctual_date"));
	$days = getDaysForReccurency($values["recurrency"], $values["dayOfTheWeek"], $values["ponctual_date"]);
	$totalDays = count($days);
	$results = array();
	$ids = array();
	foreach ($days as $day) {
		$r = getRide($bdd, $values["direction"], $values["time"], $day);
		if (!empty($r)) {
			foreach ($r as $row) {
				$ids[$row["id"]] += 1;
			}
			$results[$day] = $r;
		}
	}
	if (empty($results)) {
		echo json_encode(array("match" => "0%"));
		exit();
	}
	$maxMatchs = max(array_values($ids));
	if ($maxMatchs == $totalDays) {
		$goodProposalsIds = array();
		foreach ($ids as $key => $value)
			if ($value == $maxMatchs)
				array_push($goodProposalsIds, $key);
		$proposals = array();
		foreach ($goodProposalsIds as $id)
			array_push($proposals, getProposal($bdd, $id));
		echo json_encode(array("match" => "100%", "proposals" => $proposals));
	}



}