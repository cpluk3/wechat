#!/bin/bash

dateTale=$1
now=$2

while [ "$dateTale" -lt "$now" ]
do
	/bin/cat ../raw/source_files/hk_a1_$dateTale | sort -n | uniq -c | awk '{print $2" "$1}' > ../raw/u_$dateTale
	dateTale=$(date -d $dateTale" 1 day" +%Y%m%d)
done
