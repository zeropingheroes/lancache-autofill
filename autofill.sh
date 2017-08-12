#!/bin/bash
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BLUE='\033[1;34m'
BLACK='\033[0m'

# Configuration

TMP_DIR="/tmp/lancache-autofill"
STEAMCMD="/usr/games/steam/steamcmd.sh"
STEAM_USERNAME="ilumos"
STEAM_APP_DOWNLOAD_LIST="$SCRIPT_DIR/steam/app_download_list.json"

echo "Clearing previously downloaded data..."
rm -r $TMP_DIR
mkdir -p $TMP_DIR

echo "Loading list of Steam app ID to download..."
readarray -t STEAM_APP_IDS < <(cat $STEAM_APP_DOWNLOAD_LIST | jq '.[] | .id' )

for STEAM_APP_ID in "${STEAM_APP_IDS[@]}"
do
    # Get Steam app name from download list
    STEAM_APP_NAME=$(cat $STEAM_APP_DOWNLOAD_LIST | jq --raw-output ".[] | select(.id == $STEAM_APP_ID) | .name")

    printf "${BLUE}Starting download of $STEAM_APP_NAME (App ID: $STEAM_APP_ID)${BLACK}\n"
    $STEAMCMD  +login $STEAM_USERNAME                              \
               +@sSteamCmdForcePlatformType windows                \
               +force_install_dir $TMP_DIR/steam/$STEAM_APP_ID     \
               +app_license_request $STEAM_APP_ID                  \
               +app_update $STEAM_APP_ID validate                  \
               +quit
    printf "${BLUE}Finished download of $STEAM_APP_NAME (App ID: $STEAM_APP_ID)${BLACK}\n"
done
