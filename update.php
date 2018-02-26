<?php
	set_time_limit(0);
	$db = new PDO('sqlite:ts3bot.sqlitedb');

	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'ip'");
	while($row = $query->fetch()){
		$countip = $row['count'];
	}
	if($countip == 0){
		echo "Tworzenie tabeli ip...\n";
		$db->query("CREATE TABLE `ip` ( `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `ip` VARCHAR(255) NOT NULL, `proxy` INT(255) NOT NULL, `time` INT(255) NOT NULL )");
		$db->query("INSERT INTO `ip` VALUES (NULL, '127.0.0.1', 0, ".time().")");
		sleep(1);
		echo "Tabela ip została utworzona\n";
	}

	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'users'");
	while($row = $query->fetch()){
		$countusers = $row['count'];
	}
	if($countusers == 0){
		echo "Tworzenie tabeli users...\n";
		$db->query("CREATE TABLE `users` ( `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `cldbid` INT(255) NOT NULL, `client_nickname` VARCHAR(255) NOT NULL, `cui` VARCHAR(255) NOT NULL, `longest_connection` INT(255) NOT NULL, `connections` INT(255) NOT NULL, `time_activity` INT(255) NOT NULL, `last_activity` INT(255) NOT NULL, `regdate` INT(25) NOT NULL, `gid` VARCHAR(255) NOT NULL )");
		sleep(1);
		echo "Tabela users została utworzona\n";
		sleep(1);
		echo "Przenoszenie top connections\n";
		$query = $db->query("SELECT * FROM `top_connections`");
		while($row = $query->fetch()){
			$query2 = $db->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `cldbid` = {$row['cldbid']}");
			while($row2 = $query2->fetch()){
				$count = $row2['count'];
			}
			if($count == 0){
				$db->query("INSERT INTO `users` VALUES (NULL, {$row['cldbid']}, '{$row['client_nickname']}', '{$row['cui']}', 0, {$row['connections']}, 0, 0, '', 0)");
			}else{
				$db->query("UPDATE `users` SET `connections` = {$row['connections']} WHERE `cldbid` = {$row['cldbid']}");
			}
		}
		echo "Przeniesiono top connections\n";
		sleep(1);
		echo "Przenoszenie top connection time\n";
		$query = $db->query("SELECT * FROM `top_connection_time`");
		while($row = $query->fetch()){
			$query2 = $db->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `cldbid` = {$row['cldbid']}");
			while($row2 = $query2->fetch()){
				$count = $row2['count'];
			}
			if($count == 0){
				$db->query("INSERT INTO `users` VALUES (NULL, {$row['cldbid']}, '{$row['client_nickname']}', '{$row['cui']}', {$row['connected_time']}, 0, 0, 0, '', 0)");
			}else{
				$db->query("UPDATE `users` SET `longest_connection` = {$row['connected_time']} WHERE `cldbid` = {$row['cldbid']}");
			}
		}
		echo "Przeniesiono top connection time\n";
		sleep(1);
		echo "Przenoszenie czas przebywania\n";
		$query = $db->query("SELECT * FROM `czas_przebywania`");
		while($row = $query->fetch()){
			$query2 = $db->query("SELECT COUNT(*) AS `count` FROM `users` WHERE `cldbid` = {$row['cldbid']}");
			while($row2 = $query2->fetch()){
				$count = $row2['count'];
			}
			if($count == 0){
				$db->query("INSERT INTO `users` VALUES (NULL, {$row['cldbid']}, '{$row['client_nickname']}', '{$row['cui']}', 0, 0, {$row['time']}, 0, '', 0)");
			}else{
				$db->query("UPDATE `users` SET `time_activity` = {$row['time']} WHERE `cldbid` = {$row['cldbid']}");
			}
		}
		echo "Przeniesiono czas przebywania\n";
	}
	sleep(1);
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'czas_przebywania'");
	while($row = $query->fetch()){
		$count = $row['count'];
	}
	if($count == 1){
		echo "Usuwanie tabeli czas_przebywania\n";
		$db->query("DROP TABLE `czas_przebywania`");
		echo "Tabela czas_przebywania usunięta\n";
	}
	sleep(1);
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'top_connection_time'");
	while($row = $query->fetch()){
		$count = $row['count'];
	}
	if($count == 1){
		echo "Usuwanie tabeli top_connection_time\n";
		$db->query("DROP TABLE `top_connection_time`");
		echo "Tabela top_connection_time usunięta\n";
	}
	sleep(1);
	$query = $db->query("SELECT COUNT(*) as `count` FROM `sqlite_sequence` WHERE `name` = 'top_connections'");
	while($row = $query->fetch()){
		$count = $row['count'];
	}
	if($count == 1){
		echo "Usuwanie tabeli top_connections\n";
		$db->query("DROP TABLE `top_connections`");
		echo "Tabela top_connections usunięta\n";
	}
	sleep(1);
	echo "Sprawdzam czy wszystkie pola istnieją w tabelach\n";
	$regdate = 0;
	$gid = 0;
	$query = $db->query("PRAGMA table_info(users)");
	while($row = $query->fetch()){
		if($row['name'] == 'regdate'){
			$regdate = 1;
		}
		if($row['name'] == 'gid'){
			$gid = 1;
		}
	}
	if($regdate == 0){
		echo "Dodaje brakujące pole regdate\n";
		$db->query("ALTER TABLE `users` ADD `regdate` INT(25) DEFAULT '0'");
	}
	if($gid == 0){
		echo "Dodaje brakujące pole gid\n";
		$db->query("ALTER TABLE `users` ADD `gid` VARCHAR(255) DEFAULT ''");
	}
	echo "Aktualizacja zakończona pomyślnie możesz teraz wpisać ./start start";
?>
