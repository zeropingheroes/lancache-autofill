#!/bin/bash

apt install -y lib32gcc1 jq

mkdir -p /usr/games/steam
cd /usr/games/steam
curl -sqL "https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz" | tar zxvf -