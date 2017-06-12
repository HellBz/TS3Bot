<?php

	class funkcje{

		private $clientlist = null;
		private $serverinfo = null;
		private $config = null;

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

		public function admins_ts_online(): void
		{
			global $tsAdmin, $admin_list_online, $l, $time_edition;
			$admin_list_online[0] = $admin_list_online[0] ?? NULL;
			$admin_list_online[1]  = NULL;
			$ranga = $l->heading_admins_ts_online;
			foreach($this->config['functions_admins_ts_online']['gid'] as $klucz => $value) {
				$servergroupclientlist =  $tsAdmin->getElement('data', $tsAdmin->serverGroupClientList($klucz, "-names"));
				$servergroupclientlistarray_filter = array_filter($servergroupclientlist[0]);
				if(!empty($servergroupclientlistarray_filter)){
					$ranga .= $l->sprintf($l->group_admins_ts_online, $value);
					foreach($servergroupclientlist as $sgcl){
						foreach($this->clientlist as $cl){
						if($sgcl['cldbid'] == $cl['client_database_id']){
								$channelinfo = $tsAdmin->getElement('data', $tsAdmin->channelInfo($cl['cid']));
								$online = true;
								$channel = $l->sprintf($l->channel_admins_ts_online, $cl['cid'], $channelinfo['channel_name']);
								$nick = $l->sprintf($l->nick_admins_ts_online, $cl['client_database_id'], $cl['client_unique_identifier'], $cl['client_nickname']);
								break;
							}else{
								$online = false;
							}
						}
						if($online == true){
							$ranga .= $l->sprintf($l->admins_ts_online, $nick, $channel);
							$admin_list_online[1] .= $ranga;
						}else{
							$clientdbinfo = $tsAdmin->getElement('data', $tsAdmin->clientDbInfo($sgcl['cldbid']));
							$data = $this->przelicz_czas(time()-$clientdbinfo['client_lastconnected']);
							$txt_time = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
							$nick = $l->sprintf($l->nick_admins_ts_online, $clientdbinfo['client_database_id'], $clientdbinfo['client_unique_identifier'], $clientdbinfo['client_nickname']);
							$ranga .= $l->sprintf($l->admins_ts_offline, $nick, $txt_time);
						}
					}
					$ranga .= $l->size_admins_ts_online;
				}
			}
			if($admin_list_online[0] != $admin_list_online[1] || $time_edition+60 < time()){
				$tsAdmin->channelEdit($this->config['functions_admins_ts_online']['cid'], array('channel_description' => $ranga));
				$admin_list_online[0] = $admin_list_online[1];
				$time_edition = time()+60;
			}
		}

		public function aktualna_data(): void
		{
			global $tsAdmin, $data2, $l;
			$data = date($this->config['functions_aktualna_data']['format']);
			if($data != $data2){
				$tsAdmin->channelEdit($this->config['functions_aktualna_data']['cid'], array('channel_name' => $l->sprintf($l->success_size_admins_ts_online, $data)));
				$data2 = $data;
			}
		}

		public function aktualnie_online(): void
		{
			global $tsAdmin, $config, $online, $l;
			$count = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
			if($online != $count){
				$tsAdmin->channelEdit($this->config['functions_aktualnie_online']['cid'], array('channel_name' => $l->sprintf($l->success_aktualnie_online, $count, $this->padding_numbers($count, 'osoba', 'osoby', 'osób'))));
				$online = $count;
			}
		}

		public function anty_vpn(): void
		{
			global $tsAdmin, $l, $stare_online_anty_vpn;
			if(empty($stare_online_anty_vpn)){
				$stare_online_anty_vpn = array();
			}
			foreach($this->clientlist as $cl){
				$aktualnie_online[$cl['clid']] = $cl['connection_client_ip'];
			}
			$array_diff = array_diff($aktualnie_online, $stare_online_anty_vpn);
			if(!empty($array_diff)){
				foreach($array_diff as $key => $value){
					if(!empty($value)){
						$jdc = json_decode(file_get_contents('http://legacy.iphub.info/api.php?showtype=4&ip='.$value));
						if($jdc->proxy == 1){
							$clientInfo = $tsAdmin->getElement('data', $tsAdmin->clientInfo($key));
							$tsAdmin->clientKick($key, 'server', $l->success_kick_anty_vpn);
							$this->log('Wyrzucono (client_nickname: '.$clientInfo['client_nickname'].') za używanie VPN.');
							unset($aktualnie_online[$key]);
						}
					}
				}
				$stare_online_anty_vpn = $aktualnie_online;
			}
		}
		
		public function clean_channel(): void
		{
			global $tsAdmin, $db;
			$i = 0;
			$channellist = $tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
			foreach($channellist as $cl){
				if($cl['pid'] == $this->config['functions_clean_channel']['pid']){
					$i++;
					if($cl['channel_topic'] != 'WOLNY' && $cl['channel_topic'] != date('d.m.Y')){
						if(!empty($tsAdmin->getElement('data', $tsAdmin->channelClientList($cl['cid'])))){
							$tsAdmin->channelEdit($cl['cid'], array('channel_topic' => date('d.m.Y')));
						}else{
							foreach($channellist as $cl2){
								if($cl2['pid'] == $cl['cid'] && !empty($tsAdmin->getElement('data', $tsAdmin->channelClientList($cl2['cid'])))){
									$tsAdmin->channelEdit($cl['cid'], array('channel_topic' => date('d.m.Y')));
								}
							}
						}
						$czas_del = time()-604800;
						$czas = strtotime($cl['channel_topic']);
						if($czas <= $czas_del){
							$db->query("DELETE FROM `channel` WHERE `cid` = {$cl['cid']}");
							$data = array('channel_name' => $i.'. WOLNY', 'channel_topic' => 'WOLNY', 'channel_description' => '', 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxclients_unlimited' => 0, 'channel_maxclients' => '0', 'channel_maxfamilyclients' => '0');
							$tsAdmin->channelEdit($cl['cid'], $data);
							foreach($channellist as $cl3){
								if($cl3['pid'] == $cl['cid']){
									$tsAdmin->channelDelete($cl3['cid']);
								}
							}
							$channelgroupclientlist = $tsAdmin->getElement('data', $tsAdmin->channelGroupClientList($cl['cid']));
							if(!empty($channelgroupclientlist)){
								foreach($channelgroupclientlist as $cgcl){
									$tsAdmin->setClientChannelGroup(8, $cl['cid'], $cgcl['cldbid']);
								}
							}
							$this->log('Usunięcie kanału za brak aktywności (channel name: '.$cl['channel_name'].')');
						}
					}
				}
			}
		}

		private function cenzor($txt, $add): bool
		{
			$i = 0;
			$cenzor = array('bit(h|ch)', '(ch|h)(w|.w)(d|.d)(p|.p)', '(|o)cip', '(|o)(ch|h)uj(|a)', '(|do|na|po|do|prze|przy|roz|u|w|wy|za|z|matkojeb)jeb(|a|c|i|n|y)', '(|do|na|naw|od|pod|po|prze|przy|roz|spie|roz|poroz|s|u|w|za|wy)pierd(a|o)', 'fu(ck|k)', '/[^.]+\.[^.]+$/', "/^(\"|').+?\\1$/", '(|po|s|w|za)(ku|q)rw(i|y)', 'k(у|u)rw', 'k(у|u)tas', '(|po|wy)rucha', 'motherfucker', 'piczk', '(|w)pi(z|z)d');
			if($add == 1){
				$cenzor = array_merge($this->config['functions_sprnick']['slowa'], $cenzor);
			}
			foreach($cenzor as $c) {
				if(preg_match('~'.$c.'~s', strtolower($txt))){
					$i++;
				}
			}
			if($i > 0){
				return true;
			}else{
				return false;
			}
		}
		
		public function channelCreate(): void
		{
			global $tsAdmin, $db, $l;
			$channelClientList = $tsAdmin->getElement('data', $tsAdmin->channelClientList($this->config['functions_channelCreate']['cid'], "-ip"));
			if(!empty($channelClientList)){
			foreach($channelClientList as $ccl){
				$spr_czy_ma_kanal = $db->query("SELECT COUNT(id) AS `count`, `cid` FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']} OR `connection_client_ip` = '{$ccl['connection_client_ip']}'");
				while($scmk = $spr_czy_ma_kanal->fetch()){
						if($scmk['count'] != 0 && $scmk['cid'] != 0){
							$channelInfo = $tsAdmin->channelInfo($scmk['cid']);
							if(empty($channelInfo['errors'])){
								if($channelInfo['data']['channel_topic'] == 'WOLNY'){
									$count = 0;
									$db->query("DELETE FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']}");
								}else{
									$count = 1;
								}
							}else{
								$count = 0;
								$db->query("DELETE FROM `channel` WHERE `cldbid` = {$ccl['client_database_id']}");
							}
						}else{
							$count = 0;
						}
					}
					if($count == 0){
						$zalozony = 0;
						$id = 1;
						$channellist = $tsAdmin->getElement('data', $tsAdmin->channelList('-topic'));
						foreach($channellist as $chl){
							if($chl['pid'] == $this->config['functions_channelCreate']['pid']){
								$id++;
								$editid = $id-1;
								if(trim($chl['channel_topic']) == 'WOLNY'){
									$data1 = array(
									'channel_name' => $editid.'. '.$ccl['client_nickname'],
									'channel_topic' => date('d.m.Y'),
									'channel_description' => '',
									'channel_flag_maxfamilyclients_unlimited' => 1,
									'channel_flag_maxclients_unlimited' => 1,
									'channel_maxclients' => '-1',
									'channel_maxfamilyclients' => '-1');
									$tsAdmin->channelEdit($chl['cid'], $data1);
									if($this->config['functions_channelCreate']['ile'] != 0){
										for($isub = 1; $isub <= $this->config['functions_channelCreate']['ile']; $isub++){
											$data = array('cpid' => $chl['cid'], 'channel_name' => $isub, 'channel_flag_permanent' => 1, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 1, 'channel_maxclients' => '-1', 'channel_maxfamilyclients' => '-1', 'channel_topic' => '');
											$tsAdmin->channelCreate($data);
										}
									}
									$tsAdmin->clientMove($ccl['clid'], $chl['cid']);
									$tsAdmin->setClientChannelGroup(5, $chl['cid'], $ccl['client_database_id']);
									$db->query("INSERT INTO `channel` VALUES (NULL, {$ccl['client_database_id']}, {$chl['cid']}, '{$ccl['connection_client_ip']}')");
									$this->log('Założono kanał dla (nick name: '.$ccl['client_nickname'].')');
									$zalozony = 1;
									break;
								}
							}
						}
						if($zalozony == 0){
							$data = array('cpid' => $this->config['functions_channelCreate']['pid'], 'channel_name' => $id.'. '.$ccl['client_nickname'], 'channel_flag_permanent' => 1, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 1, 'channel_maxclients' => '-1', 'channel_maxfamilyclients' => '-1', 'channel_topic' => date('d.m.Y'));
							$channelCreate = $tsAdmin->channelCreate($data);
							if($this->config['functions_channelCreate']['ile'] != 0){
								for($isub = 1; $isub <= $this->config['functions_channelCreate']['ile']; $isub++){
									$data = array('cpid' => $channelCreate['cid'], 'channel_name' => $isub, 'channel_flag_permanent' => 1, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 1, 'channel_maxclients' => '-1', 'channel_maxfamilyclients' => '-1', 'channel_topic' => '');
									$tsAdmin->channelCreate($data);
								}
							}
							$tsAdmin->clientMove($ccl['clid'], $channelCreate['data']['cid']);
							$tsAdmin->setClientChannelGroup(5, $channelCreate['data']['cid'], $ccl['client_database_id']);
							$db->query("INSERT INTO `channel` VALUES (NULL, {$ccl['client_database_id']}, {$channelCreate['data']['cid']}, '{$ccl['connection_client_ip']}')");
							$this->log('Założono kanał dla nick name: '.$ccl['client_nickname']);
						}
					}else{
						$tsAdmin->clientMove($ccl['clid'], $this->config['functions_channelCreate']['cid_move']);
						$tsAdmin->clientPoke($ccl['clid'], $l->error_has_a_channel_channelCreate);
					}
				}
			}
		}

		public function ChannelNumber(): void
		{
			global $tsAdmin, $ChannelNumberTime;
			$ChannelNumberTime = $ChannelNumberTime ?? 0;
			if($ChannelNumberTime+10 < time()){
				$i = 0;
				$channellist = $tsAdmin->getElement('data', $tsAdmin->channelList('-topic'));
				foreach($channellist as $chl){
					if($chl['pid'] == $this->config['functions_ChannelNumber']['pid']){
						$i++;
						preg_match_all ("/(\d+)/is", $chl['channel_name'], $matches);
						if(empty($matches[1][0]) || $matches[1][0] != $i){
							$tsAdmin->channelEdit($chl['cid'], ['channel_name' => $i.$this->config['functions_ChannelNumber']['separator'].$chl['channel_name']]);
						}
					}
				}
				$ChannelNumberTime = time()+10;
			}
		}

		public function log($txt): void
		{
			if($this->config['functions_log']['on'] == true){
				$txt = '['.date('H:i:s').'] '.$txt."\n";
				$fp = @fopen('log/'.date('d.m.Y').'_log.log', "a"); 
				flock($fp, 2); 
				fwrite($fp, $txt); 
				flock($fp, 3); 
				fclose($fp);
			}
		}

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
			return $t3 ;
		}

		public function poke(): void
		{
			global $tsAdmin, $l, $czas_administracja, $czas_informacji;
			$administracja_po_poke = array();
			$admin_online = array();
			if(empty($czas_administracja)){
				$czas_administracja = array();
			}else{
				foreach($czas_administracja as $key => $value){
					if($value <= time()){
						unset($czas_administracja[$key]);
					}else{
						$administracja_po_poke[] = $key;
					}
				}
			}
			foreach($this->config['functions_poke']['cid_gid'] as $channel => $value){
				if(empty($czas_informacji[$channel][0])){
					$czas_informacji[$channel][1] = 0;
					$czas_informacji[$channel][0] = 0;
				}
				$channelClientList = $tsAdmin->getElement('data', $tsAdmin->channelClientList($channel, '-groups'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						if(empty(array_intersect(explode(',', $ccl['client_servergroups']), $value))){
							$online_na_kanale[] = $ccl['clid'];
							$nicki[] =  $ccl['client_nickname'];
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
								$tsAdmin->clientPoke($ap, $l->sprintf($l->success_admin_poke, $nicki));
								$czas_administracja[$ap] = time()+$this->config['functions_poke']['admin_time'];
							}
							if($czas_informacji[$channel][0] == 0){
								foreach($online_na_kanale as $onk){
									$tsAdmin->sendMessage(1, $onk, "Administrator został powiadomiony");
								}
								$czas_informacji[$channel][0] = 1;
								$czas_informacji[$channel][1] = time()+$this->config['functions_poke']['user_time'];
							}	
						}else{
							if($czas_informacji[$channel][0] == 0){
								foreach($online_na_kanale as $onk){
									$tsAdmin->sendMessage(1, $onk, "Aktualnie nie ma dostępnej administracji");
								}
								$czas_informacji[$channel][0] = 1;
								$czas_informacji[$channel][1] = time()+$this->config['functions_poke']['user_time'];
							}
						}
					}else{
						foreach($admin_online as $ao){
							$czas_administracja[$ao] = time()+$this->config['functions_poke']['admin_time'];
						}
						$czas_informacji[$channel][0] = 1;
						$czas_informacji[$channel][1] = time()+$this->config['functions_poke']['user_time'];
					}
				}
				if($czas_informacji[$channel][1] <= time()){
					$czas_informacji[$channel][0] = 0;
				}
			}
		}

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
		
		public function register(): void
		{
			global $tsAdmin, $config, $clientlist;
			foreach($this->clientlist as $cl) {
				if($cl['client_type'] == 0) {
					$rangiexplode = explode(',', $cl['client_servergroups']);
					if(!in_array($this->config['functions_register']['gidm'], $rangiexplode) && !in_array($this->config['functions_register']['gidk'], $rangiexplode)){
						if($cl['cid'] == $this->config['functions_register']['cidm']){
								$tsAdmin->serverGroupAddClient($this->config['functions_register']['gidm'], $cl['client_database_id']);
								$this->log('Zarejestrowano nick name: '.$cl['client_nickname']);
						}
						if($cl['cid'] == $this->config['functions_register']['cidk']){
								$tsAdmin->serverGroupAddClient($this->config['functions_register']['gidk'], $cl['client_database_id']);
								$this->log('Zarejestrowano nick name: '.$cl['client_nickname']);
						}
					}
				}
			}
		}

		public function rekord_online(): void
		{
			global $tsAdmin, $l;
			$rekord = file_get_contents('includes/rekord.php');
			$count = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
			if($rekord < $count){
				$tsAdmin->channelEdit($this->config['functions_rekord_online']['cid'], array('channel_name' => $l->sprintf($l->success_rekord_online, $count, $this->padding_numbers($count, 'osoba', 'osoby', 'osób'))));
				file_put_contents('includes/rekord.php', $count);
				$this->log('Ustanowiono rekord osób online: '.$count);
			}
		}
		
		public function servername(): void
		{
			global $tsAdmin, $onlineservername;
			$count = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
			if($onlineservername != $count){
				$tsAdmin->serverEdit(array('virtualserver_name' => str_replace('{1}', $count, $this->config['functions_servername']['name'])));
				$onlineservername = $count;
			}
		}

		public function sprchannel(): void
		{
			global $tsAdmin, $l, $db;
			$channellist = $tsAdmin->getElement('data', $tsAdmin->channelList());
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
						$data = array('channel_name' => $i.'. WOLNY', 'channel_topic' => 'WOLNY', 'channel_description' => '', 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxclients_unlimited' => 0, 'channel_maxclients' => '0', 'channel_maxfamilyclients' => '0');
						$tsAdmin->channelEdit($cl['cid'], $data);
						$channelClientList = $tsAdmin->getElement('data', $tsAdmin->channelClientList($cl['cid']));
						if(!empty($channelClientList)){
							foreach($channelClientList as $ccl){
								$tsAdmin->clientKick($ccl['clid'], 'channel', $l->kick_sprchannel);//lang
							}
						}
						foreach($channellist as $cl3){
							if($cl3['pid'] == $cl['cid']){
								$tsAdmin->channelDelete($cl3['cid']);
							}
						}
						$db->query("DELETE FROM `channel` WHERE `cid` = {$cl['cid']}");
						$this->log('Usunięcie kanału za wulgarną nazwę (channel name: '.$cl['channel_name'].')');
					}
				}
			}
		}

		public function sprnick(): void
		{
			global $tsAdmin, $l;
			foreach($this->clientlist as $cl) {
				if(!array_intersect(explode(',', $cl['client_servergroups']), $this->config['functions_sprnick']['gid'])){
					if($this->cenzor($cl['client_nickname'], 0) == true){
						$tsAdmin->clientPoke($cl['clid'], $l->poke_sprnick);
						$tsAdmin->clientKick($cl['clid'], "server", $l->kick_sprnick);
						$this->log('Wyrzucono użytkownika za wulgarny nick (client unique identifier: '.$cl['client_unique_identifier'].')');
					}
				}
			}
		}
		
		public function update_activity(): void
		{
			global $last_top_przebywania, $db, $tsAdmin;
			if($last_top_przebywania <= time()){
				foreach($this->clientlist as $cl){
					$pobierz_czas_przebywania = $db->query("SELECT COUNT(id) AS `count` FROM `czas_przebywania` WHERE `cldbid` = {$cl['client_database_id']} LIMIT 1");
					while($pcp = $pobierz_czas_przebywania->fetch()){
						$count = $pcp['count'];
					}
					if($count == 0){
					$db->query("INSERT INTO `czas_przebywania` VALUES (NULL, {$cl['client_database_id']}, {$cl['clid']}, '{$cl['client_nickname']}', '{$cl['client_unique_identifier']}', 0)");
					}else{
						if($cl['client_idle_time'] < 300000){
							$db->query("UPDATE `czas_przebywania` SET `time` = time+60, `client_nickname` = '{$cl['client_nickname']}', `clid` = {$cl['clid']} WHERE `cldbid` = {$cl['client_database_id']}");
						}
					}
				}
				$s = 0;
				$top = NULL;
				if(!empty($this->config['functions_update_activity']['cldbid'])){
					$cldbid = implode(",", $this->config['functions_update_activity']['cldbid']);
				}
				$pobierz_top = $db->query("SELECT * FROM `czas_przebywania` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `time` DESC LIMIT 10");
				while($pt = $pobierz_top->fetch()){
					$s++;
					$data = $this->przelicz_czas($pt['time'], 1);
					$data = $this->wyswietl_czas($data, 1, 1, 1, 0, 0);
					$nick = "[B][URL=client://{$pt['cldbid']}/{$pt['cui']}]{$pt['client_nickname']}[/URL][/B]";
					$top .= "[SIZE=10][COLOR=#ff0000][B]{$s}.)[/B][/COLOR] {$nick} {$data}\n[/SIZE]";
				}
				$tsAdmin->channelEdit($this->config['functions_update_activity']['cid'], array('channel_description' => $top));
				$last_top_przebywania = time()+60;
			}
		}

		public function welcome_messege(): void
		{
			global $tsAdmin, $lista2;
			$lista = array();
			if(empty($lista2)){
				$lista2 = array();
			}
			foreach((array)$this->clientlist as $cl) {
				if($cl['client_type'] == 0) {
					$lista[] = $cl['clid'];
				}
			}
			$nowi = array_diff($lista, $lista2);
			if($nowi){
				foreach($nowi as $n) {
					$wmtxt = $this->config['functions_welcome_messege']['txt'];
					$informacje = $tsAdmin->getElement('data', $tsAdmin->clientInfo($n));
					$grupy =  explode(',', $informacje['client_servergroups']);
					if(in_array($this->config['functions_welcome_messege']['gid'], $grupy)){
						$wmtxt = $this->config['functions_welcome_messege']['txt_new'];
					}
					$clientsonline = $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'];
					$clients = $this->serverinfo['virtualserver_maxclients'];
					$wmtxt = str_replace('%CLIENT_IP%', $informacje['connection_client_ip'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_UNIQUE_ID%', $informacje['client_unique_identifier'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_DATABASE_ID%', $informacje['client_database_id'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_ID%', $n, $wmtxt);
					$wmtxt = str_replace('%CLIENT_COUNTRY%', $informacje['client_country'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_VERSION%', $informacje['client_version'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_PLATFORM%', $informacje['client_platform'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_CREATED%', date("d-m-Y H:i", $informacje['client_created']), $wmtxt);
					$wmtxt = str_replace('%CLIENT_NICKNAME%', $informacje['client_nickname'], $wmtxt);
					$wmtxt = str_replace('%CLIENT_LASTCONNECTED%', date("d-m-Y H:i",$informacje['client_lastconnected']), $wmtxt);
					$wmtxt = str_replace('%CLIENT_TOTALCONNECTIONS%', $informacje['client_totalconnections'], $wmtxt);
					$wmtxt = str_replace('%CLIENTONLINE%', $this->serverinfo['virtualserver_clientsonline'] - $this->serverinfo['virtualserver_queryclientsonline'], $wmtxt);
					$wmtxt = str_replace('%MAXCLIENT%', $this->serverinfo['virtualserver_maxclients'], $wmtxt);
					$tsAdmin->sendMessage(1, $n, $wmtxt);
				}
				$lista2 = $lista;
			}
		}
		
		private function wyswietl_czas($data, $d=0, $h=0, $i=0, $s=0, $t=0):string
		{
			global $l;
			$txt_time = null;
			if($d==1){
				if($data['d'] == 0){
					$txt_time .= '';
				}else{
					$l->time_d1_wyswietl_czas = $this->padding_numbers($data['d'], $l->time_d1_wyswietl_czas, $l->time_d2_wyswietl_czas, $l->time_d2_wyswietl_czas);
					$txt_time .= $data['d'].' '.$l->time_d1_wyswietl_czas.' ';
				}
			}else{
				$data['d'] = 0;
			}
			if($h==1){
				if($data['d'] == 0 && $data['H'] == 0){
					$txt_time .= '';
				}else{
					$l->time_h1_wyswietl_czas = $this->padding_numbers($data['H'], $l->time_h1_wyswietl_czas, $l->time_h2_wyswietl_czas, $l->time_h3_wyswietl_czas);
					$txt_time .= $data['H'].' '.$l->time_h1_wyswietl_czas.' ';
				}
			}
			else{
				$data['H'] = 0;
			}
			if($i==1){
				if($data['d'] == 0 && $data['H'] == 0 && $data['i'] == 0){
					$txt_time .= '';
				}else{
					$l->time_i1_wyswietl_czas = $this->padding_numbers($data['i'], $l->time_i1_wyswietl_czas, $l->time_i2_wyswietl_czas, $l->time_i3_wyswietl_czas);
					$txt_time .= $data['i'].' '.$l->time_i1_wyswietl_czas.' ';
				}
			}else{
				$data['i'] = 0;
			}
			if($s==1){
				if($data['d'] == 0 && $data['s'] == 0 && $data['i'] == 0 && $data['H'] == 0){
					if($t == 0){
						$txt_time = '0 '.$l->time_s2_wyswietl_czas;
					}else{
						$txt_time = 'TERAZ';
					}
				}else{
					$l->time_s1_wyswietl_czas = $this->padding_numbers($data['s'], $l->time_s1_wyswietl_czas, $l->time_s2_wyswietl_czas, $l->time_s3_wyswietl_czas);
					$txt_time .= $data['s'].' '.$l->time_s1_wyswietl_czas;
				}
			}
			return $txt_time;
		}
	}
?>
