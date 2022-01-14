#!/bin/bash

set -e

if [ $# -lt 2 ]; then
   echo "Usgae: $0 [ttg/twit/twig/sn/twiet/ww/floss/tri] start_index [stop_index]"
   exit 1
fi

popcast_name=$1
shift

if [ $# -eq 1 ]; then
   start=$1
   stop=$1
elif [ $# -eq 2 ]; then
   start=$1
   stop=$2
fi

for i in $(seq -w $start $stop); do
   if [ $i -lt 10 ]; then
      index=000${i}
   elif [ $i -lt 100 ]; then
      index=00${i}
   elif [ $i -lt 1000 ]; then
      index=0${i}
   else
      index=$i
   fi

   wget -c https://twit.cachefly.net/audio/${popcast_name}/${popcast_name}${index}/${popcast_name}${index}.mp3
done

