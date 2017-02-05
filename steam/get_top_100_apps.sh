#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

URL="http://steamspy.com/api.php?request=top100in2weeks"

echo "Getting list of free apps..."
curl -s $URL | jq '[.[] | select(.price == "0") | {id: .appid, name}]' > $DIR/apps_free.json

echo "Getting list of paid apps..."
curl -s $URL | jq '[.[] | select(.price != "0") | {id: .appid, name}]' > $DIR/apps_paid.json

