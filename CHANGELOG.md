# Changelog

## [2.1.1] - 17.09.2017
* Dodanie funkcji sendAd(), która wysyła losową wiadomość
* Dodanie funkcji statusTwitch(), która ustawia w opisie status na kanale twitch.
* Dodano TOP 10 najdłuższe połączenie oraz TOP 10 najwięcej połączeń.
* Zmiana API do AntyVPN.
* Kilka drobnych poprawek.
Aktualizacja wymaga ponownego konfigurowania bota lub dodanie brakujących opcji w konofitu oraz wykonania pliku update.php.
Wystarczy wpisać php update.php.

## [2.0.6] - 25.06.2017
* Dodanie funkcji addrank(), która ustawia rangę po wejściu na kanał.
* Dodanie możliwości ustawienia opisu kanału można podać %CLIENT_NICKNAME% - Nick właściciela %DATE% - Data założenia %HOUR% - Godzina założenia.
* Dodano możliwość wyboru czy ma pukać administratora czy wysyłać prywatną wiadomość.
* Poprawienie błędu, gdzie podczas zakładania kanału nie dodawało sub kanału.
* Poprawienie błędu z odświeżaniem administracji online podziękowania za zgłoszenie dla Pir3x.
* Poprawienie błędu z numerowaniem kanałów prywatnych podziękowanie za zgłoszenie dla Majako.
* Inne drobne poprawki.
Aktualizacja wymaga ponownego konfigurowania bota lub dodanie brakujących opcji w konofitu.

## [2.0.4] - 17.06.2017
* Poprawki w kodzie - dodanie elementów statycznych.
* Naprawa błędu w funkcji sprchannel wynikającego z błędnego klucza.
Aktualizacja ta wymaga nadpisania config.php lub edycji linijki 149 mianowicie zamiany 'cid' na 'pid'.


## [2.0.3] - 12.06.2017

* Dodano funkcję ChannelNumber (), która ustawia numer kanału w razie jego braku.
* Zmniejszenie spamu, który wywoływała funkcja admits_ts_online().
