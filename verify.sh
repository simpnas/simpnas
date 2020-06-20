#!/bin/bash

id -u $1 > /dev/null
if [ $? -ne 0 ]
then
  echo "0"
  exit 1
else
  PASSWD=$2
  export PASSWD
  ORIGPASS=`grep -w "$1" /etc/shadow | cut -d: -f2`
  export ALGO=`echo $ORIGPASS | cut -d'$' -f2`
  export SALT=`echo $ORIGPASS | cut -d'$' -f3`
  GENPASS=$(perl -le 'print crypt("$ENV{PASSWD}","\$$ENV{ALGO}\$$ENV{SALT}\$")')
  if [ "$GENPASS" == "$ORIGPASS" ]
  then
    echo "1"
    exit 0
  else
    echo "0"
    exit 1
  fi
fi