#!/bin/bash
source ./data_auto_bash_cfg.sh

AUTO=$BASEDIR$DIR/bin/auto

for i in {1..11}
do
  $AUTO ls -t nabatch$i -a
  $AUTO ls -t nabatch$i -a > batches/nabatch$i
done

for i in {1..1}
do
  $AUTO ls -t emeabatch$i -a
  $AUTO ls -t emeabatch$i -a > batches/emeabatch$i
done

for i in {1..1}
do
  $AUTO ls -t apacbatch$i -a
  $AUTO ls -t apacbatch$i -a > batches/apacbatch$i
done

for i in {1..1}
do
  $AUTO ls -t lacbatch$i -a
  $AUTO ls -t lacbatch$i -a > batches/lacbatch$i
done

$AUTO ls -t sales -a > batches/sales
$AUTO ls -t nightly -a > batches/nightly