<?php

	return [

		'server' => [

			'login'		=> 'serveradmin', 									//ServerQuery Login
			'password'	=> '',												//ServerQuery password
			'ip'		=> '127.0.0.1',  									//IP serwera
			'port'		=> 9987, 											//Server port
			'queryport'	=> 10011, 								 			//Query port
			'nick'		=> 'ts3Bot by Majcon'  								//Nick bota na ts

		],
		
		'bot' => [
		
			'ver'		=> '280'											//Wersja bota.	
		
		],

	//addRank() Funkcja dodaje range po wejściu na kanało o podanym ID.
		'functions_addRank' => [

			'on'	=> false,												//true - włączona false - wyłączona
			'cid_gid'	=> [

							1 => 2

							]												//ID kanału, na który trzeba wejść wraz z ID rangi, którą ma nadać po wejściu. Tutaj 1 oraz 3 to ID kanału 2 oraz 4 ID rangi.

		],

	//aktualna_data() Funkcja ustawia aktualną datę jako nazwa kanału o podanym ID.
		'functions_aktualna_data' => [

			'on'		=> false,											//true - włączona false - wyłączona
			'cid'		=> 1,												//ID kanału, na którym ma ustawiać datę.
			'format'	=> 'd.m.Y H:i'										//Format daty d - dzień m - miesiąc Y - rok H - godzina i - minuta s - sekunda

		],

	//aktualnie_online() Funkcja ustawia aktualną liczbę osób online jako nazwa kanału o podanym ID.
		'functions_aktualnie_online' => [

			'on'		=> false,											//true - włączona false - wyłączona
			'cid'		=> 1												//ID kanału, na którym ma ustawiać aktualną liczbę online.

		],

	//anty_vpn() Funkcja wyrzuca użytkowników, którzy posiadają proxy.
		'functions_anty_vpn' => [

			'on'	=> false	,													//true - włączona false - wyłączona
			'client_unique_identifier'	=> [

				'6wZcPZcelLsaW7BBMjfDG+NHVAQ=', '6wZcPZcelLsaW7BBMjfDG+NHVAQ='

			],																	//Unique identifier użytkownika, którego ma nie wyrzucać za VPN.
			'gid'		=> '24,78,157',											//ID Group, które ma nie wyrzucać za VPN.
			'key'		=> 'NTU4OnEzSmJZaENmWU1LcHJBYWw1VFN4enpVSGcwdkRFeHFs'	//Klucz do API można go uzyskać na stronie https://iphub.info/pricing

		],

	//cleanChannel() Funkcja czyści kanały, które nie są aktywne dłużej niż 7 dni w podanym sektorze.
		'functions_cleanChannel' => [

			'on'	=> false,												//true - włączona false - wyłączona
			'pid'	=> 1,													//Strefa, w której ma sprawdzać kanały, które są nieaktywne.
			'time'	=> 7													//Czas w dniach, po którym ma usuwać kanał.

		],

	//channelCreate() Funkcja zakłada kanały w podanym sektorze.
		'functions_channelCreate' => [

			'on'		=> false,											//true - włączona false - wyłączona
			'cid'		=> 1,												//ID kanału, na którego trzeba wejść, aby dostać kanał prywatny.
			'pid'		=> 2,												//Strefa, w której ma zakładać kanały prywatne.
			'ile'		=> 3,												//Liczba podkanałów.
			'gid'		=> 4,												//ID Grupy właściciela kanału.
			'channel_description'	=> '[hr]\n\nWłaściciel: %CLIENT_NICKNAME%\n\nData utworzenia: %DATE%\n\n[hr]', //Opis kanału %CLIENT_NICKNAME% - Nick właściciela kanału %DATE% - Data założenia %HOUR% - Godzina założenia
			'setting'	=> [

								'channel_flag_permanent' 					=> 1,
								'channel_flag_maxfamilyclients_unlimited' 	=> 1,
								'channel_flag_maxclients_unlimited' 		=> 1,
								'channel_maxclients'						=> '-1',
								'channel_maxfamilyclients' 					=> '-1',
								'channel_password' 							=> '',
								'channel_codec'								=> 4,
								'channel_codec_quality'						=> 6,
								'channel_flag_semi_permanent'				=> 0,
								'channel_needed_talk_power'					=> 0,

			],																//Dodatkowe ustawienia kanału głównego.
			'setting_subchannel'	=> [

								'channel_flag_permanent' 					=> 1,
								'channel_flag_maxfamilyclients_unlimited' 	=> 1,
								'channel_flag_maxclients_unlimited' 		=> 1,
								'channel_maxclients' 						=> '-1',
								'channel_maxfamilyclients'					=> '-1',
								'channel_topic' 							=> '',
								'channel_codec'								=> 4,
								'channel_codec_quality'						=> 6,
								'channel_flag_semi_permanent'				=> 0,
								'channel_needed_talk_power'					=> 0,

			],																	//Dodatkowe ustawienia podkanałów.
			'separator'		=> '. '												//Separator oddzielający nazwę kanału od numeru.

		],
		
	//channelNumber() Funkcja sprawdza i w razie, czego poprawia numer kanału.
		'functions_channelNumber' => [

			'on'			=> false,										//true - włączona false - wyłączona
			'pid'			=> 1,											//Strefa, w której ma sprawdzać numery.
			'separator'		=> '. '											//Separator oddzielający nazwę kanału od numeru.

		],

	//delInfoChannel() Funkcja ustawia w opisie kanału listę kanałów które zostana usunięte w najbliższym czasie.
		'functions_delInfoChannel' => [

			'on'		=> true,							//true - włączona false - wyłączona
			'pid'		=> 1,								//Strefa, w której ma sprawdzać kanały do usunięcia.
			'cid'		=> 2,								//ID kanału w którym ma ustawiać listę kanałów.
			'time'		=> 6								//Czas, po którym ma ustawić kanał w opisie czyli jeżeli kanały są usuwane po 7 dniach można ustawić 6 wtedy jeżeli kanał jest nieaktywny dłużej niż 6 dni trafia do opisu..

		],

	//delPermissions() Funkcja usuwa prywane permisje.
		'functions_delPermissions' => [

			'on'		=> false,											//true - włączona false - wyłączona
			'gid'		=> [

								0

			],																//ID Grupy, którą ma pomijać.
			'cldbid'	=> [

								0

			]																//Client database id użytkowników, których ma pomijać.

		],

	//delRank() Funkcja usuwa range po wejściu na kanało o podanym ID.
		'functions_delRank' => [

			'on'		=> false,											//true - włączona false - wyłączona
			'cid_gid'	=> [
			
							1 => 2
							
							]												//ID kanału, na który trzeba wejść wraz z ID rangi, którą ma zabrać po wejściu.

		],

	//groupOnline() Funkcja wyświetla listę osób z podanej grupy w opisie na kanale o podanym ID.
		'functions_groupOnline' => [
			'on'		=> false,																						//true - włączona false - wyłączona
			'cid'       => [
				1 => [																								//ID Kanału.
					'gid' => [	
						2	=> '[CENTER][SIZE=16][COLOR=#A12364][B]CEO[/B][/COLOR][/SIZE][/CENTER]\n',
						3	=> '[CENTER][SIZE=16][COLOR=#A12364][B]SA[/B][/COLOR][/SIZE][/CENTER]\n',
						4	=> '[CENTER][SIZE=16][COLOR=#A12364][B]NA[/B][/COLOR][/SIZE][/CENTER]\n'
					],																									//ID kanału, na którym ma być zmieniany opis oraz nazwa grupy.
					'title' => '[CENTER][B][COLOR=#ff0000][SIZE=17]Administracja TS3[/SIZE][/COLOR][/B][/CENTER]\n\n',
					'channel_name' => '[cspacer]▪ Administracja ({1} Online) ▪',										//Nazwa kanału {1} - oznacza liczbę online {2} - oznacza łączną liczbę osób.
					'name_online' => true																				//Czy ma zmieniać nazwę kanału.
				],
				5 => [																								//ID Kanału.
					'gid' => [	
						6	=> '[CENTER][SIZE=16][COLOR=#E82A0B][B]ROOT[/B][/COLOR][/SIZE][/CENTER]\n',
						7	=> '[CENTER][SIZE=16][COLOR=#E82A0B][B]Administrator[/B][/COLOR][/SIZE][/CENTER]\n',
						103	=> '[CENTER][SIZE=16][COLOR=#C54201][B]Support[/B][/COLOR][/SIZE][/CENTER]\n'
					],																									//ID kanału, na którym ma być zmieniany opis oraz nazwa grupy.
					'title' => '[CENTER][B][COLOR=#ff0000][SIZE=17]Administracja Forum[/SIZE][/COLOR][/B][/CENTER]\n\n',	//Tytuł w opisie.
					'channel_name' => '[cspacer]▪ Administracja MC ({1} Online) ▪',										//Nazwa kanału {1} - oznacza liczbę online {2} - oznacza łączną liczbę osób.
					'name_online' => true																				//Czy ma zmieniać nazwę kanału.
				]
			]
		],

	//moveAfk() Funkcja przenosi nieaktywne osoby na kanał o podanym ID.
		'functions_moveAfk' => [

			'on'				=> false,										//true - włączona false - wyłączona
			'cid'				=> 1,											//ID kanału, na który ma przenosić.
			'default_channel'	=> 2,											//ID kanału w razie wywalenia błędu (polecam podać poczekalnie).
			'gid'				=> [

									3, 4	

			],																	//ID grup odporne na afk.
			'cidaa'				=> [

									5, 6	

			],																	//ID kanałów, na których można być AFK.
			'input_muted'		=> 1,											//Czy ma przenosić za wyłączony mikrofon.
			'output_muted'		=> 1,											//Czy ma przenosić za wyłączony głośnik.
			'away'				=> 1,											//Czy ma przenosić za włączenie statusu AFK.
			'idle'				=> 1,											//Czy ma przenosić za czas bezczynności.
			'idle_time'			=> 900,											//Czas bezczynności w sekundach.

		],

	//newUser() Funkcja dodaje nowych użytkowników do opisu.
		'functions_newUser' => [

			'on'			=> false,											//true - włączona false - wyłączona
			'cid'			=> 1,												//ID kanału, na którym ma ustawiać liste nowych użytkowników.
			'time'			=> 86400											//Czas w sekundach od którego ma zaliczac.

		],

	//log() Funkcja zapisuje logi z bota do pliku.
		'functions_log' => [

			'on'	=> true,										//true - włączona false - wyłączona
			'power'	=> 2											//Moc zapisu logu.

		],

	//poke() Funkcja puka podane grupy jeżeli ktoś wbije na podany kanał.
		'functions_poke' => [

			'on'			=> true,										//true - włączona false - wyłączona
			'cid'		=> [

				1 => [														//ID Kanału, na który trzeba wejść, aby pukało admina.
				
					'gid'				=> [ 1, 2, 3 ],					//ID Grup, które ma pukać.
					'info_admin'		=> 1,								//Czy oprócz wiadomości na PW ma jeszcze pukać admina 1 – Tak 0 – Nie.
					'info_user'			=> 1,								//Czy ma informować graczy za pomocą poke czy msg.
					'cidafk'			=> [ 4, 5 ],						//ID kanałów AFK, które wykluczają admina z poke
					'anty_gid'			=> [ 6 ],							//ID grup, które wykluczają admina z poke.
					'input_muted'		=> 1,								//Czy ma wykluczać admina gdy ma wyłączony mikrofon 1 - Tak 0 - Nie.
					'output_muted'		=> 1,								//Czy ma wykluczać admina gdy ma wyłączony głośnik 1 - Tak 0 - Nie.
					'away'				=> 1,								//Czy ma wykluczać admina gdy ma status AFK 1 - Tak 0 - Nie.
					

				],
				
				2 => [														//ID Kanału, na który trzeba wejść, aby pukało admina.
				
					'gid'				=> [ 1, 2, 3 ],					//ID Grup, które ma pukać.
					'info_admin'		=> 1,								//Czy oprócz wiadomości na PW ma jeszcze pukać admina 1 – Tak 0 – Nie.
					'info_user'			=> 1,								//Czy ma informować graczy za pomocą poke czy msg.
					'cidafk'			=> [ 4, 5 ],						//ID kanałów AFK, które wykluczają admina z poke
					'anty_gid'			=> [ 6 ],							//ID grup, które wykluczają admina z poke.
					'input_muted'		=> 1,								//Czy ma wykluczać admina gdy ma wyłączony mikrofon 1 - Tak 0 - Nie.
					'output_muted'		=> 1,								//Czy ma wykluczać admina gdy ma wyłączony głośnik 1 - Tak 0 - Nie.
					'away'				=> 1,								//Czy ma wykluczać admina gdy ma status AFK 1 - Tak 0 - Nie.

					

				],
				
				3 => [
				
					'gid'				=> [ 1, 2, 3 ],						//ID Grup, które ma pukać.
					'info_admin'		=> 1,								//Czy oprócz wiadomości na PW ma jeszcze pukać admina 1 – Tak 0 – Nie.
					'info_user'			=> 1,								//Czy ma informować graczy za pomocą poke czy msg.
					'cidafk'			=> [ 4, 5 ],						//ID kanałów AFK, które wykluczają admina z poke
					'anty_gid'			=> [ 6 ],							//ID grup, które wykluczają admina z poke.
					'input_muted'		=> 1,								//Czy ma wykluczać admina gdy ma wyłączony mikrofon 1 - Tak 0 - Nie.
					'output_muted'		=> 1,								//Czy ma wykluczać admina gdy ma wyłączony głośnik 1 - Tak 0 - Nie.
					'away'				=> 1,								//Czy ma wykluczać admina gdy ma status AFK 1 - Tak 0 - Nie.

					

				]

			],

			'admin_time'		=> 120,										//Czas, po który ma ponownie powiadomić admina.
			'user_time'			=> 120										//Czas, po który ma ponownie powiadomić użytkownika.

		],

	//register() Funkcja automatycznie rejestruje użytkownika gdy on wbije na podane id kanału.
		 'functions_register' => [

			'on'	=> false,										//true - włączona false - wyłączona
			'gidm'	=> 1,											//ID grupy zarejestrowanego.
			'cidm'	=> 2,											//ID kanału zarejestrowanego.
			'gidk'	=> 3,											//ID grupy zarejestrowanej.
			'cidk'	=> 4											//ID kanału zarejestrowanej.

		],
		
	//rekord_online() Funkcja ustawia rekord osób online jako nazwa kanału o podanym ID.
		'functions_rekord_online' => [

			'on'	=> false,										//true - włączona false - wyłączona
			'cid'	=> 1											//ID kanału, na którym ma ustawiać rekord osób online.

		],

	//sendAd() Funkcja wysyła losową wiadomość na serwerze co określony czas.
		'functions_sendAd' => [

			'on'			=> false,								//true - włączona false - wyłączona.
			'time'			=> 10,									//Czas w minutach po jakim ma wysyłać losową wiadomość.
			'txt_group'		=> [
			
				['Testowa wiadomość od bota na serwerze' =>	[ -0 ]],
				['Testowa wiadomość od bota na pw' =>	[ 0 ]],
				['Testowa wiadomość do grup' =>	[ 1, 2, 3 ]]

			]														//Treść oraz gdzie i do jakich grup ma wysyłać wiadomość -1 - Wiadomość jest wysyłana na czacie serwera 0 - Wiadomość jest wysyłana do wszytkich na PW 

		],

	//servername() Funkcja ustawia nazwę serwera wraz z liczbą osób online.
		'functions_servername' => [

			'on'	=> false,										//true - włączona false - wyłączona
			'name'	=> 'TS3Server ({1}/128)'						//Nazwa serwera, {1} zostanie zmienione na liczbę osób online.

		],

	//sprchannel() Funkcja sprawdza nazwy kanału pod względem wulgaryzmów.
		'functions_sprchannel' => [

			'on'	=> false,										//true - włączona false - wyłączona
			'pid'	=> 1,											//Strefa, w której ma sprawdzać kanały, które zawierają wulgaryzmy w nazwie.
			'setting'	=> 1,										//0 - Zmiana nazwy 1 - Usunięcie kanału.
			'new_name'	=> 'Cenzura',								//Nazwa kanału jaką ma ustawić po edycji.

		],

	//sprnick() Funkcja sprawdza nicki użytkowników pod względem wulgaryzmów.
		'functions_sprnick' => [

			'on'	=> false,										//true - włączona false - wyłączona
			'slowa'	=> [

				'admin', 'root', 'ceo'

			],														//Dodatkowe słowa do cenzora słów.
			'gid'	=> [

				1, 2, 3

			]														//ID grup, które ma nie wyrzucać.

		],

	//statusTwitch() Funkcja ustawia w opisie status na kanale twitch.
		'functions_statusTwitch' => [

			'on'		=> false,									//true - włączona false - wyłączona
			'cid_name'	=> [

				34 => 'pago3',
				35 => 'izakooo'

			]														//ID kanału oraz nick na twitch.tv
			
		],

	//statusYt() Funkcja ustawia w opisie status na kanale twitch.
		'functions_statusYt' => [

			'on'		=> false,										//true - włączona false - wyłączona
			'key'		=> 'AIzaSyDdCIT6ptA0fdvCb6CwE5-jbUUqHeKKJrY',	//Klucz api
			'cid_id'	=> [

				1 => 'UCb9PGfYb_Cv1ysuENPIAvRQ'

			]															//ID kanału oraz ID kanału
			
		],

	//top_activity_time() Funkcja ustawia w opisie kanału o podanym ID TOP 10 aktywnych użytkowników.
		'functions_top_activity_time' => [

			'on'		=> false,									//true - włączona false - wyłączona
			'cid'		=> 1,										//ID kanału, w którym ma ustawiać TOP 10 online.
			'gid'		=> [

								2, 3

			],														//ID grupy, której ma nie wyświetlać w topce.
			'cldbid'	=> [

								4, 5, 6

			],														//Client database id użytkowników, których ma nie wyświetlać w topce np. MusicBOT czy też ten bot.
			'limit'		=> 20										//Limit osób, które ma wyświetlać w top.

		],

	//top_connections() Funkcja ustawia w opisie kanału o podanym ID TOP 10 połączeń z serwerem.
		'functions_top_connections' => [

			'on'		=> false,									//true - włączona false - wyłączona
			'cid'		=> 1,									//ID kanału, w którym ma ustawiać TOP 10 połączeń z serwerem.
			'gid'		=> [

								2, 3

			],														//ID grupy, której ma nie wyświetlać w topce.
			'cldbid'	=> [

								4, 5, 6

			],														//Client database id użytkowników, których ma nie wyświetlać w topce np. MusicBOT czy też ten bot.
			'limit'		=> 20										//Limit osób, które ma wyświetlać w top.

		],

	//top_longest_connection() Funkcja ustawia w opisie kanału o podanym ID TOP 10 Najdłuższych połączeń z serwerem.
		'functions_top_longest_connection' => [

			'on'		=> false,									//true - włączona false - wyłączona
			'cid'		=> 1,										//ID kanału, w którym ma ustawiać TOP 10 połączeń z serwerem.
			'gid'		=> [

								2, 3

			],														//ID grupy, której ma nie wyświetlać w topce.
			'cldbid'	=> [

								4, 5, 6

			],														//Client database id użytkowników, których ma nie wyświetlać w topce np. MusicBOT czy też ten bot.
			'limit'		=> 20										//Limit osób, które ma wyświetlać w top.

		],

	//welcome_messege() Funkcja wysyła wiadomość powitalną.
		'functions_welcome_messege' => [

			'on'		=> false,									//true - włączona false - wyłączona
			'txt'		=> file_get_contents(__DIR__ . '/welcome_messege_txt.php'), 		//Tekst wiadomości, którą dostanie użytkownik po wejściu na serwer.
			'gid'		=> 8,										//ID grupy niezarejestrowanej, dla których ma wysyłać inną wiadomość.
			'txt_new'	=> file_get_contents(__DIR__ . '/welcome_messege_txt_new.php')		//Tekst wiadomości, którą dostanie nowy użytkownik po wejściu na serwer.

		]

	];
?>
