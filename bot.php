<?php
	set_time_limit(0);
	ini_set('error_log', 'log/php_error.log');
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
		$funkcja = new funkcje();
	}
	if(!file_exists("class/ts3admin.class.php") == true) {
		die("Plik ts3admin.class.php nie istnieje!");
	}else{
		require_once 'class/ts3admin.class.php';
	}
	$db = new PDO('sqlite:ts3bot.sqlitedb');
	$tsAdmin = new ts3admin($config['server']['ip'], $config['server']['queryport']);
	if($tsAdmin->getElement('success', $tsAdmin->connect())) {
		$tsAdmin->login($config['server']['login'], $config['server']['password']);
		$tsAdmin->selectServer($config['server']['port']);
		$tsAdmin->setName($config['server']['nick']);
		do{
			$whoami = $tsAdmin->getElement('data', $tsAdmin->whoAmI());
			$funkcja->setClientlist($tsAdmin->getElement('data', $tsAdmin->clientList("-groups -uid -times -ip")));
			$funkcja->setServerinfo($tsAdmin->getElement('data', $tsAdmin->serverInfo()));
			$funkcja->setConfig($config);
			
			if($config['functions_admins_ts_online']['on'] == true) {
				$funkcja->admins_ts_online();
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

			if($config['functions_clean_channel']['on'] == true) {
				$funkcja->clean_channel();
			}

			if($config['functions_clean_channel']['on'] == true) {
				$funkcja->channelCreate();
			}

			if($config['functions_ChannelNumber']['on'] == true) {
				$funkcja->ChannelNumber();
			}

			if($config['functions_poke']['on'] == true) {
				$funkcja->poke();
			}
			if($config['functions_register']['on'] == true) {
				$funkcja->register();
			}

			if($config['functions_rekord_online']['on'] == true) {
				$funkcja->rekord_online();
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

			if($config['functions_update_activity']['on'] == true) {
				$funkcja->update_activity();
			}

			if($config['functions_welcome_messege']['on'] == true) {
				$funkcja->welcome_messege();
			}
			sleep(1);
		}while($whoami['virtualserver_status'] == 'online');
	}else{
		$function->log('Connection could not be established.');
	}
	$tsAdmin->logout();
?>
