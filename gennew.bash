#!/bin/bash

## create new random directory for one-time use

if [ "$1" != "" ]; then
   new_dir=$1
else
   new_hash=$(date +%s | md5sum | awk '{print $1}')
   new_dir=${new_hash}tempdir
fi

mkdir -p /app/$new_dir

cp -v /app/*.php /app/$new_dir

echo $new_dir
