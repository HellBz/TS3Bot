<?php
	declare(strict_types=1);
	set_time_limit(0);
	ini_set('error_log', 'log/php_error.log');
	/**
	 * @author		Majcon
	 * @email		Majcon94@gmail.com
	 * @copyright	© 2016-2017 Majcon
	 * @version		2.0
	 **/
	if(!file_exists("includes/config.php") == true) {
		die("Plik config.php nie istnieje!");
	}else{
		$config = require 'includes/config.php';
	}
	if(!file_exists("class/functions_lang.php") == true) {
		die("Plik functions_lang.php nie istnieje!");
	}else{
		require_once 'class/functions_lang.php';
		$l = new Language();
	}
	if(!file_exists("class/functions.class.php") == true) {
		die("Plik functions.class.php nie istnieje!");
	}else{
		require_once 'class/functions.class.php';;
	}
		require_once 'class/commands.class.php';
		$command = new Commands();

	if(!file_exists("class/ts3admin.class.php") == true) {
		die("Plik ts3admin.class.php nie istnieje!");
	}else{
		require_once 'class/ts3admin.class.php';
	}
	if($config['bot']['ver'] < file_get_contents('http://51.254.119.80/ts3bot/ver.php')){
		echo 'Korzystasz ze starej wersji bota zaktualizuj ją. ;)';
	}
	$db = new PDO('sqlite:ts3bot.sqlitedb');
	$tsAdmin = new ts3admin($config['server']['ip'], $config['server']['queryport']);
	if($tsAdmin->getElement('success', $tsAdmin->connect())) {
		$tsAdmin->login($config['server']['login'], $config['server']['password']);
		$tsAdmin->selectServer($config['server']['port']);
		$tsAdmin->setName($config['server']['nick']);
		$command->setConfig($config);
		$command->setDb($db);
		$command->setLang($l);
		$command->setTs3admin($tsAdmin);
		do{
			$whoami = $tsAdmin->getElement('data', $tsAdmin->whoAmI());
			$command->setClientlist($tsAdmin->getElement('data', $tsAdmin->clientList("-groups -uid -times -ip -voice -away")));
			$command->setServerinfo($tsAdmin->getElement('data', $tsAdmin->serverInfo()));
			$command->update_activity();

			if($config['functions_addRank']['on'] == true) {
				$command->addRank();
			}

			if($config['functions_aktualna_data']['on'] == true) {
				$command->aktualna_data();
			}

			if($config['functions_aktualnie_online']['on'] == true) {
				$command->aktualnie_online();
			}

			if($config['functions_anty_vpn']['on'] == true) {
				$command->anty_vpn();
			}

			if($config['functions_cleanChannel']['on'] == true) {
				$command->cleanChannel();
			}

			if($config['functions_channelCreate']['on'] == true) {
				$command->channelCreate();
			}
			if($config['functions_channelNumber']['on'] == true) {
				$command->channelNumber();
			}

			if($config['functions_delInfoChannel']['on'] == true) {
				$command->delInfoChannel();
			}

			if($config['functions_delPermissions']['on'] == true) {
				$command->delPermissions();
			}

			if($config['functions_delRank']['on'] == true) {
				$command->delRank();
			}

			if($config['functions_groupOnline']['on'] == true) {
				$command->groupOnline();
			}

			if($config['functions_moveAfk']['on'] == true) {
				$command->moveAfk();
			}

			if($config['functions_newUser']['on'] == true) {
				$command->newUser();
			}

			if($config['functions_register']['on'] == true) {
				$command->register();
			}

			if($config['functions_rekord_online']['on'] == true) {
				$command->rekord_online();
			}

			if($config['functions_sendAd']['on'] == true) {
				$command->sendAd();
			}

			if($config['functions_servername']['on'] == true) {
				$command->servername();
			}

			if($config['functions_sprchannel']['on'] == true) {
				$command->sprchannel();
			}

			if($config['functions_sprnick']['on'] == true) {
				$command->sprnick();
			}

			if($config['functions_statusTwitch']['on'] == true) {
				$command->statusTwitch();
			}
			
			if($config['functions_statusYt']['on'] == true) {
				$command->statusYt();
			}

			if($config['functions_poke']['on'] == true) {
				$command->poke();
			}

			if($config['functions_top_activity_time']['on'] == true) {
				$command->top_activity_time();
			}

			if($config['functions_top_connections']['on'] == true) {
				$command->top_connections();
			}

			if($config['functions_top_longest_connection']['on'] == true) {
				$command->top_longest_connection();
			}

			if($config['functions_welcome_messege']['on'] == true) {
				$command->welcome_messege();
			}
			sleep(1);
		}while($whoami['virtualserver_status'] == 'online');
	}else{
		$command->log(1, 'Connection could not be established.');
	}
	$tsAdmin->logout();
?>
