<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$flapp = new Knister\FLApp(array(
		"useFrontController" => true,
		"autoAddPagesAsRoute" => true
	));
	$flapp->init();
	$path = $flapp->getPath();
	$url = $flapp->getURL();

	var_dump($flapp, $path, $url);