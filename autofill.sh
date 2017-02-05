#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

PATH_TMP="/tmp/lancache-autofill"
PATH_STEAMCMD="/usr/games/steam/steamcmd.sh"

STEAM_REQUEST_WINDOWS="+@sSteamCmdForcePlatformType windows"

echo "Enter Steam username:"

read STEAM_USERNAME

echo "Clearing data from previous download location..."
rm -r $PATH_TMP
mkdir -p $PATH_TMP

if [ ! -f $DIR/steam/apps_free.json
    echo "Getting top 100 Steam apps..."
    $DIR/steam/get_top_100_apps.sh
fi

echo "Starting download of free Steam apps..."
readarray -t STEAM_APP_IDS < <(cat $DIR/steam/apps_free.json | jq '.[] | .id' )

for STEAM_APP_ID in "${STEAM_APP_IDS[@]}"
do
    STEAM_APP_NAME=$(cat $DIR/steam/apps_free.json | jq --raw-output ".[] | select(.id == $STEAM_APP_ID) | .name")
    echo "##################################################################################"
    echo "Starting download of $STEAM_APP_NAME (App ID: $STEAM_APP_ID)"
    echo "Launching SteamCMD..."
    $PATH_STEAMCMD  +login $STEAM_USERNAME                              \
                    $STEAM_REQUEST_WINDOWS                              \
                    +force_install_dir $PATH_TMP/steam/$STEAM_APP_ID    \
                    +app_license_request $STEAM_APP_ID                  \
                    +app_update $STEAM_APP_ID validate                  \
                    +app_update_cancel 3                                \
                    +quit
    echo "Finished download of $STEAM_APP_NAME (App ID: $STEAM_APP_ID)"
    echo "##################################################################################"
    STEAM_APP_NAME=""
done
