<?php
	$db = new PDO('sqlite:ts3bot.sqlitedb');
	$db->query("CREATE TABLE `top_connections` ( `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `cldbid` INT(255) NOT NULL, `client_nickname` VARCHAR(255) NOT NULL, `cui` VARCHAR(255) NOT NULL, `connections` INT(255) NOT NULL )");
	$db->query("CREATE TABLE `top_connection_time` ( `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `cldbid` INT(255) NOT NULL, `client_nickname` VARCHAR(255) NOT NULL, `cui` VARCHAR(255) NOT NULL, `connected_time` INT(255) NOT NULL )");
	echo ":)";
?>