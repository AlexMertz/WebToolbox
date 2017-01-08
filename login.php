<?php 

session_start();

$urlCasLogin = "https://cas.utc.fr/cas/login?service=";
$urlCasValidate = "https://cas.utc.fr/cas/serviceValidate?service=";
$loginUrl = "http://assos.utc.fr/onveutdurable/webtoolbox2/login.php";

$ticket = $_GET['ticket'] ?: null;

$toGet = array(
 "/cas:serviceResponse/cas:authenticationSuccess/cas:user" => "login",
 "/cas:serviceResponse/cas:authenticationSuccess/cas:attributes/cas:mail" => "mail",
 "/cas:serviceResponse/cas:authenticationSuccess/cas:attributes/cas:givenName" => "firstName",
 "/cas:serviceResponse/cas:authenticationSuccess/cas:attributes/cas:sn" => "lastName"
);

if(empty($ticket)) { 
	header("Location: " . $urlCasLogin . $loginUrl); 
} else {
	try {
		$url = $urlCasValidate . $loginUrl . "&ticket=". $ticket;
		$rawResponse = file_get_contents($url); 
		$response = new SimpleXMLElement($rawResponse);
		$fields = array();
		foreach ($toGet as $path => $attribute) {
			$field = $response->xpath($path);
			$field = (string) $field[0] ;
			if(!$field) 
				throw new Exception('No valid response');
			$fields[$attribute] = $field; 
		}
		$_SESSION["user"] = $fields;
		header("Location: http://assos.utc.fr/onveutdurable/webtoolbox2/");
	} catch (Exception $e) {
    	echo $e->getMessage();
    }
}
