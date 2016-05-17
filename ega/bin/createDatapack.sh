#!/bin/bash

name="hk_a1"
dateTale=$1
now=$2

rawfolder="../raw"
varfolder="../var"

tempfile="temp_"$3
outfile="out_"$3

/bin/rm $tempfile
/bin/rm $outfile
/bin/touch $outfile

while [ "$dateTale" -lt "$now" ]
do
	echo "Converting file of $dateTale"...
	/bin/cat "$rawfolder"/"$name"_"$dateTale" | sort -n | /usr/bin/uniq -c | /usr/bin/awk '{print $2" "$1}' > "$varfolder"/u_"$dateTale"
	/usr/bin/php ./merge_tool.php $outfile "$varfolder"/u_"$dateTale" $tempfile
	/bin/rm "$varfolder"/u_"$dateTale"
	/bin/mv $tempfile $outfile
	dateTale=$(date -d $dateTale" 1 day" +%Y%m%d)
done
