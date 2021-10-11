#!/bin/bash
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
GREEN='\033[0;32m'
BLACK='\033[0m'

printf "${GREEN}Installing apt package dependencies${BLACK}\n"
sudo apt update -y
sudo apt install -y lib32gcc1 lib32stdc++6 lib32tinfo6 lib32ncurses6 composer expect zip unzip \
                    php-cli php-mbstring php-sqlite3 php-bcmath php-dom

printf "${GREEN}Installing PHP dependencies with Composer${BLACK}\n"
cd $SCRIPT_DIR && composer install

printf "${GREEN}Creating database file${BLACK}\n"
cd $SCRIPT_DIR && touch "database.sqlite"

printf "${GREEN}Creating your environment file${BLACK}\n"
cd $SCRIPT_DIR && cp ".env.example" ".env"

cd $SCRIPT_DIR && ./lancache-autofill app:initialise-database --yes

cd $SCRIPT_DIR && ./lancache-autofill steam:update-app-list

cd $SCRIPT_DIR && ./lancache-autofill steam:initialise --yes
