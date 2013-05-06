#!/bin/bash
#Test Shell only
#Last Edit by Jacky.Wu
#Last Edit date 23:03 5.2 2013
WRITE_DIR=/home/corei7/Project/eoj_files/work/
COUNT=50
CPFILE=/home/corei7/Project/test/test.c
BREAKPOINT=1
i=1

echo PID:$$...
cd ${WRITE_DIR}
while true
do
  BREAKPOINT=`expr $i + $COUNT`
  while true
  do
    cp ${CPFILE} 1-1-${i}.c
    i=`expr $i + 1`
    [ $i -gt $BREAKPOINT ] && break
  done
  sleep 1
done
