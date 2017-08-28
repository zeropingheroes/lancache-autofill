#!/bin/bash
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
GREEN='\033[0;32m'
BLACK='\033[0m'

printf "${GREEN}Installing dependencies with apt${BLACK}\n"
apt install -y  lib32gcc1 \
                lib32stdc++6 \
                lib32tinfo5 \
                lib32ncurses5 \
                php7.0-cli \
                php7.0-mbstring \
                php7.0-sqlite \
                composer \
                expect \

printf "${GREEN}Installing dependencies with Composer${BLACK}\n"
cd $SCRIPT_DIR && composer install

printf "${GREEN}Creating database file${BLACK}\n"
cd $SCRIPT_DIR && touch "database.sqlite"

printf "${GREEN}Creating your enviroment file${BLACK}\n"
cd $SCRIPT_DIR && cp ".env.example" ".env"

cd $SCRIPT_DIR && ./lancache-autofill app:initialise-database

cd $SCRIPT_DIR && ./lancache-autofill steam:update-app-list