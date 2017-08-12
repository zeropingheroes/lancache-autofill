#!/bin/bash

apt install -y lib32gcc1 lib32stdc++6 lib32tinfo5 lib32ncurses5 libcurl3-gnutls:i386 jq

mkdir -p /usr/games/steam

URL="http://media.steampowered.com/client/steamcmd_linux.tar.gz"

cd /usr/games/steam && curl -sqL $URL | tar zxvf -
