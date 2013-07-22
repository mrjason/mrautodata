#!/bin/bash
source ~/data_auto_bash_cfg.sh

AUTO=$BASEDIR$DIR/bin/auto
for i in {1..11}
do
  $AUTO ls -t batch$i -a
  $AUTO ls -t batch$i -a > batches/batch$i
done
for i in {1..1}
do
  $AUTO ls -t ukbatch$i -a
  $AUTO ls -t ukbatch$i -a > batches/ukbatch$i
done

$AUTO ls -t sales -a > batches/sales
$AUTO ls -t nightly -a > batches/nightly