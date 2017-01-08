<?php 

session_start();
session_destroy();

$config = parse_ini_file("config.ini", true);

header("Location: " . $config["this"]["main_url"]);