#! /bin/bash -
EOJ_FILE_PATH=/var/eoj_files
LONG_BITS=`getconf LONG_BIT`
REDHAT_VERSION=`cat /etc/redhat-release | sed 's/.*\([0-9]\)\.[0-9].*/\1/'`
HTTP_CONF=/etc/httpd/conf/httpd.conf
if [ $REDHAT_VERSION = "6" ]
then
	SYSLOG=rsyslog
else
	SYSLOG=syslog
fi

#! test user
if [ `id -u` != 0 ]
then
	echo "must run by root"
	exit 1
fi

#! system requirements
yum install -y git wget unzip make gcc gcc-c++ libxml2 libxml2-devel mysql mysql-server mysql-devel php php-dom php-mysql httpd
if [ $? != 0 ]
then
	echo "install system requirement fail"
	exit 1
fi

#! clean existed file and dir
rm -rf $EOJ_FILE_PATH
mv /var/www/html /var/www/html.bak
rm -rf EOJ
rm -f ZendFramework-1.12.0.zip

#! download zend framework
wget http://framework.zend.com/releases/ZendFramework-1.12.0/ZendFramework-1.12.0.zip
if [ $? != 0 ]
then
	echo "download zendframework fail"
	exit 1
fi

#! unzip zend framework
unzip -oq ZendFramework-1.12.0.zip
if [ $? != 0 ]
then
	echo "extract file fail"
	exit 1
fi

#! get EOJ source code from github
git clone git://github.com/core2duoe6420/EOJ.git
if [ $? != 0 ]
then
	echo "get EOJ source code fail"
	exit 1
fi

#! compile eojdaemon
cd EOJ/eojdaemon
#! some modification to makefile
sed -i "s/@.out/@/" makefile
if [ $LONG_BITS -eq 32 ]
then
	sed -i "s/lib64/lib/" makefile
fi
#! fix xml file path in main.c and eoj.xml
sed -i "s;\$EOJ_FILE_PATH;$EOJ_FILE_PATH;" main.c
sed -i "s;\$EOJ_FILE_PATH;$EOJ_FILE_PATH;g" eoj.xml
#! go
make
if [ $? != 0 ]
then
	echo "compile eojdaemon fail"
	exit 1;
fi
#! compile eojjudge
cd ../eojjudge
sed -i "s/@.out/@/" makefile
if [ $LONG_BITS -eq 32 ]
then
	sed -i "s/lib64/lib/" makefile
fi
#! go
make
if [ $? != 0 ]
then
	echo "compile eojjudge fail"
	exit 1;
fi

#! environment for eojdaemon
#! syslog config
cat /etc/$SYSLOG.conf | grep "user.debug" > /dev/null
if [ $? != 0 ]
then
	echo "user.debug /var/log/eoj.log" >> /etc/$SYSLOG.conf
fi
service $SYSLOG restart
if [ $? != 0 ]
then
	echo "service syslog restart fail"
	exit 1
fi
#! lock file
touch /var/run/eojdaemon.lock
if [ $? != 0 ]
then
	echo "create lock file fail"
	exit 1
fi
chmod 777 /var/run/eojdaemon.lock

#! apache configuration
#! backup first
cp $HTTP_CONF $HTTP_CONF.bak
cat $HTTP_CONF | grep "ecustoj.info" >> /dev/null
if [ $? != 0 ]
then
	echo "<VirtualHost *:80>
	ServerName ecustoj.info
    DocumentRoot /var/www/html/public
    ServerAlias ecustoj.info
    RewriteEngine off
    <Location />
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule !.(js|ico|gif|jpg|jpeg|pdf|png|css)$ /index.php
    </Location>

    <Directory /var/www/html/public>
	Options FollowSymLinks Includes ExecCGI
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>" >> $HTTP_CONF
fi

cd ..
cp -r web /var/www/html
cd ..
cp -r ZendFramework-1.12.0/library /var/www/html/

#! restart apache
service httpd restart
if [ $? != 0 ]
then
	echo "apache restart fail"
	exit 1
fi

#! setup database
cd EOJ/mysql
service mysqld start
mysql -u root -p << EOF
DROP USER 'eojapp'@'localhost';
DROP DATABASE IF EXISTS eojdb;
source mysql_tbls_views.sql
EOF
mysql -u eojapp -pecust << EOF
source mysql_setup_procs.sql
source mysql_triggers.sql
source mysql_init_data.sql
source sample.sql
EOF

