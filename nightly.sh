#!/bin/bash
FILENAME=$1
B=batches

source ~/data_auto_bash_cfg.sh

cd $BASEDIR/$DIR
pwd
FILE="$BASEDIR$DIR/$B/$FILENAME"
echo $FILE
cat $FILE | while read LINE
do
    if [ ! -z $LINE ]
    then
        echo "./bin/auto n -s $LINE"
        ./bin/auto n -s $LINE
    fi
done

killall -9 firefox
