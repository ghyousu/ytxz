#!/bin/bash

## create new random directory for one-time use

new_hash=$(date +%s | md5sum | awk '{print $1}')

new_dir=${new_hash}tempdir

mkdir -p /app/$new_dir

cp -v /app/*.php /app/$new_dir

echo $new_dir
