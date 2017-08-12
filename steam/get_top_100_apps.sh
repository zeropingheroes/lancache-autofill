#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

URL="http://steamspy.com/api.php?request=top100in2weeks"

echo "Downloading list of top 100 free apps..."
curl -s $URL | jq '[.[] | select(.price == "0") | {id: .appid, name}]' > $SCRIPT_DIR/top_100_free_apps.json

echo "Downloading list of top 100 paid apps..."
curl -s $URL | jq '[.[] | select(.price != "0") | {id: .appid, name}]' > $SCRIPT_DIR/top_100_paid_apps.json

echo "Creating default app download list from list of top 100 free apps..."
cp $SCRIPT_DIR/top_100_free_apps.json $SCRIPT_DIR/app_download_list.json
