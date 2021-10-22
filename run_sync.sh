#! /bin/bash

while :
do
    php -f sync.php $1
    echo "SLEEP 5 SEC"
    sleep 5
    clear 
done