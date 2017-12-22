<?php
	declare(strict_types=1);
	set_time_limit(0);
	ini_set('error_log', 'log/php_error.log');
	/**
	 * @author		Majcon
	 * @email		Majcon94@gmail.com
	 * @copyright	© 2016-2017 Majcon
	 * @version		2.5
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
		require_once 'class/functions.class.php';
		$funkcja = new Funkcje();
	}
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
		$funkcja->setConfig($config);
		$funkcja->setDb($db);
		$funkcja->setLang($l);
		$funkcja->setTs3admin($tsAdmin);
		do{
			$whoami = $tsAdmin->getElement('data', $tsAdmin->whoAmI());
			$funkcja->setClientlist($tsAdmin->getElement('data', $tsAdmin->clientList("-groups -uid -times -ip")));
			$funkcja->setServerinfo($tsAdmin->getElement('data', $tsAdmin->serverInfo()));
			$funkcja->update_activity();

			if($config['functions_addRank']['on'] == true) {
				$funkcja->addRank();
			}

			if($config['functions_aktualna_data']['on'] == true) {
				$funkcja->aktualna_data();
			}

			if($config['functions_aktualnie_online']['on'] == true) {
				$funkcja->aktualnie_online();
			}

			if($config['functions_anty_vpn']['on'] == true) {
				$funkcja->anty_vpn();
			}

			if($config['functions_cleanChannel']['on'] == true) {
				$funkcja->cleanChannel();
			}

			if($config['functions_channelCreate']['on'] == true) {
				$funkcja->channelCreate();
			}
			if($config['functions_channelNumber']['on'] == true) {
				$funkcja->channelNumber();
			}

			if($config['functions_delRank']['on'] == true) {
				$funkcja->delRank();
			}

			if($config['functions_groupOnline']['on'] == true) {
				$funkcja->groupOnline();
			}

			if($config['functions_register']['on'] == true) {
				$funkcja->register();
			}

			if($config['functions_rekord_online']['on'] == true) {
				$funkcja->rekord_online();
			}

			if($config['functions_sendAd']['on'] == true) {
				$funkcja->sendAd();
			}

			if($config['functions_servername']['on'] == true) {
				$funkcja->servername();
			}

			if($config['functions_sprchannel']['on'] == true) {
				$funkcja->sprchannel();
			}

			if($config['functions_sprnick']['on'] == true) {
				$funkcja->sprnick();
			}

			if($config['functions_statusTwitch']['on'] == true) {
				$funkcja->statusTwitch();
			}
			
			if($config['functions_statusYt']['on'] == true) {
				$funkcja->statusYt();
			}

			if($config['functions_poke']['on'] == true) {
				$funkcja->poke();
			}

			if($config['functions_top_activity_time']['on'] == true) {
				$funkcja->top_activity_time();
			}

			if($config['functions_top_connections']['on'] == true) {
				$funkcja->top_connections();
			}

			if($config['functions_top_longest_connection']['on'] == true) {
				$funkcja->top_longest_connection();
			}

			if($config['functions_welcome_messege']['on'] == true) {
				$funkcja->welcome_messege();
			}
			sleep(1);
		}while($whoami['virtualserver_status'] == 'online');
	}else{
		$funkcja->log(1, 'Connection could not be established.');
	}
	$tsAdmin->logout();
?>
