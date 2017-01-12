<?php 

session_start();

$config = parse_ini_file("config.ini", true);

$toGet = array(
 "/cas:serviceResponse/cas:authenticationSuccess/cas:user" => "login",
 "/cas:serviceResponse/cas:authenticationSuccess/cas:attributes/cas:mail" => "email",
 "/cas:serviceResponse/cas:authenticationSuccess/cas:attributes/cas:givenName" => "firstName",
 "/cas:serviceResponse/cas:authenticationSuccess/cas:attributes/cas:sn" => "lastName"
);

$ticket = $_GET['ticket'] ?: null;

if(empty($ticket)) { 
	header("Location: " . $config["cas"]["login_url"] . "?service=" . $config["this"]["login_url"]); 
	exit();
} 
try {
	$rawResponse = file_get_contents($config["cas"]["validate_url"] . "?service=" . $config["this"]["login_url"] . "&ticket=". $ticket);
	$response = new SimpleXMLElement($rawResponse);
	$user = array();
	foreach ($toGet as $path => $attribute) {
		$field = $response->xpath($path);
		$field = (string) $field[0];
		if(empty($field)) 
			throw new Exception('No valid response');
		$user[$attribute] = $field; 
	}
	$bdd = new PDO("mysql:host=" . $config["bdd"]["server"] .";dbname=" . $config["bdd"]["name"] .";charset=utf8", $config["bdd"]["login"], $config["bdd"]["password"]);
	$req = $bdd->prepare("INSERT INTO Usr (login, firstName, lastName, email) VALUES (:login, :firstName, :lastName, :email) ON DUPLICATE KEY UPDATE login=:login") or die("Erreur lors de la requette à la BDD : " . print_r($bdd->errorInfo()));
	$req->execute(array(
		"login" => $user["login"],
		"firstName" => $user["firstName"],
		"lastName" => $user["lastName"],
		"email" => $user["email"]
	));
	$_SESSION["user"] = $user;
	header("Location: " . $config["this"]["main_url"]); 

} catch (Exception $e) {
  	echo $e->getMessage();
}
