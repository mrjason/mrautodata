#!/bin/bash
FILENAME=$1
MY_DIR=`dirname $0`

$MY_DIR/data_auto_bash_cfg.sh

FILE=$MY_DIR"/batches/"$FILENAME
echo $FILE
cat $FILE | while read LINE
do
    if [ ! -z $LINE ]
    then
        echo $MY_DIR"/bin/auto n -s $LINE"
        $MY_DIR/bin/auto n -s $LINE
    fi
done

killall -9 firefox
