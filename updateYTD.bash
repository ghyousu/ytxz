#!/bin/bash

set -e

old_ver=$(youtube-dl --version)

md5sum youtube-dl
rm -fv youtube-dl

# wget -c https://youtube-dl.org/downloads/latest/youtube-dl > /dev/null

wget -c https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -O youtube-dl

md5sum youtube-dl

new_ver=$(youtube-dl --version)

echo git ci youtube-dl -m "updated youtube-dl from $old_ver to $new_ver"
echo git push