if [ $? != 0 ]
then
	echo "mysql setup fail"
	exit 1
fi
cd ..

#! make working dirs
mkdir $EOJ_FILE_PATH
if [ $? != 0 ]
then
	echo "create working dir fail"
	exit 1
fi
mkdir $EOJ_FILE_PATH/work
mkdir $EOJ_FILE_PATH/codes
mkdir $EOJ_FILE_PATH/input
mkdir $EOJ_FILE_PATH/answer
mkdir $EOJ_FILE_PATH/tmp
mkdir $EOJ_FILE_PATH/err

#! change owner and prio
chown -R apache:apache $EOJ_FILE_PATH
chmod 777 -R $EOJ_FILE_PATH
#! move file to EOJ_FILE_PATH
cp eojdaemon/eojdaemon $EOJ_FILE_PATH
cp eojjudge/eojjudge $EOJ_FILE_PATH
cp eojdaemon/eoj.xml $EOJ_FILE_PATH
#! set eoj.xml location to SubmitCode.php
sed -i "s;\$EOJ_FILE_PATH;$EOJ_FILE_PATH;" /var/www/html/application/models/SubmitCode.php

#! start daemon
$EOJ_FILE_PATH/eojdaemon

#! these are sample input and answer file
#! problem 1
id=1
rm -rf $EOJ_FILE_PATH/input/$id
rm -rf $EOJ_FILE_PATH/answer/$id
mkdir $EOJ_FILE_PATH/input/$id
mkdir $EOJ_FILE_PATH/answer/$id

for i in `seq 4`
do
        mkdir $EOJ_FILE_PATH/answer/$id/$i
done

echo "1 3" > $EOJ_FILE_PATH/input/$id/1
echo "2 -100" > $EOJ_FILE_PATH/input/$id/2
echo "10000000 2000000" > $EOJ_FILE_PATH/input/$id/3
echo "-1300 -100" > $EOJ_FILE_PATH/input/$id/4

echo "4" > $EOJ_FILE_PATH/answer/$id/1/1
echo "-98" > $EOJ_FILE_PATH/answer/$id/2/1
echo "12000000" > $EOJ_FILE_PATH/answer/$id/3/1
echo "-1400" > $EOJ_FILE_PATH/answer/$id/4/1

#! problem 2
id=2
rm -rf $EOJ_FILE_PATH/input/$id
rm -rf $EOJ_FILE_PATH/answer/$id
mkdir $EOJ_FILE_PATH/input/$id
mkdir $EOJ_FILE_PATH/answer/$id

for i in `seq 4`
do
        mkdir $EOJ_FILE_PATH/answer/$id/$i
done

echo "3150.2 1200" > $EOJ_FILE_PATH/input/$id/1
echo "0 5000" > $EOJ_FILE_PATH/input/$id/2
echo "30000 23421.24" > $EOJ_FILE_PATH/input/$id/3
echo "2900 2231" > $EOJ_FILE_PATH/input/$id/4

echo "2175.10" > $EOJ_FILE_PATH/answer/$id/1/1
echo "2500.00" > $EOJ_FILE_PATH/answer/$id/2/1
echo "26710.62" > $EOJ_FILE_PATH/answer/$id/3/1
echo "2565.50" > $EOJ_FILE_PATH/answer/$id/4/1

#! problem 3
id=3
rm -rf $EOJ_FILE_PATH/input/$id
rm -rf $EOJ_FILE_PATH/answer/$id
mkdir $EOJ_FILE_PATH/input/$id
mkdir $EOJ_FILE_PATH/answer/$id

for i in `seq 2`
do
        mkdir $EOJ_FILE_PATH/answer/$id/$i
done

echo "1fewfew 3" >> $EOJ_FILE_PATH/input/$id/1
echo "1231-012  23  332\n 3" >> $EOJ_FILE_PATH/input/$id/1
echo "13dfsd3" >> $EOJ_FILE_PATH/input/$id/1
echo "" >> $EOJ_FILE_PATH/input/$id/1
echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa" >> $EOJ_FILE_PATH/input/$id/1
echo "@" >> $EOJ_FILE_PATH/input/$id/1
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "2 -100" >> $EOJ_FILE_PATH/input/$id/2
echo "@" >> $EOJ_FILE_PATH/input/$id/2

echo "5" > $EOJ_FILE_PATH/answer/$id/1/1
echo "56" > $EOJ_FILE_PATH/answer/$id/2/1

