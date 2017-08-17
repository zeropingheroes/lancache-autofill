#!/bin/bash
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
GREEN='\033[0;32m'
BLACK='\033[0m'

printf "${GREEN}Installing dependencies with apt${BLACK}\n"
apt install -y  lib32gcc1 \
                lib32stdc++6 \
                lib32tinfo5 \
                lib32ncurses5 \
                libcurl3-gnutls:i386 \
                php7.0-cli \
                php7.0-mbstring \
                php7.0-sqlite \
                composer \
                expect \

printf "${GREEN}Installing dependencies with Composer${BLACK}\n"
composer update

printf "${GREEN}Installing Steam${BLACK}\n"
mkdir -p /usr/games/steam && cd /usr/games/steam && curl -sqL "http://media.steampowered.com/client/steamcmd_linux.tar.gz" | tar zxvf -

printf "${GREEN}Creating database file${BLACK}\n"
cd $SCRIPT_DIR && touch "database.sqlite"

printf "${GREEN}Creating your enviroment file${BLACK}\n"
cd $SCRIPT_DIR && cp ".env.example" ".env" && /bin/nano ".env"

cd $SCRIPT_DIR && ./lancache-autofill app:initialise-database

cd $SCRIPT_DIR && ./lancache-autofill steam:update-app-list