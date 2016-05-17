#!/bin/bash
CONFIG_FILE="../conf/config.ini"
SECTION="db"

function parse_ini(){
        local config=$1
        local section=$2
        local var=$3
        eval `sed -e 's/[[:space:]]*\=[[:space:]]*/=/g' \
                -e 's/;.*$//' \
                -e 's/[[:space:]]*$//' \
                -e 's/^[[:space:]]*//' \
                -e "s/^\(.*\)=\([^\"']*\)$/\1=\"\2\"/" \
                < $config \
                | sed -n -e "/^\[$section\]/,/^\s*\[/{/^[^;].*\=.*/p;}"`
        echo "${!var}"
}

host=$(parse_ini $CONFIG_FILE $SECTION 'host')
user=$(parse_ini $CONFIG_FILE $SECTION 'user')
pass=$(parse_ini $CONFIG_FILE $SECTION 'pass')
dbname=$(parse_ini $CONFIG_FILE $SECTION 'name')
dbtable1=$(parse_ini $CONFIG_FILE $SECTION 'table1')
dbtable2=$(parse_ini $CONFIG_FILE $SECTION 'table2')

echo "DROP TABLE $dbname.$dbtable1" | mysql -h$host -u$user -p$pass;
echo "DROP DATABASE $dbname" | mysql -h$host -u$user -p$pass;

