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

echo $host;
echo $user;
echo $pass;
echo $dbname;
echo $dbtable1;

echo "CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;" | mysql -h$host -u$user -p

echo "CREATE TABLE IF NOT EXISTS $dbname.$dbtable1 (
job_id MEDIUMINT NOT NULL AUTO_INCREMENT,
filename VARCHAR(1024),
filets VARCHAR(1024),
datapack_start VARCHAR(10),
datapack_end VARCHAR(10),
event_start VARCHAR(10),
event_end VARCHAR(10),
window_size INT,
status VARCHAR(10),
ctime TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
mtime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY(job_id) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; " | mysql -h$host -u$user -p$pass

