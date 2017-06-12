#!/bin/bash
    case "$1" in
        "start")
            if ! screen -list | grep -q "botphp"; then
                screen -AmdS botphp php bot.php
                echo -e '\e[30;48;5;82mBot został uruchomiony\e[0m'
            else
                echo -e '\e[30;48;5;1mBot jest juz uruchomiony!\e[0m'
            fi
        ;;
        "stop")
            screen -X -S botphp stuff "^C"vvv
            echo -e '\e[30;48;5;82mPomyslnie zatrzymano bota!\e[0m'
        ;;
        "restart")
            screen -X -S botphp stuff "^C"
            screen -AmdS botphp php bot.php
			echo -e '\e[30;48;5;82mRestart bota zakończony!\e[0m'
        ;;
        *)
            echo -e 'Uzyj start | stop | restart'
        ;; esac
