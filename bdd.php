<?php

// You must have config declared before requiring this file

$bdd = new PDO("mysql:host=" . $config["bdd"]["server"] .";dbname=" . $config["bdd"]["name"] .";charset=utf8", $config["bdd"]["login"], $config["bdd"]["password"]);