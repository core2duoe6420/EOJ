#!/bin/bash
#kill the pid which have the name you defined

if [ $# != 1 ]
then
  echo "Usage: $0 KILLNAME.."
  exit -1
fi

#set the want-kill process part-string
KILL_NAME=$1

#get the cmd string and pid
echo "`ps -ef | grep ${KILL_NAME}` " | grep -v "grep" | grep -v "bash" > /tmp/test.tmp
echo "`awk '{for(i=8;i<NF;i++) printf $i} {print $NF} ' /tmp/test.tmp`" > /tmp/pname.list
echo "`awk '{print $2}' /tmp/test.tmp`" > /tmp/pid.list

#set array
t=0
for i in `cat /tmp/pname.list`
do
  arrt[$t]=$i;
  t=`expr $t + 1`
done

#none pid found,exit
[ $t -eq 0 ] && echo "Nothing found! Exit.." && exit -1

t=0
#try prompt user and kill it
for p in `cat /tmp/pid.list`
do
    read -p "kill the [pid] $p [cmd] ${arrt[$t]}?[y/n] " check
    if [ ! -z $check ] && [ $check == "y" ]
    then
      kill -9 $p
      if [ $? -eq 0 ]
      then
        echo "killed [pid] $p : [cmd] ${arrt[$t]}.."
      else echo "failed to kill [pid] $p : [cmd] ${arrt[$t]}.."
      fi
      t=`expr $t + 1`
    else
      echo "skipped.."
      t=`expr $t + 1`
    fi
done

#delete the temp file
rm -f /tmp/test.tmp /tmp/pid.list /tmp/pname.list

#exit
echo done..
exit 0
