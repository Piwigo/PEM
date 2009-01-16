#!/bin/bash

# usage: ./generate_dbcreate_file.sh root conway pwg_forum pwg_ > pem.sql

user=$1
pass=$2
database=$3
prefix=$4

echo "show tables like '$prefix%'" > /tmp/generate_dbcreate_file.$$

mysqldump \
  --no-defaults \
  --user=$user \
  --password=$pass \
  --add-drop-table \
  --no-data \
  $database \
  $(mysql \
    --user=$user \
    --password=$pass \
    $database \
    < /tmp/generate_dbcreate_file.$$ \
    | grep ^$prefix \
    | perl -ne 'END {print join(" ", @tables)} chomp; push @tables, $_;' \
  ) \
  | perl -pe "s{$prefix}{pem_}g; s{ENGINE=MyISAM}{}; s{AUTO_INCREMENT=\d+}{}g;" \
  | grep -v '^/\*' \
  | grep -v '^--' \
  | grep -v ^$ \
  | grep -v '^SET '
