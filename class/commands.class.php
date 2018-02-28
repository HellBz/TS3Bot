<?php

	class Commands extends Functions{

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
		
		private static $user_moveAfk = [];

		private static $olde_user_list = 1;

		private static $servername_online = 0;

		private static $statusTwitch_time = 0;

		private static $statusYt_time = 0;

		private static $statusYt_description = [];

		private static $top_activity_time = 0;

		private static $description_top_connections  = NULL;
		
		private static $edit_top_connections = 0;

		private static $description_top_longest_connection = NULL;

		private static $edit_top_longest_connection = 0;


		/**
		 * addRank()
		 * Funkcja dodaje range po wejściu na kanało o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function addRank(): void
		{
			foreach($this->config['functions_addRank']['cid_gid'] as $klucz => $value) {
				$channelClientList = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelClientList($klucz, '-groups'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						$explode = explode(',', $ccl['client_servergroups']);
						if(!in_array($value, $explode)){
							Functions::$tsAdmin->serverGroupAddClient($value, $ccl['client_database_id']);
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
				Functions::$tsAdmin->channelEdit($this->config['functions_aktualna_data']['cid'], array('channel_name' => Functions::$l->sprintf(Functions::$l->success_size_admins_ts_online, $data)));
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
				Functions::$tsAdmin->channelEdit($this->config['functions_aktualnie_online']['cid'], array('channel_name' => Functions::$l->sprintf(Functions::$l->success_aktualnie_online, $count, $this->padding_numbers($count, 'osoba', 'osoby', 'osób'))));
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
						$check = 0;
						$proxy = 0;
						$query = Functions::$db->query("SELECT COUNT(id) as `count`, `proxy`, `time` FROM `ip` WHERE `ip` = '{$value}'");
						while($row = $query->fetch()){
							$count = $row['count'];
							if($count != 0){
								if($row['proxy'] == 3 || $row['time']+2592000 < time()){
									$check = 1;
								}
							}else{
								$check = 1;
							}
						}
						if($check == 1){
							$ch = curl_init();
							curl_setopt_array($ch, [
								CURLOPT_URL => "http://v2.api.iphub.info/ip/{$value}",
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_HTTPHEADER => ["X-Key: {$this->config['functions_anty_vpn']['key']}"]
							]);
							$data = json_decode(curl_exec($ch));
							if(isset($data->block)){;
								$proxy = $data->block;
							}else{
								$proxy = 3;
							}
							if($count == 0){
								Functions::$db->query("INSERT INTO `ip` VALUES (NULL, '{$value}', {$proxy}, ".time().")");
							}else{
								Functions::$db->query("UPDATE `ip` SET `proxy` = {$proxy}, time = ".time()." WHERE `ip` = '{$value}'");
							}
						}
						if($proxy == 1){
							$clientInfo = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->clientInfo($key));
							Functions::$tsAdmin->clientKick($key, 'server', Functions::$l->success_kick_anty_vpn);
							$this->log(2, 'Wyrzucono (client_nickname: '.$clientInfo['client_nickname'].') za używanie VPN.');
							unset($aktualnie_online[$key]);
						}
					}
				}
				self::$online_anty_vpn = $aktualnie_online;
			}
		}

		/**
		 * cleanChannel()
		 * Funkcja czyści kanały, które nie są aktywne dłużej niż 7 dni w podanym sektorze.
		 * @author	Majcon
		 * @return	void
		 **/
		public function cleanChannel(): void
		{
			$channellist = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelList("-topic -flags -voice -limits -icon"));
			$i = 0;
			foreach($channellist as $cl){
				if($cl['pid'] == $this->config['functions_cleanChannel']['pid']){
					$i++;
					if($cl['channel_topic'] != 'WOLNY' && $cl['channel_topic'] != date('d.m.Y')){
						if(!empty(Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelClientList($cl['cid'])))){
							Functions::$tsAdmin->channelEdit($cl['cid'], array('channel_topic' => date('d.m.Y')));
						}else{
							foreach($channellist as $cl2){
								if($cl2['pid'] == $cl['cid'] && !empty(Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelClientList($cl2['cid'])))){
									Functions::$tsAdmin->channelEdit($cl['cid'], array('channel_topic' => date('d.m.Y')));
								}
							}
						}
						$czas_del = time()-$this->config['functions_cleanChannel']['time']*86400;
						$czas = strtotime($cl['channel_topic']);
						if($czas <= $czas_del){
							$this->channelDelete($i, $cl['cid']);
							$this->log(2, 'Usunięcie kanału za brak aktywności (channel name: '.$cl['channel_name'].')');
						}
					}
				}
			}
		}

		/**
		 * channelCreate()
		 * Funkcja zakłada kanały w podanym sektorze.
		 * @author	Majcon
		 * @return	void
		 **/
		public function channelCreate(): void
		{
			$channelClientList = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelClientList($this->config['functions_channelCreate']['cid'], "-ip -uid"));
			if(!empty($channelClientList)){
				foreach($channelClientList as $ccl){
					$spr_czy_ma_kanal = Functions::$db->query("SELECT COUNT(id) AS `count`, `cid` FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']} OR `connection_client_ip` = '{$ccl['connection_client_ip']}'");
					while($scmk = $spr_czy_ma_kanal->fetch()){
						if($scmk['count'] != 0 && $scmk['cid'] != 0){
							$channelInfo = Functions::$tsAdmin->channelInfo($scmk['cid']);
							if(empty($channelInfo['errors'])){
								if($channelInfo['data']['channel_topic'] == 'WOLNY'){
									$count = 0;
									Functions::$db->query("DELETE FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']}");
								}else{
									$cid = $scmk['cid'];
									$count = 1;
								}
							}else{
								$count = 0;
								Functions::$db->query("DELETE FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']}");
							}
						}else{
							$count = 0;
						}
					}
					if($count == 0){
						$zalozony = 0;
						$id = 1;
						$search = [ '%CLIENT_NICKNAME%', '%HOUR%', '%DATE%'	];
						$replace = [ $this->getUrlName($ccl['client_database_id'], $ccl['client_unique_identifier'], $ccl['client_nickname']), date('H:i'), date('d.m.Y') ];
						$channellist = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelList('-topic'));
						foreach($channellist as $chl){
							if($chl['pid'] == $this->config['functions_channelCreate']['pid']){
								$id++;
								$editid = $id-1;
								if(trim($chl['channel_topic']) == 'WOLNY'){
									$data1 = [
										'channel_name' => $editid.'. '.$ccl['client_nickname'],
										'channel_topic' => date('d.m.Y'),
										'channel_description' => str_replace($search, $replace, $this->config['functions_channelCreate']['channel_description']),
									];
									$data1 = array_merge($data1, $this->config['functions_channelCreate']['setting']);
									Functions::$tsAdmin->channelEdit($chl['cid'], $data1);
									if($this->config['functions_channelCreate']['ile'] != 0){
										for($isub = 1; $isub <= $this->config['functions_channelCreate']['ile']; $isub++){
											$data = [ 
												'cpid' => $chl['cid'], 'channel_name' => $isub,
											];
											$data = array_merge($data, $this->config['functions_channelCreate']['setting_subchannel']);
											Functions::$tsAdmin->channelCreate($data);
										}
									}
									Functions::$tsAdmin->clientMove($ccl['clid'], $chl['cid']);
									Functions::$tsAdmin->setClientChannelGroup($this->config['functions_channelCreate']['gid'], $chl['cid'], $ccl['client_database_id']);
									Functions::$db->query("INSERT INTO `channel` VALUES (NULL, {$ccl['client_database_id']}, {$chl['cid']}, '{$ccl['connection_client_ip']}')");
									$this->log(2, 'Założono kanał dla (nick name: '.$ccl['client_nickname'].')');
									$zalozony = 1;
									break;
								}
							}
						}
						if($zalozony == 0){
							$data1 = [
								'cpid' 						=> $this->config['functions_channelCreate']['pid'],
								'channel_name'				=> $id.'. '.$ccl['client_nickname'],
								'channel_description' 		=> str_replace($search, $replace, $this->config['functions_channelCreate']['channel_description']),
								'channel_topic' 			=> date('d.m.Y'),
							];
						$data1 = array_merge($data1, $this->config['functions_channelCreate']['setting']);
						$channelCreate = Functions::$tsAdmin->channelCreate($data1);
						if($this->config['functions_channelCreate']['ile'] != 0){
							for($isub = 1; $isub <= $this->config['functions_channelCreate']['ile']; $isub++){
								$data = [
									'cpid' => $channelCreate['data']['cid'],
									'channel_name' => $isub, 
								];
								$data = array_merge($data, $this->config['functions_channelCreate']['setting_subchannel']);
								Functions::$tsAdmin->channelCreate($data);
							}
						}
							Functions::$tsAdmin->clientMove($ccl['clid'], $channelCreate['data']['cid']);
							Functions::$tsAdmin->setClientChannelGroup($this->config['functions_channelCreate']['gid'], $channelCreate['data']['cid'], $ccl['client_database_id']);
							Functions::$db->query("INSERT INTO `channel` VALUES (NULL, {$ccl['client_database_id']}, {$channelCreate['data']['cid']}, '{$ccl['connection_client_ip']}')");
							$this->log(2, 'Założono kanał dla nick name: '.$ccl['client_nickname']);
						}
					}else{
						Functions::$tsAdmin->clientMove($ccl['clid'], $cid);
						Functions::$tsAdmin->clientPoke($ccl['clid'], Functions::$l->error_has_a_channel_channelCreate);
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
			if(self::$channelNumberTime < time()){
				$i = 0;
				$channellist = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelList('-topic'));
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
								Functions::$tsAdmin->channelEdit($chl['cid'], ['channel_name' => $i.$this->config['functions_channelNumber']['separator'].$matches[2][0]]);
							}
						}else{
							Functions::$tsAdmin->channelEdit($chl['cid'], ['channel_name' => $i.$this->config['functions_channelNumber']['separator'].$chl['channel_name']]);

						}
					}
				}
				self::$channelNumberTime = time()+10;
			}
		}

		/**
		 * delRank()
		 * Funkcja usuwa range po wejściu na kanało o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function delRank(): void
		{
			foreach($this->config['functions_delRank']['cid_gid'] as $klucz => $value) {
				$channelClientList = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelClientList($klucz, '-groups'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						$explode = explode(',', $ccl['client_servergroups']);
						if(in_array($value, $explode)){
							Functions::$tsAdmin->serverGroupDeleteClient($value, $ccl['client_database_id']);
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
					$serverGroupClientList = Functions::$tsAdmin->serverGroupClientList($gid, '-names');
					$serverGroupClientListarray_filter = array_filter($serverGroupClientList['data'][0] ?? []);
					if(!empty($serverGroupClientListarray_filter)){
						$channel_description .= $name;
						foreach($serverGroupClientList['data'] as $sgcl){
							$i_all++;
							foreach($this->clientlist as $cl){
								if($sgcl['cldbid'] == $cl['client_database_id']){
									$online = true;
									$i_online++;
									$channelinfo = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelInfo($cl['cid']));
									$channel = $this->getUrlChannel($cl['cid'], $channelinfo['channel_name']);
									$nick = $this->getUrlName($cl['client_database_id'], $cl['client_unique_identifier'], $cl['client_nickname']);
									$channel_description .= Functions::$l->sprintf(Functions::$l->groupOnline_online, $nick, $channel);
									$groupOnline .= $nick.$channel;
									break;
								}else{
									$online = false;
								}
							}
							if($online == false){
								$query = Functions::$db->query("SELECT COUNT(id) AS `count`, `last_activity` FROM `users` WHERE `cldbid` = {$sgcl['cldbid']} LIMIT 1");
								while($row = $query->fetch()){
									if($row['count'] != 0){
										$last_activity = $row['last_activity'];
									}else{
										$last_activity = 0;
									}
								}
								$clientdbinfo = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->clientDbInfo($sgcl['cldbid']));
								$data = $this->przelicz_czas(time()-$last_activity);
								$txt_time = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
								$nick = $this->getUrlName($clientdbinfo['client_database_id'], $clientdbinfo['client_unique_identifier'], $clientdbinfo['client_nickname']);
								$channel_description .= Functions::$l->sprintf(Functions::$l->groupOnline_offline, $nick, $txt_time);
								$groupOnline .= $nick;
							}
						}
					}
				}
				if(self::$older_groupOnline[$cid] != $groupOnline || self::$groupOnline_time_edition < time()){
					$data['channel_description'] = $channel_description;
					$groupOnline_name = Functions::$l->sprintf($value['channel_name'], $i_online, $i_all);
					if($value['name_online'] == true && self::$older_groupOnline_name[$cid] != $groupOnline_name){
						self::$older_groupOnline_name[$cid] = $groupOnline_name;
						$data['channel_name'] = $groupOnline_name;
					}
					Functions::$tsAdmin->channelEdit($cid, $data);
					self::$older_groupOnline[$cid] = $groupOnline;
					self::$groupOnline_time_edition = time()+60;
				}
			}
		}

		/**
		 * moveAfk()
		 * Funkcja przenosi nieaktywne osoby na kanał o podanym ID.
		 * @author	Majcon
		 * @return	void
		 **/
		public function moveAfk(): void
		{
			foreach($this->clientlist as $cl){
				if($cl['client_type'] == 0){
					if(($this->config['functions_moveAfk']['input_muted'] == 1 && $cl['client_input_muted'] == 1) || ($this->config['functions_moveAfk']['output_muted'] == 1 && $cl['client_output_muted'] == 1) || ($this->config['functions_moveAfk']['away'] == 1 && $cl['client_away'] == 1) || ($this->config['functions_moveAfk']['idle'] == 1 && $cl['client_idle_time'] >=  $this->config['functions_moveAfk']['idle_time']*1000)){
						if($cl['cid'] != $this->config['functions_moveAfk']['cid'] && !array_intersect(explode(',', $cl['client_servergroups']), $this->config['functions_moveAfk']['gid']) && !in_array($cl['cid'], $this->config['functions_moveAfk']['cidaa'])){
							self::$user_moveAfk[$cl['client_database_id']] = $cl['cid'];
							Functions::$tsAdmin->clientMove($cl['clid'], $this->config['functions_moveAfk']['cid']);
						}
					}else{
						if($cl['cid'] == $this->config['functions_moveAfk']['cid'] && !array_intersect(explode(',', $cl['client_servergroups']), $this->config['functions_moveAfk']['gid'])){
							Functions::$tsAdmin->clientMove($cl['clid'], self::$user_moveAfk[$cl['client_database_id']] ?? $this->config['functions_moveAfk']['default_channel']);
						}
					}
				}
			}
		}

		/**
		 * newUser()
		 * Funkcja dodaje nowych użytkowników do opisu.
		 * @author	Majcon
		 * @return	void
		 **/
		public function newUser(): void
		{
			$time = time() - $this->config['functions_newUser']['time'];
			$i = 0;
			$query = Functions::$db->query("SELECT `client_nickname`, `cui`, `cldbid` FROM `users` WHERE `regdate` >= {$time} ORDER BY `regdate` DESC");
			while($row = $query->fetch()){
				$i++;
				$user_list[] = $this->getUrlName($row['cldbid'], $row['cui'], $row['client_nickname']);
			}
			$user_list = implode(', ', $user_list ?? []);
			if($user_list != self::$olde_user_list){
				Functions::$tsAdmin->channelEdit($this->config['functions_newUser']['cid'], ['channel_name' => Functions::$l->sprintf(Functions::$l->newUser_name, $i), 'channel_description' => Functions::$l->sprintf(Functions::$l->newUser_title, $user_list) ]);
				self::$olde_user_list = $user_list;
			}
		}

		/**
		 * poke()
		 * Funkcja puka podane grupy jeżeli ktoś wbije na podany kanał.
		 * @author	Majcon
		 * @return	void
		 **/
		public function poke(): void
		{
			$administracja_po_poke = [];
			$admin_online = [];
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
				$channelClientList = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelClientList($channel, '-groups -uid'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						if(empty(array_intersect(explode(',', $ccl['client_servergroups']), $value))){
							$online_na_kanale[] = $ccl['clid'];
							$nicki[] =  $this->getUrlName($ccl['client_database_id'], $ccl['client_unique_identifier'], $ccl['client_nickname']);
						}else{
							$admin_online[] = $ccl['clid'];
						}
					}
					if(empty($admin_online)){
						$lista_adminow = [];
						foreach($this->clientlist as $cl) {
							if(!empty(array_intersect(explode(',', $cl['client_servergroups']), $value))){
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
									Functions::$tsAdmin->clientPoke($ap, Functions::$l->sprintf(Functions::$l->success_admin_poke, $nicki));
								}else{
									Functions::$tsAdmin->sendMessage(1, $ap, Functions::$l->sprintf(Functions::$l->success_admin_poke, $nicki));
								}
								self::$czas_administracja_poke[$ap] = time()+$this->config['functions_poke']['admin_time'];
							}
							if(self::$czas_informacji_poke[$channel][0] == 0){
								foreach($online_na_kanale as $onk){
									Functions::$tsAdmin->sendMessage(1, $onk, Functions::$l->success_he_was_informed_poke);
								}
								self::$czas_informacji_poke[$channel][0] = 1;
								self::$czas_informacji_poke[$channel][1] = time()+$this->config['functions_poke']['user_time'];
							}	
						}else{
							if(self::$czas_informacji_poke[$channel][0] == 0){
								foreach($online_na_kanale as $onk){
									Functions::$tsAdmin->sendMessage(1, $onk, Functions::$l->error_admin_offline_poke);
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
								Functions::$tsAdmin->serverGroupAddClient($this->config['functions_register']['gidm'], $cl['client_database_id']);
								$this->log(2, 'Zarejestrowano nick name: '.$cl['client_nickname']);
						}
						if($cl['cid'] == $this->config['functions_register']['cidk']){
								Functions::$tsAdmin->serverGroupAddClient($this->config['functions_register']['gidk'], $cl['client_database_id']);
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
				Functions::$tsAdmin->channelEdit($this->config['functions_rekord_online']['cid'], array('channel_name' => Functions::$l->sprintf(Functions::$l->success_rekord_online, $count, $this->padding_numbers($count, 'osoba', 'osoby', 'osób'))));
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
					Functions::$tsAdmin->sendMessage(3, 1, $txt);
				}else{
					foreach($this->clientlist as $cl) {
						if(array_intersect(explode(',', $cl['client_servergroups']), $group) || $group[0] == 0){
							Functions::$tsAdmin->sendMessage(1, $cl['clid'], $txt);
						}
					}
				}
				self::$sendAd_time = time()+$this->config['functions_sendAd']['time']*60;
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
				Functions::$tsAdmin->serverEdit(array('virtualserver_name' => str_replace('{1}', $count, $this->config['functions_servername']['name'])));
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
			$channellist = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelList());
			$i = 0;
			foreach($channellist as $cl){
				$delete = 0;
				if($cl['pid'] == $this->config['functions_sprchannel']['pid']){
					$i++;
					if($this->cenzor($cl['channel_name'], 0) == true){
						$delete = 1;
					}else{
						$is = 0;
						foreach($channellist as $cl2){
							if($cl2['pid'] == $cl['cid']){
								$is++;
								if($this->cenzor($cl2['channel_name'], 0) == true){
									$delete = 1;
									$sub[$is] = $cl2['cid'];
								}
							}
						}
					}
					if($delete == 1){
						if($this->config['functions_sprchannel']['setting'] == 0){
							if(!empty($sub)){
								foreach($sub as $key => $value){
									Functions::$tsAdmin->channelEdit($value, array('channel_name' => $key.' '.$this->config['functions_sprchannel']['new_name']));
								}
							}else{
								Functions::$tsAdmin->channelEdit($cl['cid'], array('channel_name' => $i.' .'.$this->config['functions_sprchannel']['new_name']));
								$this->log(2, 'Wyedytowano kanał za wulgarną nazwę: (channel name: '.$cl['channel_name'].') (channel id: '.$cl['cid'].')');
							}
						}else{
							$this->channelDelete($i, $cl['cid']);
							$this->log(2, 'Usunięcie kanału za wulgarną nazwę (channel name: '.$cl['channel_name'].') (channel id: '.$cl['cid'].')');
						}
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
						Functions::$tsAdmin->clientPoke($cl['clid'], Functions::$l->poke_sprnick);
						Functions::$tsAdmin->clientKick($cl['clid'], "server", Functions::$l->kick_sprnick);
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
					$jdc = json_decode($this->file_get_contents_curl('https://api.twitch.tv/kraken/streams/'.$name.'?client_id=56o6gfj3nakgeaaqpku3cugkf7lgzk'));
					if($jdc->stream == null){
						$jdc2 = json_decode($this->file_get_contents_curl('https://api.twitch.tv/kraken/users/'.$name.'?client_id=56o6gfj3nakgeaaqpku3cugkf7lgzk'));
						$channel_description = Functions::$l->sprintf(Functions::$l->offline_statusTwitch, $name, $jdc2->logo);
						$channelinfo = Functions::$tsAdmin->getElement('data', Functions::$tsAdmin->channelInfo($cid));
						if($channelinfo['channel_description'] != $channel_description){
							Functions::$tsAdmin->channelEdit($cid, array('channel_description' => $channel_description));
						}
					}else{
						$channel_description = Functions::$l->sprintf(Functions::$l->online_statusTwitch, $jdc->stream->channel->url, $name, $jdc->stream->game, $jdc->stream->channel->status, $jdc->stream->viewers, $jdc->stream->channel->logo);
						Functions::$tsAdmin->channelEdit($cid, array('channel_description' => $channel_description));
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
					$jdc = json_decode($this->file_get_contents_curl("https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$id}&key={$this->config['functions_statusYt']['key']}"));
					if(!empty($jdc->items[0])){
						$channel_description = Functions::$l->sprintf(Functions::$l->channel_description_statusYt, $jdc->items[0]->id, $jdc->items[0]->snippet->title, $jdc->items[0]->statistics->subscriberCount, $jdc->items[0]->statistics->viewCount, $jdc->items[0]->snippet->description, $jdc->items[0]->snippet->thumbnails->medium->url);
						$channel_name = Functions::$l->sprintf(Functions::$l->channel_name_statusYt, $jdc->items[0]->snippet->title, $jdc->items[0]->statistics->subscriberCount);
						Functions::$tsAdmin->channelEdit($cid, [ 'channel_name' => $channel_name ]);
						if(self::$statusYt_description[$cid] != $channel_description){
							Functions::$tsAdmin->channelEdit($cid, [ 'channel_description' => $channel_description ]);
							self::$statusYt_description[$cid] = $channel_description;
						}
					}
				}
				self::$statusYt_time = time()+60;
			}
		}

		/**
		 * top_activity_time()
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
				$query = Functions::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `time_activity`, `gid` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `time_activity` DESC");
				while($row = $query->fetch()){
					if(!array_intersect(explode(',', $row['gid']), $this->config['functions_top_activity_time']['gid'])){
						$s++;
						$data = $this->przelicz_czas($row['time_activity'], 1);
						$data = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
						$nick = $this->getUrlName($row['cldbid'], $row['cui'], $row['client_nickname']);
						$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$data}\n[/SIZE]";
					}
					if($s >= $this->config['functions_top_activity_time']['limit']){
						break;
					}
				}
				Functions::$tsAdmin->channelEdit($this->config['functions_top_activity_time']['cid'], array('channel_description' => $top));
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
			$query = Functions::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `connections`, `gid` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `connections` DESC LIMIT {$this->config['functions_top_activity_time']['limit']}");
			while($row = $query->fetch()){
				if(!array_intersect(explode(',', $row['gid']), $this->config['functions_top_activity_time']['gid'])){
					$s++;
					$nick = $this->getUrlName($row['cldbid'], $row['cui'], $row['client_nickname']);
					$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$row['connections']}\n[/SIZE]";
				}
				if($s >= $this->config['functions_top_activity_time']['limit']){
					break;
				}
			}
			if($top != self::$description_top_connections){
				Functions::$tsAdmin->channelEdit($this->config['functions_top_connections']['cid'], array('channel_description' => $top));
				self::$description_top_connections = $top;
			}
		}

		/**
		 * top_longest_connection()
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
				$query = Functions::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `longest_connection`, `gid` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `longest_connection` DESC LIMIT {$this->config['functions_top_activity_time']['limit']}");
				while($row = $query->fetch()){
					if(!array_intersect(explode(',', $row['gid']), $this->config['functions_top_activity_time']['gid'])){
						$s++;
						$data = $this->przelicz_czas($row['longest_connection']/1000);
						$data = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
						$nick = $this->getUrlName($row['cldbid'], $row['cui'], $row['client_nickname']);
						$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$data}\n[/SIZE]";
					}
					if($s >= $this->config['functions_top_activity_time']['limit']){
						break;
					}
				}
				if($top != self::$description_top_longest_connection){
					Functions::$tsAdmin->channelEdit($this->config['functions_top_longest_connection']['cid'], array('channel_description' => $top));
					self::$description_top_longest_connection = $top;
				}
				self::$edit_top_longest_connection = time()+300;
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
			$nowi = array_diff($listOfUser, self::$welcome_messege_list);
			if(!empty($nowi)){
				foreach($nowi as $n) {
					$wmtxt = $this->config['functions_welcome_messege']['txt'];
					$clientInfo = self::$tsAdmin->getElement('data', self::$tsAdmin->clientInfo($n));
					$grupy =  explode(',', $clientInfo['client_servergroups']);
					if(in_array($this->config['functions_welcome_messege']['gid'], $grupy)){
						$wmtxt = $this->config['functions_welcome_messege']['txt_new'];
					}
					$data = $this->przelicz_czas($this->serverinfo['virtualserver_uptime']);
					$txt_time = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
					$search = [
						'%CLIENT_IP%', '%CLIENT_UNIQUE_ID%', '%CLIENT_DATABASE_ID%', '%CLIENT_ID%', '%CLIENT_CREATED%', '%CLIENT_COUNTRY%', '%CLIENT_VERSION%', '%CLIENT_PLATFORM%', '%CLIENT_NICKNAME%', '%CLIENT_TOTALCONNECTIONS%', '%CLIENT_LASTCONNECTED%', '%CLIENTONLINE%', '%MAXCLIENT%', '%SERVER_UPTIME%', '%HOUR%', '%DATE%'			
					];
					$replace = [
						$clientInfo['connection_client_ip'], $clientInfo['client_unique_identifier'], $clientInfo['client_database_id'], $n, date("H:i d.m.Y", $clientInfo['client_created']), $clientInfo['client_country'], $clientInfo['client_version'], $clientInfo['client_platform'], $clientInfo['client_nickname'], $clientInfo['client_totalconnections'], date("H:i d.m.Y",$clientInfo['client_lastconnected']), $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'], $this->serverinfo['virtualserver_maxclients'], $txt_time, date('H:i'), date('d.m.Y')
					];
					$wmtxt = str_replace($search, $replace, $wmtxt);
					self::$tsAdmin->sendMessage(1, $n, $wmtxt);
				}
				self::$welcome_messege_list = $listOfUser;
			}
		}
	}
?>
