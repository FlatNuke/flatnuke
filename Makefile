# Makefile for Flatnuke building
#

# Flatnuke version
VER = "4.0.0"
DEV ?=
# Current user name
USER = $(shell whoami)
# Current date
DATE = $(shell date +%Y%m%d)
# Filename for public release
FILEDIST = flatnuke$(DEV)-$(VER).tar.gz
# Filename for snapshot release
FILE = flatnuke$(DEV)-$(VER)-$(DATE).tar.gz
# Directory where to create the package
FNUSER = $(shell cat ~/.flatnukerc)
# Directory of the webserver
WEBDIR=/var/www

##
## This option creates a daily snapshot of Flatnuke package
##
snapshot:
	@cd ..;\
	rm -fr $(FILE) $(FILE).md5;\
	cp -dpR flatnuke$(DEV) flatnuke$(DEV)-$(VER);\
	find flatnuke$(DEV)-$(VER) -name CVS -exec rm -fr \{\} \; 2>/dev/null;\
	find flatnuke$(DEV)-$(VER) -name .git -exec rm -fr \{\} \; 2>/dev/null;\
	find flatnuke$(DEV)-$(VER) -type f \( -iname "\.*" ! -iname "\.htaccess" \) -exec rm -fr \{\} \; 2>/dev/null;\
	rm flatnuke$(DEV)-$(VER)/Makefile;\
	tar vfzc $(FILE) flatnuke$(DEV)-$(VER) > /dev/null;\
	rm -fr flatnuke$(DEV)-$(VER);\
	md5sum $(FILE) | cut -d" " -f1 > $(FILE).md5;\
	scp $(FILE) $(FILE).md5 $(FNUSER);\
	rm -fr $(FILE) $(FILE).md5;

##
## This option creates the official Flatnuke package to be distributed
##
dist:
	@cd ..;\
	rm -fr $(FILEDIST);\
	cp -dpR flatnuke$(DEV) flatnuke$(DEV)-$(VER);\
	find flatnuke$(DEV)-$(VER) -name CVS -exec rm -fr \{\} \; 2>/dev/null;\
	find flatnuke$(DEV)-$(VER) -name .git -exec rm -fr \{\} \; 2>/dev/null;\
	find flatnuke$(DEV)-$(VER) -type f \( -iname "\.*" ! -iname "\.htaccess" \) -exec rm -fr \{\} \; 2>/dev/null;\
	rm flatnuke$(DEV)-$(VER)/Makefile;\
	tar vfzc $(FILEDIST) flatnuke$(DEV)-$(VER) > /dev/null;\
	rm -fr flatnuke$(DEV)-$(VER);\
	scp $(FILEDIST) $(FNUSER);\
	rm -fr $(FILEDIST)

##
## This option builds local Flatnuke test environment
## (usually installed in /var/www directory)
##
webtest:
	@cd ..;\
	rm -fr $(WEBDIR)/flatnuke$(DEV)-$(VER);\
	cp -dpR flatnuke$(DEV) flatnuke$(DEV)-$(VER);\
	find flatnuke$(DEV)-$(VER) -name CVS -exec rm -fr \{\} \; 2>/dev/null;\
	find flatnuke$(DEV)-$(VER) -name .git -exec rm -fr \{\} \; 2>/dev/null;\
	find flatnuke$(DEV)-$(VER) -type f \( -iname "\.*" ! -iname "\.htaccess" \) -exec rm -fr \{\} \; 2>/dev/null;\
	rm flatnuke$(DEV)-$(VER)/Makefile;\
	mv flatnuke$(DEV)-$(VER) $(WEBDIR);\
	chown -R $(USER):$(USER) $(WEBDIR)/flatnuke$(DEV)-$(VER);

##
## This option cleans local Flatnuke test environment
## (usually installed in /var/www directory)
##
cleantest:
	@su -c "rm -fr $(WEBDIR)/flatnuke$(DEV)-$(VER)"

##
## This option fixes all files/directories permissions'
## to make them 644 and 755.
##
fixperms:
	@echo "----------------------";\
	echo "List of files to fix :";\
	echo "----------------------";\
	find ./ -type f -ls | grep -v drwx | grep -v rw-r--r--;\
	find ./ -type d -ls | grep  -v drwxr-xr-x;\
	echo "----------------------";\
	echo "Fixing ....";\
	find ./ -type f | grep -v drwx | grep -v rw-r--r-- | xargs chmod 644;\
	find ./ -type d | xargs chmod 755;\
	echo "----------------------";\
	echo "Now check manually :";\
	echo "----------------------";\
	find ./ -type f -ls | grep -v drwx | grep -v rw-r--r--;\
	find ./ -type d -ls | grep -v drwxr-xr-x;