#! problem 4
id=4
rm -rf $EOJ_FILE_PATH/input/$id
rm -rf $EOJ_FILE_PATH/answer/$id
mkdir $EOJ_FILE_PATH/input/$id
mkdir $EOJ_FILE_PATH/answer/$id

for i in `seq 13`
do
        mkdir $EOJ_FILE_PATH/answer/$id/$i
done

echo "0" >> $EOJ_FILE_PATH/input/$id/1
echo "1" >> $EOJ_FILE_PATH/input/$id/2
echo "2" >> $EOJ_FILE_PATH/input/$id/3
echo "3" >> $EOJ_FILE_PATH/input/$id/4
echo "4" >> $EOJ_FILE_PATH/input/$id/5
echo "5" >> $EOJ_FILE_PATH/input/$id/6
echo "6" >> $EOJ_FILE_PATH/input/$id/7
echo "7" >> $EOJ_FILE_PATH/input/$id/8
echo "8" >> $EOJ_FILE_PATH/input/$id/9
echo "9" >> $EOJ_FILE_PATH/input/$id/10
echo "10" >> $EOJ_FILE_PATH/input/$id/11
echo "11" >> $EOJ_FILE_PATH/input/$id/12
echo "12" >> $EOJ_FILE_PATH/input/$id/13

echo "1" > $EOJ_FILE_PATH/answer/$id/1/1
echo "1" > $EOJ_FILE_PATH/answer/$id/2/1
echo "2" > $EOJ_FILE_PATH/answer/$id/3/1
echo "6" > $EOJ_FILE_PATH/answer/$id/4/1
echo "24" > $EOJ_FILE_PATH/answer/$id/5/1
echo "120" > $EOJ_FILE_PATH/answer/$id/6/1
echo "720" > $EOJ_FILE_PATH/answer/$id/7/1
echo "5040" > $EOJ_FILE_PATH/answer/$id/8/1
echo "40320" > $EOJ_FILE_PATH/answer/$id/9/1
echo "362880" > $EOJ_FILE_PATH/answer/$id/10/1
echo "3628800" > $EOJ_FILE_PATH/answer/$id/11/1
echo "39916800" > $EOJ_FILE_PATH/answer/$id/12/1
echo "479001600" > $EOJ_FILE_PATH/answer/$id/13/1

#! problem 5
id=5
rm -rf $EOJ_FILE_PATH/input/$id
rm -rf $EOJ_FILE_PATH/answer/$id
mkdir $EOJ_FILE_PATH/input/$id
mkdir $EOJ_FILE_PATH/answer/$id

for i in `seq 5`
do
        mkdir $EOJ_FILE_PATH/answer/$id/$i
done

echo "1" >> $EOJ_FILE_PATH/input/$id/1
echo "2" >> $EOJ_FILE_PATH/input/$id/2
echo "3" >> $EOJ_FILE_PATH/input/$id/3
echo "7" >> $EOJ_FILE_PATH/input/$id/4
echo "9" >> $EOJ_FILE_PATH/input/$id/5

echo "1" >> $EOJ_FILE_PATH/answer/$id/1/1
echo "1" >> $EOJ_FILE_PATH/answer/$id/2/1
echo "2   4" >> $EOJ_FILE_PATH/answer/$id/2/1
echo "1" >> $EOJ_FILE_PATH/answer/$id/3/1
echo "2   4" >> $EOJ_FILE_PATH/answer/$id/3/1
echo "3   6   9" >> $EOJ_FILE_PATH/answer/$id/3/1
echo "1" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "2   4" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "3   6   9" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "4   8  12  16" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "5  10  15  20  25" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "6  12  18  24  30  36" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "7  14  21  28  35  42  49" >> $EOJ_FILE_PATH/answer/$id/4/1
echo "1" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "2   4" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "3   6   9" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "4   8  12  16" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "5  10  15  20  25" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "6  12  18  24  30  36" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "7  14  21  28  35  42  49" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "8  16  24  32  40  48  56  64" >> $EOJ_FILE_PATH/answer/$id/5/1
echo "9  18  27  36  45  54  63  72  81" >> $EOJ_FILE_PATH/answer/$id/5/1

#! finished! some information
echo "EOJ setup complete! try 'ps -ef | grep eoj' to see whether eojdaemon is running."
echo "If eojdaemon doesn't run, check /var/log/eoj.log"
