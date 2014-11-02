DynamicHosts
============

Automatic hosts file for OS X
-----------------------------

Setting up new entries in /etc/hosts is a pain.  Apache has mod_vhost_alias, but without some type of wildcard or dynamic DNS support, you still have to setup entries in hosts.

I want my hosts file to automagicly get updated when I create a new directory.

	/var/htdocs/develop.dev/someproject --> http://someproject.develop.dev

With a little help from Apple's [launched](https://developer.apple.com/library/mac/documentation/Darwin/Reference/ManPages/man8/launchd.8.html) this script can automate your /etc/hosts file.

#### Quick Start

Step 0:
Backup your original hosts file.

    sudo cp -p /etc/hosts /etc/hosts.org

Step 1:
Edit the file DynamicHosts.plist and change the two string entries that have "/usr/local/zend/apache2/htdocs/" to the path of your webroot.  You must includ the trailing "/".

Step 2:
Copy DynamicHosts.plist to /System/Library/LaunchAgents/

    sudo cp DynamicHosts.plist /System/Library/LaunchAgents/

Step 3:
Start the monitoring.

	sudo launchctl load /System/Library/LaunchAgents/DynamicHosts.plist
	
Test:
In your webroot create a new directory.  Inside that directory create a directory.

If you cat out your /etc/hosts your new direcotry/sub-directory should appear at the bottom of your hosts file as 127.0.0.1 sub-direcotry.directory

Step 4:
Using [mod_vhost_alias](http://httpd.apache.org/docs/2.2/mod/mod_vhost_alias.html) in Apache, it is now possible to create a development website just by createing directories.
