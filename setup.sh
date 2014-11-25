#!/bin/bash

chmod 777 -R pages
chmod 777 -R uploads
chmod 777 -R tmp
chmod a+rX -R .
chmod a+rX ..
chmod a+rX ../..
echo "Please type your linuxproj database password and press [ENTER]: "
read dbpass
db=db_$(whoami)
sed -i.bak "s/YOURUSERNAME/$(whoami)/g" config/db.cfg
sed -i.bak "s/YOURPASSWORD/$dbpass/g" config/db.cfg
mysql -h linuxproj.ecs.soton.ac.uk -p$dbpass $db < install.sql
echo "RobPress has now been installed at http://linuxproj.ecs.soton.ac.uk/~$(whoami)/blog"
