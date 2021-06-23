#!/bin/bash

set -e

old_ver=$(python youtube-dl --version)

rm -fv youtube-dl

wget -c https://youtube-dl.org/downloads/latest/youtube-dl > /dev/null

new_ver=$(python youtube-dl --version)

git ci youtube-dl -m "updated youtube-dl from $old_ver to $new_ver"

git push
