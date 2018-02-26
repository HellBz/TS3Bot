<?php

	class Functions{

		protected $clientlist;

		protected $serverinfo;

		protected $config;

		protected static $tsAdmin = NULL;

		protected static $l = NULL;

		protected static $db = NULL;
		
		private static $update_activity_time = NULL;

		public function setClientlist($clientlist): void
		{
			$this->clientlist = $clientlist;
		}

		public function setServerinfo($serverinfo): void
		{
			$this->serverinfo = $serverinfo;
		}

		public function setConfig($config): void
		{
			$this->config = $config;
		}
		
		public function setTs3admin($ts3admin): void
		{
			self::$tsAdmin = $ts3admin;
		}
		
		public function setLang($lang): void
		{
			self::$l = $lang;
		}
		
		public function setDb($database): void
		{
			self::$db = $database;
		}

		/**
		 * cenzor()
		 * Funkcja sprawdza czy string zawiera przekleństwo.
		 * @param string $txt
		 * @param int $add
		 * @author	Majcon
		 * @return	bool
		 **/
		public function cenzor($txt, $add): bool
		{
			$cenzor = array('bit(h|ch)', '(ch|h)(w|.w)(d|.d)(p|.p)', '(|o)cip', '(|o)(ch|h)uj(|a)', '(|do|na|po|do|prze|przy|roz|u|w|wy|za|z|matkojeb)jeb(|a|c|i|n|y)', '(|do|na|naw|od|pod|po|prze|przy|roz|spie|roz|poroz|s|u|w|za|wy)pierd(a|o)', 'fu(ck|k)', '/[^.]+\.[^.]+$/', "/^(\"|').+?\\1$/", '(|po|s|w|za)(ku|q)rw(i|y)', 'k(у|u)rw', 'k(у|u)tas', '(|po|wy)rucha', 'motherfucker', 'piczk', '(|w)pi(z|z)d');
			if($add == 1){
				$cenzor = array_merge($this->config['functions_sprnick']['slowa'], $cenzor);
			}
			foreach($cenzor as $c) {
				if(preg_match('~'.$c.'~s', strtolower($txt))){
					return true;
				}
			}
			return false;
		}

		/**
		 * channelDelete()
		 * Funkcja usuwa kanał.
		 * @author	Majcon
		 * @return	void
		 **/
		public function channelDelete($i=0, $cid): void
		{
			self::$db->query("DELETE FROM `channel` WHERE `cid` = {$cid}");
			$data = [ 'channel_name' => $i.'. WOLNY', 'channel_topic' => 'WOLNY', 'channel_description' => '', 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxclients_unlimited' => 0, 'channel_maxclients' => '0', 'channel_maxfamilyclients' => '0', 'channel_password' => '' ];
			self::$tsAdmin->channelEdit($cid, $data);
			$channellist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelList('-topic'));
			foreach($channellist as $cl3){
				if($cl3['pid'] == $cid){
					self::$tsAdmin->channelDelete($cl3['cid']);
				}
			}
			$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($cid));
			if(!empty($channelClientList)){
				foreach($channelClientList as $ccl){
					self::$tsAdmin->clientKick($ccl['clid'], 'channel', self::$l->kick_channelDelete);
				}
			}
			$channelgroupclientlist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelGroupClientList($cid));
			if(!empty($channelgroupclientlist)){
				foreach($channelgroupclientlist as $cgcl){
					self::$tsAdmin->setClientChannelGroup(8, $cid, $cgcl['cldbid']);
				}
			}
		}

		/**
		 * file_get_contents_curl()
		 * 
		 * @author	Majcon
		 * @return	string
		 **/
		public function file_get_contents_curl($url): string
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);       
			$data = curl_exec($ch);
			curl_close($ch);
			return $data;
		}

		/**
		 * getUrlChannel()
		 * Funkcja tworzy link do kanału.
		 * @author	Majcon
		 * @return	string
		 **/
		public function getUrlChannel($cid, $cn): string
		{
			return '[B][URL=channelID://'.$cid.']'.$cn.'[/URL][/B]';
		}

		/**
		 * getUrlName()
		 * Funkcja tworzy link do użytkownika.
		 * @author	Majcon
		 * @return	string
		 **/
		public function getUrlName($cdid, $cuid, $cnn): string
		{
			return '[B][URL=client://'.$cdid.'/'.$cuid.']'.$cnn.'[/URL][/B]';
		}

		/**
		 * log()
		 * Funkcja zamisuje logi
		 * @param string $txt
		 * @author	Majcon
		 * @return	void
		 **/
		public function log($error, $txt): void
		{
			if($this->config['functions_log']['on'] == true){
				if($error == 1 && $this->config['functions_log']['power'] > 1){
					$txt = '['.date('H:i:s').'] '.$txt."\n";
					$fp = @fopen('log/'.date('d.m.Y').'_error.log', "a"); 
					flock($fp, 2); 
					fwrite($fp, $txt); 
					flock($fp, 3); 
					fclose($fp);
				}
				if($error == 2 && $this->config['functions_log']['power'] > 0){
					$txt = '['.date('H:i:s').'] '.$txt."\n";
					$fp = @fopen('log/'.date('d.m.Y').'_log.log', "a"); 
					flock($fp, 2); 
					fwrite($fp, $txt); 
					flock($fp, 3); 
					fclose($fp);
				}
			}
		}

		/**
		 * padding_numbers()
		 * @param int $number
		 * @param string $t1
		 * @param string $t2
		 * @param string $t3
		 * @author	Majcon
		 * @return	void
		 **/
		public function padding_numbers($number, $t1, $t2, $t3): string
		{
			$number %= 100;
			if($number == 0 || ($number >=5 && $number <=21)){
				return $t3;
			}
			if($number == 1){
				return $t1;
			}
			if($number > 1 && $number < 5){
				return $t2;
			}
			$number %= 10;
			if($number >1 && $number < 5){
				return $t2;
			}
			return $t3;
		}

		/**
		 * przelicz_czas()
		 * Funkjca przelicza czas.
		 * @param int $time
		 * @author	Majcon
		 * @return	array
		 **/
		public function przelicz_czas($time): array
		{
			$dni_r = $time / 86400;
			$data['d'] = floor($dni_r);
			$rzd = $time - $data['d'] * 86400;
			$godzin_r = $rzd / 3600;
			$data['H'] = floor($godzin_r);
			$rzg = $rzd - $data['H'] * 3600;
			$minut_r = $rzg / 60;
			$data['i'] = floor($minut_r);
			$data['s']  = $rzg - $data['i'] * 60;
			return $data;
		}

		/**
		 * update_activity()
		 * Funkcja aktualizuje aktywność użytkowników.
		 * @author	Majcon
		 * @return	void
		 **/
		public function update_activity(): void
		{
			if(self::$update_activity_time <= time()){
				$update_activity_clientlist = [];
				foreach($this->clientlist as $cl){
					if(!in_array($cl['clid'], $update_activity_clientlist)){
						$clientInfo = self::$tsAdmin->getElement('data', self::$tsAdmin->clientInfo($cl['clid']));
						$query = self::$db->query("SELECT COUNT(id) AS `count`, `longest_connection` FROM `users` WHERE `cldbid` = {$cl['client_database_id']} LIMIT 1");
						while($row = $query->fetch()){
							$longest_connection = $row['longest_connection'];
							$count = $row['count'];
						}
						if($count == 0){
							self::$db->query("INSERT INTO `users` VALUES (NULL, {$cl['client_database_id']}, '{$cl['client_nickname']}', '{$cl['client_unique_identifier']}', 0, 0, 0, ".time().", {$clientInfo['client_created']}, '{$clientInfo['client_servergroups']}')");
						}else{
							if($longest_connection < $clientInfo['connection_connected_time']){
								$longest_connection = $clientInfo['connection_connected_time'];
							}
							if($cl['client_idle_time'] < 300000){
								self::$db->query("UPDATE `users` SET `connections` = {$clientInfo['client_totalconnections']}, `longest_connection` = {$longest_connection}, `time_activity` = time_activity+60, `last_activity` = ".time().", `client_nickname` = '{$cl['client_nickname']}', `gid` = '{$clientInfo['client_servergroups']}', `regdate` = {$clientInfo['client_created']}  WHERE `cldbid` = {$cl['client_database_id']}");
							}else{
								self::$db->query("UPDATE `users` SET `connections` = {$clientInfo['client_totalconnections']}, `longest_connection` = {$longest_connection}, `last_activity` = ".time().", `client_nickname` = '{$cl['client_nickname']}', `gid` = '{$clientInfo['client_servergroups']}', `regdate` = {$clientInfo['client_created']}  WHERE `cldbid` = {$cl['client_database_id']}");
							}
						}
						$update_activity_clientlist[] = $cl['clid'];
					}
				}
				self::$update_activity_time = time()+60;
			}
		}

		/**
		 * wyswietl_czas()
		 * Funkcja wyświetla czas.
		 * @param array $data
		 * @param int $d
		 * @param int $h
		 * @param int $i
		 * @param int $t
		 * @author	Majcon
		 * @return	string
		 **/
		public function wyswietl_czas($data, $d=0, $h=0, $i=0, $s=0, $t=0): string
		{
			$txt_time = null;
			if($d == 1){
				if($data['d'] == 0){
					$txt_time .= '';
				}else{
					self::$l->time_d1_wyswietl_czas = $this->padding_numbers($data['d'], self::$l->time_d1_wyswietl_czas, self::$l->time_d2_wyswietl_czas, self::$l->time_d2_wyswietl_czas);
					$txt_time .= $data['d'].' '.self::$l->time_d1_wyswietl_czas.' ';
				}
			}else{
				$data['d'] = 0;
			}
			if($h == 1){
				if($data['d'] == 0 && $data['H'] == 0){
					$txt_time .= '';
				}else{
					self::$l->time_h1_wyswietl_czas = $this->padding_numbers($data['H'], self::$l->time_h1_wyswietl_czas, self::$l->time_h2_wyswietl_czas, self::$l->time_h3_wyswietl_czas);
					$txt_time .= $data['H'].' '.self::$l->time_h1_wyswietl_czas.' ';
				}
			}
			else{
				$data['H'] = 0;
			}
			if($i == 1){
				if($data['d'] == 0 && $data['H'] == 0 && $data['i'] == 0){
					$txt_time .= '';
				}else{
					self::$l->time_i1_wyswietl_czas = $this->padding_numbers($data['i'], self::$l->time_i1_wyswietl_czas, self::$l->time_i2_wyswietl_czas, self::$l->time_i3_wyswietl_czas);
					$txt_time .= $data['i'].' '.self::$l->time_i1_wyswietl_czas.' ';
				}
			}else{
				$data['i'] = 0;
			}
			if($s == 1){
				if($data['d'] == 0 && $data['s'] == 0 && $data['i'] == 0 && $data['H'] == 0){
					$txt_time .= '';
				}else{
					$l->time_s1_wyswietl_czas = $this->padding_numbers($data['s'], self::$l->time_s1_wyswietl_czas, self::$l->time_s2_wyswietl_czas, self::$l->time_s3_wyswietl_czas);
					$txt_time .= $data['s'].' '.self::$l->time_s1_wyswietl_czas;
				}
			}
			if(empty($txt_time)){
				if($t == 0){
					$txt_time = '0 '.self::$l->time_s3_wyswietl_czas;
				}else{
					$txt_time = 'TERAZ';
				}
			}
			return $txt_time;
		}
	}
?>
