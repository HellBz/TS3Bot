<?php
	$db = new PDO('sqlite:ts3bot.sqlitedb');
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'ip'");
	while($row = $query->fetch()){
		$count = $row['count'];
	}
	if($count == 0){
		$db->query("CREATE TABLE `ip` ( `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `ip` VARCHAR(255) NOT NULL, `proxy` INT(255) NOT NULL, `time` INT(255) NOT NULL )");
	}
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'users'");
	while($row = $query->fetch()){
		$count = $row['count'];
	}
	if($count == 0){
		$db->query("CREATE TABLE `users` ( `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `cldbid` INT(255) NOT NULL, `client_nickname` VARCHAR(255) NOT NULL, `cui` VARCHAR(255) NOT NULL, `longest_connection` INT(255) NOT NULL, `connections` INT(255) NOT NULL, `time_activity` INT(255) NOT NULL, `last_activity` INT(255) NOT NULL )");
		$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'top_connections'");
		while($row = $query->fetch()){
			$count = $row['count'];
		}
		if($count == 1){
			$query = $db->query("SELECT * FROM `top_connections`");
			while($row = $query->fetch()){
				$query2 = $db->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `cldbid` = {$row['cldbid']}");
				while($row2 = $query2->fetch()){
					$count = $row2['count'];
				}
				if($count == 0){
					$db->query("INSERT INTO `users` VALUES (NULL, {$row['cldbid']}, '{$row['client_nickname']}', '{$row['cui']}', 0, {$row['connections']}, 0, 0)");
				}else{
					$db->query("UPDATE `users` SET `connections` = {$row['connections']} WHERE `cldbid` = {$row['cldbid']}");
				}
			}
		}
		$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'top_connection_time'");
		while($row = $query->fetch()){
			$count = $row['count'];
		}
		if($count == 1){
		$query = $db->query("SELECT * FROM `top_connection_time`");
			while($row = $query->fetch()){
				$query2 = $db->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `cldbid` = {$row['cldbid']}");
				while($row2 = $query2->fetch()){
					$count = $row2['count'];
				}
				if($count == 0){
					$db->query("INSERT INTO `users` VALUES (NULL, {$row['cldbid']}, '{$row['client_nickname']}', '{$row['cui']}', {$row['connected_time']}, 0, 0, 0)");
				}else{
					$db->query("UPDATE `users` SET `longest_connection` = {$row['connected_time']} WHERE `cldbid` = {$row['cldbid']}");
				}
			}
		}
		$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'czas_przebywania'");
		while($row = $query->fetch()){
			$count = $row['count'];
		}
		if($count == 1){
			$query = $db->query("SELECT * FROM `czas_przebywania`");
			while($row = $query->fetch()){
				$query2 = $db->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `cldbid` = {$row['cldbid']}");
				while($row2 = $query2->fetch()){
					$count = $row2['count'];
				}
				if($count == 0){
					$db->query("INSERT INTO `users` VALUES (NULL, {$row['cldbid']}, '{$row['client_nickname']}', '{$row['cui']}', 0, 0, {$row['time']}, 0)");
				}else{
					$db->query("UPDATE `users` SET `time_activity` = {$row['time']} WHERE `cldbid` = {$row['cldbid']}");
				}
			}
		}
	}
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'czas_przebywania'");
	while($row = $query->fetch()){
		if($row['count'] == 1){
			$db->query("DROP TABLE `czas_przebywania`");
		}
	}
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'top_connection_time'");
	while($row = $query->fetch()){
		if($row['count'] == 1){
			$db->query("DROP TABLE `top_connection_time`");
		}
	}
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'top_connections'");
	while($row = $query->fetch()){
		if($row['count'] == 1){
			$db->query("DROP TABLE `top_connections`");
		}
	}
?>
