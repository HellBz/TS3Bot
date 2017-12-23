<?php

	class Funkcje {

		private $clientlist = null;

		private $serverinfo = null;

		private $config = null;

		private static $welcome_messege_list = [];

		private static $older_groupOnline_name = [];

		private static $older_groupOnline = [];

		private static $groupOnline_time_edition = 0;

		private static $aktualna_data = NULL;

		private static $aktualnie_online = NULL;

		private static $online_anty_vpn = [];

		private static $channelNumberTime = 0;

		private static $czas_administracja_poke = [];

		private static $czas_informacji_poke = [];

		private static $sendAd_time = 0;

		private static $servername_online = 0;

		private static $statusTwitch_time = 0;

		private static $statusYt_time = 0;

		private static $statusYt_description = [];

		private static $top_activity_time = 0;

		private static $description_top_connections  = NULL;
		
		private static $edit_top_connections = 0;

		private static $description_top_longest_connection = 0;

		private static $edit_top_longest_connection = NULL;

		private static $update_activity_time = 0;

		private static $tsAdmin = NULL;

		private static $l = NULL;

		private static $db = NULL;




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
		 * addRank()
		 * Funkcja dodaje range po wejściu na kanało o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function addRank(): void
		{
			foreach($this->config['functions_addRank']['cid_gid'] as $klucz => $value) {
				$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($klucz, '-groups'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						$explode = explode(',', $ccl['client_servergroups']);
						if(!in_array($value, $explode)){
							self::$tsAdmin->serverGroupAddClient($value, $ccl['client_database_id']);
						}
					}
				}
			}
		}
		
		/**
		 * aktualna_data()
		 * Funkcja ustawia aktualną datę jako nazwa kanału o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function aktualna_data(): void
		{
			$data = date($this->config['functions_aktualna_data']['format']);
			if($data != self::$aktualna_data){
				self::$tsAdmin->channelEdit($this->config['functions_aktualna_data']['cid'], array('channel_name' => self::$l->sprintf(self::$l->success_size_admins_ts_online, $data)));
				self::$aktualna_data = $data;
			}
		}

		/**
		 * aktualnie_online()
		 * Funkcja Funkcja ustawia aktualną liczbę osób online jako nazwa kanału o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function aktualnie_online(): void
		{
			$count = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
			if(self::$aktualnie_online != $count){
				self::$tsAdmin->channelEdit($this->config['functions_aktualnie_online']['cid'], array('channel_name' => self::$l->sprintf(self::$l->success_aktualnie_online, $count, $this->padding_numbers($count, 'osoba', 'osoby', 'osób'))));
				self::$aktualnie_online = $count;
			}
		}

		/**
		 * anty_vpn()
		 * Funkcja wyrzuca użytkowników, którzy posiadają proxy.
		 * @author	Majcon
		 * @return	void
		 **/
		public function anty_vpn(): void
		{
			$aktualnie_online = [];
			foreach($this->clientlist as $cl){
				if($cl['client_type'] == 0 && !in_array($cl['client_unique_identifier'], $this->config['functions_anty_vpn']['client_unique_identifier'])) {
					$aktualnie_online[$cl['clid']] = $cl['connection_client_ip'];
				}
			}
			$array_diff = array_diff($aktualnie_online, self::$online_anty_vpn);
			if(!empty($array_diff)){
				foreach($array_diff as $key => $value){
					if(!empty($value)){
						$ch = curl_init();
						curl_setopt_array($ch, [
							CURLOPT_URL => "http://v2.api.iphub.info/ip/{$value}",
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HTTPHEADER => ["X-Key: {$this->config['functions_anty_vpn']['key']}"]
						]);
						if(json_decode(curl_exec($ch))->block == 1){
							$clientInfo = self::$tsAdmin->getElement('data', self::$tsAdmin->clientInfo($key));
							self::$tsAdmin->clientKick($key, 'server', self::$l->success_kick_anty_vpn);
							$this->log(2, 'Wyrzucono (client_nickname: '.$clientInfo['client_nickname'].') za używanie VPN.');
							unset($aktualnie_online[$key]);
						}
					}
				}
				self::$online_anty_vpn = $aktualnie_online;
			}
		}

		/**
		 * clean_channel()
		 * Funkcja czyści kanały, które nie są aktywne dłużej niż 7 dni w podanym sektorze.
		 * @author	Majcon
		 * @return	void
		 **/
		public function cleanChannel(): void
		{
			$channellist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelList("-topic -flags -voice -limits -icon"));
			$i = 0;
			foreach($channellist as $cl){
				if($cl['pid'] == $this->config['functions_cleanChannel']['pid']){
					$i++;
					if($cl['channel_topic'] != 'WOLNY' && $cl['channel_topic'] != date('d.m.Y')){
						if(!empty(self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($cl['cid'])))){
							self::$tsAdmin->channelEdit($cl['cid'], array('channel_topic' => date('d.m.Y')));
						}else{
							foreach($channellist as $cl2){
								if($cl2['pid'] == $cl['cid'] && !empty(self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($cl2['cid'])))){
									self::$tsAdmin->channelEdit($cl['cid'], array('channel_topic' => date('d.m.Y')));
								}
							}
						}
						$czas_del = time()-604800;
						$czas = strtotime($cl['channel_topic']);
						if($czas <= $czas_del){
							self::$db->query("DELETE FROM `channel` WHERE `cid` = {$cl['cid']}");
							$data = [ 'channel_name' => $i.'. WOLNY', 'channel_topic' => 'WOLNY', 'channel_description' => '', 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxclients_unlimited' => 0, 'channel_maxclients' => '0', 'channel_maxfamilyclients' => '0', 'channel_password' => '' ];
							self::$tsAdmin->channelEdit($cl['cid'], $data);
							foreach($channellist as $cl3){
								if($cl3['pid'] == $cl['cid']){
									self::$tsAdmin->channelDelete($cl3['cid']);
								}
							}
							$channelgroupclientlist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelGroupClientList($cl['cid']));
							if(!empty($channelgroupclientlist)){
								foreach($channelgroupclientlist as $cgcl){
									self::$tsAdmin->setClientChannelGroup(8, $cl['cid'], $cgcl['cldbid']);
								}
							}
							$this->log(2, 'Usunięcie kanału za brak aktywności (channel name: '.$cl['channel_name'].')');
						}
					}
				}
			}
		}

		/**
		 * cenzor()
		 * Funkcja sprawdza czy string zawiera przekleństwo.
		 * @param string $txt
		 * @param int $add
		 * @author	Majcon
		 * @return	bool
		 **/
		private function cenzor($txt, $add): bool
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
		 * channelCreate()
		 * Funkcja zakłada kanały w podanym sektorze.
		 * @author	Majcon
		 * @return	void
		 **/
		public function channelCreate(): void
		{
			$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($this->config['functions_channelCreate']['cid'], "-ip"));
			if(!empty($channelClientList)){
			foreach($channelClientList as $ccl){
				$spr_czy_ma_kanal = self::$db->query("SELECT COUNT(id) AS `count`, `cid` FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']} OR `connection_client_ip` = '{$ccl['connection_client_ip']}'");
				while($scmk = $spr_czy_ma_kanal->fetch()){
						if($scmk['count'] != 0 && $scmk['cid'] != 0){
							$channelInfo = self::$tsAdmin->channelInfo($scmk['cid']);
							if(empty($channelInfo['errors'])){
								if($channelInfo['data']['channel_topic'] == 'WOLNY'){
									$count = 0;
									self::$db->query("DELETE FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']}");
								}else{
									$count = 1;
								}
							}else{
								$count = 0;
								self::$db->query("DELETE FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']}");
							}
						}else{
							$count = 0;
						}
					}
					if($count == 0){
						$zalozony = 0;
						$id = 1;
						$search = [ '%CLIENT_NICKNAME%', '%HOUR%', '%DATE%'	];
						$replace = [ $ccl['client_nickname'], date('H:i'), date('d.m.Y') ];
						$channellist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelList('-topic'));
						foreach($channellist as $chl){
							if($chl['pid'] == $this->config['functions_channelCreate']['pid']){
								$id++;
								$editid = $id-1;
								if(trim($chl['channel_topic']) == 'WOLNY'){
									$data1 = [
										'channel_name' => $editid.'. '.$ccl['client_nickname'],
										'channel_topic' => date('d.m.Y'),
										'channel_description' => str_replace($search, $replace, $this->config['functions_channelCreate']['channel_description']),
										'channel_flag_maxfamilyclients_unlimited' => 1,
										'channel_flag_maxclients_unlimited' => 1,
										'channel_maxclients' => '-1',
										'channel_maxfamilyclients' => '-1',
										'channel_password' => ''
									];
									self::$tsAdmin->channelEdit($chl['cid'], $data1);
									if($this->config['functions_channelCreate']['ile'] != 0){
										for($isub = 1; $isub <= $this->config['functions_channelCreate']['ile']; $isub++){
											$data = [ 'cpid' => $chl['cid'], 'channel_name' => $isub, 'channel_flag_permanent' => 1, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 1, 'channel_maxclients' => '-1', 'channel_maxfamilyclients' => '-1', 'channel_topic' => '' ];
											self::$tsAdmin->channelCreate($data);
										}
									}
									self::$tsAdmin->clientMove($ccl['clid'], $chl['cid']);
									self::$tsAdmin->setClientChannelGroup(5, $chl['cid'], $ccl['client_database_id']);
									self::$db->query("INSERT INTO `channel` VALUES (NULL, {$ccl['client_database_id']}, {$chl['cid']}, '{$ccl['connection_client_ip']}')");
									$this->log(2, 'Założono kanał dla (nick name: '.$ccl['client_nickname'].')');
									$zalozony = 1;
									break;
								}
							}
						}
						if($zalozony == 0){
							$data = [
								'cpid' => $this->config['functions_channelCreate']['pid'],
								'channel_name' => $id.'. '.$ccl['client_nickname'],
								'channel_description' => str_replace($search, $replace, $this->config['functions_channelCreate']['channel_description']),
								'channel_topic' => date('d.m.Y'),
								'channel_flag_permanent' => 1,
								'channel_flag_maxclients_unlimited' => 1,
								'channel_flag_maxfamilyclients_unlimited' => 1,
								'channel_maxclients' => '-1', 'channel_maxfamilyclients' => '-1'
							];
							$channelCreate = self::$tsAdmin->channelCreate($data);
							if($this->config['functions_channelCreate']['ile'] != 0){
								for($isub = 1; $isub <= $this->config['functions_channelCreate']['ile']; $isub++){
									$data = [
										'cpid' => $channelCreate['data']['cid'],
										'channel_name' => $isub, 'channel_flag_permanent' => 1,
										'channel_topic' => '',
										'channel_flag_maxclients_unlimited' => 1,
										'channel_flag_maxfamilyclients_unlimited' => 1,
										'channel_maxclients' => '-1',
										'channel_maxfamilyclients' => '-1'
									];
									$test = self::$tsAdmin->channelCreate($data);
								}
							}
							self::$tsAdmin->clientMove($ccl['clid'], $channelCreate['data']['cid']);
							self::$tsAdmin->setClientChannelGroup(5, $channelCreate['data']['cid'], $ccl['client_database_id']);
							self::$db->query("INSERT INTO `channel` VALUES (NULL, {$ccl['client_database_id']}, {$channelCreate['data']['cid']}, '{$ccl['connection_client_ip']}')");
							$this->log(2, 'Założono kanał dla nick name: '.$ccl['client_nickname']);
						}
					}else{
						self::$tsAdmin->clientMove($ccl['clid'], $this->config['functions_channelCreate']['cid_move']);
						self::$tsAdmin->clientPoke($ccl['clid'], self::$l->error_has_a_channel_channelCreate);
					}
				}
			}
		}

		/**
		 * channelNumber()
		 * Funkcja sprawdza i w razie, czego poprawia numer kanału.
		 * @author	Majcon
		 * @return	void
		 **/
		public function channelNumber(): void
		{
			if(self::$channelNumberTime+10 < time()){
				$i = 0;
				$channellist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelList('-topic'));
				foreach($channellist as $chl){
					if($chl['pid'] == $this->config['functions_channelNumber']['pid']){
						$i++;
						preg_match_all('/(\d+)(.*)/is', $chl['channel_name'], $matches);
						if(!empty($matches[1][0])){
							if($matches[1][0] != $i){
								$matches[2][0] = $matches[2][0] ?? NULL;
								if(!empty($matches[2][0]) && $matches[2][0]{0} == trim($this->config['functions_channelNumber']['separator'])){
									$matches[2][0] = trim(substr(trim($matches[2][0]), 1));
								}
								self::$tsAdmin->channelEdit($chl['cid'], ['channel_name' => $i.$this->config['functions_channelNumber']['separator'].$matches[2][0]]);
							}
						}else{
							self::$tsAdmin->channelEdit($chl['cid'], ['channel_name' => $i.$this->config['functions_channelNumber']['separator'].$chl['channel_name']]);

						}
					}
				}
				self::$channelNumberTime = time()+10;
			}
		}

		/**
		 * addRank()
		 * Funkcja dodaje range po wejściu na kanało o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function delRank(): void
		{
			foreach($this->config['functions_delRank']['cid_gid'] as $klucz => $value) {
				$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($klucz, '-groups'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						$explode = explode(',', $ccl['client_servergroups']);
						if(in_array($value, $explode)){
							self::$tsAdmin->serverGroupDeleteClient($value, $ccl['client_database_id']);
						}
					}
				}
			}
		}

		/**
		 * groupOnline()
		 * Funkcja wyświetla listę osób z podanej grupy w opisie na kanale o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function groupOnline(): void
		{
			foreach($this->config['functions_groupOnline']['cid'] as $cid => $value){
				$i_online = 0;
				$i_all = 0;
				$groupOnline = NULL;
				self::$older_groupOnline[$cid] = self::$older_groupOnline[$cid] ?? NULL;
				self::$older_groupOnline_name[$cid] = self::$older_groupOnline_name[$cid] ?? NULL;
				$channel_description = $value['title'];
				foreach($value['gid'] as $gid => $name){
					$serverGroupClientList = self::$tsAdmin->serverGroupClientList($gid, '-names');
					$serverGroupClientListarray_filter = array_filter($serverGroupClientList['data'][0] ?? []);
					if(!empty($serverGroupClientListarray_filter)){
						$channel_description .= $name;
						foreach($serverGroupClientList['data'] as $sgcl){
							$i_all++;
							foreach($this->clientlist as $cl){
								if($sgcl['cldbid'] == $cl['client_database_id']){
									$online = true;
									$i_online++;
									$channelinfo = self::$tsAdmin->getElement('data', self::$tsAdmin->channelInfo($cl['cid']));
									$channel = self::$l->sprintf(self::$l->channel_groupOnline, $cl['cid'], $channelinfo['channel_name']);
									$nick = self::$l->sprintf(self::$l->nick_groupOnline, $cl['client_database_id'], $cl['client_unique_identifier'], $cl['client_nickname']);
									$channel_description .= self::$l->sprintf(self::$l->groupOnline_online, $nick, $channel);
									$groupOnline .= $nick.$channel;
									break;
								}else{
									$online = false;
								}
							}
							if($online == false){
								$query = self::$db->query("SELECT COUNT(id) AS `count`, `last_activity` FROM `users` WHERE `cldbid` = {$sgcl['cldbid']} LIMIT 1");
								while($row = $query->fetch()){
									if($row['count'] != 0){
										$last_activity = $row['last_activity'];
									}else{
										$last_activity = 0;
									}
								}
								$clientdbinfo = self::$tsAdmin->getElement('data', self::$tsAdmin->clientDbInfo($sgcl['cldbid']));
								$data = $this->przelicz_czas(time()-$last_activity);
								$txt_time = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
								$nick = self::$l->sprintf(self::$l->nick_groupOnline, $clientdbinfo['client_database_id'], $clientdbinfo['client_unique_identifier'], $clientdbinfo['client_nickname']);
								$channel_description .= self::$l->sprintf(self::$l->groupOnline_offline, $nick, $txt_time);
								$groupOnline .= $nick.$txt_time;
							}
						}
					}
				}
				if(self::$older_groupOnline[$cid] != $groupOnline || self::$groupOnline_time_edition+60 < time()){
					$data['channel_description'] = $channel_description;
					$groupOnline_name = self::$l->sprintf($value['channel_name'], $i_online, $i_all);
					if($value['name_online'] == true && self::$older_groupOnline_name[$cid] != $groupOnline_name){
						self::$older_groupOnline_name[$cid] = $groupOnline_name;
						$data['channel_name'] = $groupOnline_name;
					}
					self::$tsAdmin->channelEdit($cid, $data);
					self::$older_groupOnline[$cid] = $groupOnline;
					self::$groupOnline_time_edition = time()+60;
				}
			}
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
		private function padding_numbers($number, $t1, $t2, $t3): string
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
		 * poke()
		 * Funkcja puka podane grupy jeżeli ktoś wbije na podany kanał.
		 * @author	Majcon
		 * @return	void
		 **/
		public function poke(): void
		{
			$administracja_po_poke = array();
			$admin_online = array();
			foreach(self::$czas_administracja_poke as $key => $value){
				if($value <= time()){
					unset(self::$czas_administracja_poke[$key]);
				}else{
					$administracja_po_poke[] = $key;
				}
			}
			foreach($this->config['functions_poke']['cid_gid'] as $channel => $value){
				if(empty(self::$czas_informacji_poke[$channel][0])){
					self::$czas_informacji_poke[$channel][1] = 0;
					self::$czas_informacji_poke[$channel][0] = 0;
				}
				$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($channel, '-groups -uid'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						if(empty(array_intersect(explode(',', $ccl['client_servergroups']), $value))){
							$online_na_kanale[] = $ccl['clid'];
							$nicki[] =  self::$l->sprintf(self::$l->nick_poke, $ccl['cid'], $ccl['client_unique_identifier'], $ccl['client_nickname']);
						}else{
							$admin_online[] = $ccl['clid'];
						}
					}
					if(empty($admin_online)){
						$lista_adminow = array();
						foreach($this->clientlist as $cl) {
							if(!empty(array_intersect(explode(',',$cl['client_servergroups']), $value))){
								if(!in_array($cl['cid'], $this->config['functions_poke']['cidafk'])){
									$lista_adminow[] = $cl['clid'];
								}
							}
						}
						$administracja_poke = array_diff($lista_adminow, $administracja_po_poke);
						if(!empty($administracja_poke)){
							$nicki = implode(', ', $nicki);
							foreach($administracja_poke as $ap){
								if($this->config['functions_poke']['poke_message'] == 1){
									self::$tsAdmin->clientPoke($ap, self::$l->sprintf(self::$l->success_admin_poke, $nicki));
								}else{
									self::$tsAdmin->sendMessage(1, $ap, self::$l->sprintf(self::$l->success_admin_poke, $nicki));
								}
								
								self::$czas_administracja_poke[$ap] = time()+$this->config['functions_poke']['admin_time'];
							}
							if(self::$czas_informacji_poke[$channel][0] == 0){
								foreach($online_na_kanale as $onk){
									self::$tsAdmin->sendMessage(1, $onk, self::$l->success_he_was_informed_poke);
								}
								self::$czas_informacji_poke[$channel][0] = 1;
								self::$czas_informacji_poke[$channel][1] = time()+$this->config['functions_poke']['user_time'];
							}	
						}else{
							if(self::$czas_informacji_poke[$channel][0] == 0){
								foreach($online_na_kanale as $onk){
									self::$tsAdmin->sendMessage(1, $onk, self::$l->error_admin_offline_poke);
								}
								self::$czas_informacji_poke[$channel][0] = 1;
								self::$czas_informacji_poke[$channel][1] = time()+$this->config['functions_poke']['user_time'];
							}
						}
					}else{
						foreach($admin_online as $ao){
							self::$czas_administracja_poke[$ao] = time()+$this->config['functions_poke']['admin_time'];
						}
						self::$czas_informacji_poke[$channel][0] = 1;
						self::$czas_informacji_poke[$channel][1] = time()+$this->config['functions_poke']['user_time'];
					}
				}
				if(self::$czas_informacji_poke[$channel][1] <= time()){
					self::$czas_informacji_poke[$channel][0] = 0;
				}
			}
		}

		/**
		 * przelicz_czas()
		 * Funkjca przelicza czas.
		 * @param int $time
		 * @author	Majcon
		 * @return	array
		 **/
		private function przelicz_czas($time): array
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
		 * register()
		 * Funkcja automatycznie rejestruje użytkownika gdy on wbije na podane id kanału.
		 * @author	Majcon
		 * @return	void
		 **/
		public function register(): void
		{
			foreach($this->clientlist as $cl) {
				if($cl['client_type'] == 0) {
					$rangiexplode = explode(',', $cl['client_servergroups']);
					if(!in_array($this->config['functions_register']['gidm'], $rangiexplode) && !in_array($this->config['functions_register']['gidk'], $rangiexplode)){
						if($cl['cid'] == $this->config['functions_register']['cidm']){
								self::$tsAdmin->serverGroupAddClient($this->config['functions_register']['gidm'], $cl['client_database_id']);
								$this->log(2, 'Zarejestrowano nick name: '.$cl['client_nickname']);
						}
						if($cl['cid'] == $this->config['functions_register']['cidk']){
								self::$tsAdmin->serverGroupAddClient($this->config['functions_register']['gidk'], $cl['client_database_id']);
								$this->log(2, 'Zarejestrowano nick name: '.$cl['client_nickname']);
						}
					}
				}
			}
		}

		/**
		 * rekord_online()
		 * Funkcja ustawia rekord osób online jako nazwa kanału o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function rekord_online(): void
		{
			$rekord = file_get_contents('includes/rekord.php');
			$count = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
			if($rekord < $count){
				self::$tsAdmin->channelEdit($this->config['functions_rekord_online']['cid'], array('channel_name' => self::$l->sprintf(self::$l->success_rekord_online, $count, $this->padding_numbers($count, 'osoba', 'osoby', 'osób'))));
				file_put_contents('includes/rekord.php', $count);
				$this->log(2, 'Ustanowiono rekord osób online: '.$count);
			}
		}

		/**
		 * sendAd()
		 * Funkcja wysyła reklame co określony czas.
		 * @author	Majcon
		 * @return	void
		 **/
		public function sendAd(): void
		{
			if(self::$sendAd_time <= time()){
				$array_rand = $this->config['functions_sendAd']['txt_group'][array_rand($this->config['functions_sendAd']['txt_group'])];
				foreach($array_rand as $key => $value) {
					$txt = $key;
					$group = $value;
				}
				if($group[0] == -1){
					self::$tsAdmin->sendMessage(3, 1, $txt);
				}else{
					foreach($this->clientlist as $cl) {
						if(array_intersect(explode(',', $cl['client_servergroups']), $group) || $group[0] == 0){
							self::$tsAdmin->sendMessage(1, $cl['clid'], $txt);
						}
					}
				}
				self::$sendAd_time = time()+$this->config['functions_sendAd']['time']*1;
			}
		}


		/**
		 * servername()
		 * Funkcja ustawia nazwę serwera wraz z liczbą osób online.
		 * @author	Majcon
		 * @return	void
		 **/
		public function servername(): void
		{
			$count = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
			if(self::$servername_online != $count){
				self::$tsAdmin->serverEdit(array('virtualserver_name' => str_replace('{1}', $count, $this->config['functions_servername']['name'])));
				self::$servername_online = $count;
			}
		}

		/**
		 * sprchannel()
		 * Funkcja sprawdza nazwy kanału pod względem wulgaryzmów.
		 * @author	Majcon
		 * @return	void
		 **/
		public function sprchannel(): void
		{
			$channellist = self::$tsAdmin->getElement('data', self::$tsAdmin->channelList());
			$i = 0;
			foreach($channellist as $cl){
				$delete = 0;
				if($cl['pid'] == $this->config['functions_sprchannel']['pid']){
					$i++;
					if($this->cenzor($cl['channel_name'], 0) == true){
						$delete = 1;
					}else{
						foreach($channellist as $cl2){
							if($cl2['pid'] == $cl['cid'] && $this->cenzor($cl2['channel_name'], 0) == true){
								$delete = 1;
							}
						}
					}
					if($delete == 1){
						$data = [ 'channel_name' => $i.'. WOLNY', 'channel_topic' => 'WOLNY', 'channel_description' => '', 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxclients_unlimited' => 0, 'channel_maxclients' => '0', 'channel_maxfamilyclients' => '0' ];
						self::$tsAdmin->channelEdit($cl['cid'], $data);
						$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($cl['cid']));
						if(!empty($channelClientList)){
							foreach($channelClientList as $ccl){
								self::$tsAdmin->clientKick($ccl['clid'], 'channel', self::$l->kick_sprchannel);//lang
							}
						}
						foreach($channellist as $cl3){
							if($cl3['pid'] == $cl['cid']){
								self::$tsAdmin->channelDelete($cl3['cid']);
							}
						}
						self::$db->query("DELETE FROM `channel` WHERE `cid` = {$cl['cid']}");
						$this->log(2, 'Usunięcie kanału za wulgarną nazwę (channel name: '.$cl['channel_name'].')');
					}
				}
			}
		}

		/**
		 * sprnick()
		 * Funkcja sprawdza nicki użytkowników pod względem wulgaryzmów.
		 * @author	Majcon
		 * @return	void
		 **/
		public function sprnick(): void
		{
			foreach($this->clientlist as $cl) {
				if(!array_intersect(explode(',', $cl['client_servergroups']), $this->config['functions_sprnick']['gid'])){
					if($this->cenzor($cl['client_nickname'], 1) == true){
						self::$tsAdmin->clientPoke($cl['clid'], self::$l->poke_sprnick);
						self::$tsAdmin->clientKick($cl['clid'], "server", self::$l->kick_sprnick);
						$this->log(2, 'Wyrzucono użytkownika ('.$cl['client_nickname'].') za wulgarny nick (client unique identifier: '.$cl['client_unique_identifier'].')');
					}
				}
			}
		}

		/**
		 * statusTwitch()
		 * Funkcja ustawia w opisie status twitch.
		 * @author	Majcon
		 * @return	void
		 **/
		public function statusTwitch(): void
		{
			if(self::$statusTwitch_time <= time()){
				foreach($this->config['functions_statusTwitch']['cid_name'] as $cid => $name){
					$jdc = json_decode(file_get_contents('https://api.twitch.tv/kraken/streams/'.$name.'?client_id=56o6gfj3nakgeaaqpku3cugkf7lgzk'));
					if($jdc->stream == null){
						$channel_description = self::$l->sprintf(self::$l->offline_statusTwitch, $name);
						$channelinfo = self::$tsAdmin->getElement('data', self::$tsAdmin->channelInfo($cid));
						if($channelinfo['channel_description'] != $channel_description){
							self::$tsAdmin->channelEdit($cid, array('channel_description' => $channel_description));
						}
					}else{
						$channel_description = self::$l->sprintf(self::$l->online_statusTwitch, $jdc->stream->channel->url, $name, $jdc->stream->game, $jdc->stream->channel->status, $jdc->stream->viewers, $jdc->stream->preview->medium);
						self::$tsAdmin->channelEdit($cid, array('channel_description' => $channel_description));
					}
				}
				self::$statusTwitch_time = time()+60;
			}
		}

		/**
		 * statusYt()
		 * Funkcja liczbę subskrypcji w nazwie oraz podstawowe informacje w opisie.
		 * @author	Majcon
		 * @return	void
		 **/
		public function statusYt(): void
		{
			if(self::$statusYt_time <= time()){
				foreach($this->config['functions_statusYt']['cid_id'] as $cid => $id){
					self::$statusYt_description[$cid] = self::$statusYt_description[$cid] ?? NULL;
					$jdc = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$id}&key={$this->config['functions_statusYt']['key']}"));
					$channel_description = self::$l->sprintf(self::$l->channel_description_statusYt, $jdc->items[0]->id, $jdc->items[0]->snippet->title, $jdc->items[0]->statistics->subscriberCount, $jdc->items[0]->statistics->viewCount, $jdc->items[0]->snippet->description);
					$channel_name = self::$l->sprintf(self::$l->channel_name_statusYt, $jdc->items[0]->snippet->title, $jdc->items[0]->statistics->subscriberCount);
					self::$tsAdmin->channelEdit($cid, [ 'channel_name' => $channel_name ]);
					if(self::$statusYt_description[$cid] != $channel_description){
						self::$tsAdmin->channelEdit($cid, [ 'channel_description' => $channel_description ]);
						self::$statusYt_description[$cid] = $channel_description;
					}
				}
				self::$statusYt_time = time()+60;
			}
		}
		/**
		 * top_connections()
		 * Funkcja ustawia w opisie kanału o podanym ID TOP 10 aktywnych użytkowników.
		 * @author	Majcon
		 * @return	void
		 **/
		public function top_activity_time(): void
		{
			if(self::$top_activity_time <= time()){
				$s = 0;
				$top = NULL;
				if(!empty($this->config['functions_top_activity_time']['cldbid'])){
					$cldbid = implode(",", $this->config['functions_top_activity_time']['cldbid']);
				}
				$query = self::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `time_activity` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `time_activity` DESC LIMIT {$this->config['functions_top_activity_time']['limit']}");
				while($row = $query->fetch()){
					$s++;
					$data = $this->przelicz_czas($row['time_activity'], 1);
					$data = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
					$nick = "[B][URL=client://{$row['cldbid']}/{$row['cui']}]{$row['client_nickname']}[/URL][/B]";
					$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$data}\n[/SIZE]";
				}
				self::$tsAdmin->channelEdit($this->config['functions_top_activity_time']['cid'], array('channel_description' => $top));
				self::$top_activity_time = time()+60;
			}
		}

		/**
		 * top_connections()
		 * Funkcja ustawia w opisie kanału o podanym ID TOP 10 połączeń z serwerem.
		 * @author	Majcon
		 * @return	void
		 **/
		public function top_connections(): void
		{
			$s = 0;
			$top = NULL;
			if(!empty($this->config['functions_top_connections']['cldbid'])){
				$cldbid = implode(",", $this->config['functions_top_activity_time']['cldbid']);
			}
			$query = self::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `connections` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `connections` DESC LIMIT {$this->config['functions_top_activity_time']['limit']}");
			while($row = $query->fetch()){
				$s++;
				$nick = "[B][URL=client://{$row['cldbid']}/{$row['cui']}]{$row['client_nickname']}[/URL][/B]";
				$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$row['connections']}\n[/SIZE]";
			}
			if($top != self::$description_top_connections){
				self::$tsAdmin->channelEdit($this->config['functions_top_connections']['cid'], array('channel_description' => $top));
				self::$description_top_connections = $top;
			}
		}

		/**
		 * top_connection_time()
		 * Funkcja ustawia w opisie kanału o podanym ID TOP 10 Najdłuższych połączeń z serwerem.
		 * @author	Majcon
		 * @return	void
		 **/
		public function top_longest_connection(): void
		{
			if(self::$edit_top_longest_connection <= time()){
				$s = 0;
				$top = NULL;
				if(!empty($this->config['functions_top_longest_connection']['cldbid'])){
					$cldbid = implode(",", $this->config['functions_top_longest_connection']['cldbid']);
				}
				$query = self::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `longest_connection` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `longest_connection` DESC LIMIT {$this->config['functions_top_activity_time']['limit']}");
				while($row = $query->fetch()){
					$s++;
					$data = $this->przelicz_czas($row['longest_connection']/1000);
					$data = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
					$nick = "[B][URL=client://{$row['cldbid']}/{$row['cui']}]{$row['client_nickname']}[/URL][/B]";
					$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$data}\n[/SIZE]";
				}
				if($top != self::$description_top_longest_connection){
					self::$tsAdmin->channelEdit($this->config['functions_top_longest_connection']['cid'], array('channel_description' => $top));
					self::$description_top_longest_connection = $top;
				}
				self::$edit_top_longest_connection = time()+300;
			}
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
				foreach($this->clientlist as $cl){
					$clientInfo = self::$tsAdmin->getElement('data', self::$tsAdmin->clientInfo($cl['clid']));
					$query = self::$db->query("SELECT COUNT(id) AS `count` FROM `users` WHERE `cldbid` = {$cl['client_database_id']} LIMIT 1");
					while($row = $query->fetch()){
						$count = $row['count'];
					}
					if($count == 0){
						self::$db->query("INSERT INTO `users` VALUES (NULL, {$cl['client_database_id']}, '{$cl['client_nickname']}', '{$cl['client_unique_identifier']}', 0, 0, 0, ".time().")");
					}else{
						if($cl['client_idle_time'] < 300000){
							self::$db->query("UPDATE `users` SET `connections` = {$clientInfo['client_totalconnections']}, `longest_connection` = {$clientInfo['connection_connected_time']}, `time_activity` = time_activity+60, `last_activity` = ".time().", `client_nickname` = '{$cl['client_nickname']}'  WHERE `cldbid` = {$cl['client_database_id']}");
						}else{
							self::$db->query("UPDATE `users` SET `connections` = {$clientInfo['client_totalconnections']}, `longest_connection` = {$clientInfo['connection_connected_time']}, `last_activity` = ".time().", `client_nickname` = '{$cl['client_nickname']}'  WHERE `cldbid` = {$cl['client_database_id']}");
						}
					}
				}
				self::$update_activity_time = time()+60;
			}
		}

		/**
		 * welcome_messege()
		 * Funkcja wysyła wiadomość powitalną.
		 * @author	Majcon
		 * @return	void
		 **/
		public function welcome_messege(): void
		{
			$listOfUser = [];
			foreach($this->clientlist as $cl) {
				if($cl['client_type'] == 0) {
					$listOfUser[] = $cl['clid'];
				}
			}
			$this->config['functions_welcome_messege']['command_bot'] = true;
			$nowi = array_diff($listOfUser, self::$welcome_messege_list);
			if(!empty($nowi)){
				foreach($nowi as $n) {
					$wmtxt = $this->config['functions_welcome_messege']['txt'];
					$clientInfo = self::$tsAdmin->getElement('data', self::$tsAdmin->clientInfo($n));
					$grupy =  explode(',', $clientInfo['client_servergroups']);
					if(in_array($this->config['functions_welcome_messege']['gid'], $grupy)){
						$wmtxt = $this->config['functions_welcome_messege']['txt_new'];
					}
					$search = [
						'%CLIENT_IP%', '%CLIENT_UNIQUE_ID%', '%CLIENT_DATABASE_ID%', '%CLIENT_ID%', '%CLIENT_CREATED%', '%CLIENT_COUNTRY%', '%CLIENT_VERSION%', '%CLIENT_PLATFORM%', '%CLIENT_NICKNAME%', '%CLIENT_TOTALCONNECTIONS%', '%CLIENT_LASTCONNECTED%', '%CLIENTONLINE%', '%MAXCLIENT%', '%HOUR%', '%DATE%'			
					];

					$replace = [
						$clientInfo['connection_client_ip'], $clientInfo['client_unique_identifier'], $clientInfo['client_database_id'], $n, date("H:i d.m.Y", $clientInfo['client_created']), $clientInfo['client_country'], $clientInfo['client_version'], $clientInfo['client_platform'], $clientInfo['client_nickname'], $clientInfo['client_totalconnections'], date("H:i d.m.Y",$clientInfo['client_lastconnected']), $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'], $this->serverinfo['virtualserver_maxclients'], date('H:i'), date('d.m.Y')
					];

					$wmtxt = str_replace($search, $replace, $wmtxt);
					self::$tsAdmin->sendMessage(1, $n, $wmtxt);
				}
				self::$welcome_messege_list = $listOfUser;
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
		private function wyswietl_czas($data, $d=0, $h=0, $i=0, $s=0, $t=0): string
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
