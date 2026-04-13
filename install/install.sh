#!/bin/bash
set -ue

usage() {
    echo "install.sh [--update]"
    echo "  --update : For development/debugging use only. If set, skip" \
	 "    fetching dependencies and certificates, and do not restart the" \
	 "    server. Copy the website assets to /var/www, and nothing else." \
	 "    Do not use this option for updating the apache config file."
    exit 1
}

error() {
    echo $@ >&2
}

abort() {
    echo "$@" >&2
    exit $fail_status
}

# update SRC_DIR DEST_DIR
# return 0 if no changes, 1 if any files were updated
update() {
    status=0
    cd $1
    rsync -ruv . $2 | \
	awk '!/(sending |sent |total size is |$^)/{print updating $1;}' \
	    >/tmp/changes.txt
    [ $(wc -l /tmp/changes.txt) -eq 0 ]
    cd - >/dev/null
}

incremental=
while [ $# -gt 0 ]; do
    case $1 in
	--update|--incremental)
	    incremental=true;
	    shift;
	    ;;
	*)
	    
	    break
	    ;;
    esac
done

if [ $UID -ne 0 ]; then
    error "Error: Please run this script with elevated privileges using sudo."
    error
    exit 1
fi

if [ $PWD != "/var/www/ls-cat.org/install" ]; then
    cd ..
    rm -rf /var/www/ls-car.org.new /var/www/ls-cat.org.old
    cp -Rf . /var/www/ls-cat.org.new
    [ ! -e /var/www/ls-cat.org ] || \
	mv -f /var/www/ls-cat.org /var/www/ls-cat.org.old
    mv -f /var/www/ls-cat.org.new /var/www/ls-cat.org
    cd - >/dev/null
    
    if [ "$incremental" = true ]; then
	cd apache-conf
	cfgdir=/etc/apache2/sites-available
	conf_update=
	for site_config in *; do
	    if [ ! -f $cfgdir/$site_config ] ||
	       [ $site_config -nt $cfgdir/$site_config ]; then
		
		echo "$site_config was changed since the last update," \
		     "installing to $cfgdir now"
		cp -f $site_config $cfgdir
		a2ensite $site_config
		conf_update=true
	    fi
	done
	cd - >/dev/null
	
	if [ "$conf_update" = true ]; then
	    echo "Restarting apache2 to apply config changes now."
	    systemctl restart apache2
	fi
	echo "Update succeeded."
	exit
    fi
fi

# Install apache2 w/ the prerequisite modules and their dependencies.
apt update
apt install apache2 libapache2-mod-php libapache2-mod-auth-openidc
php_version=$(ls -l /etc/apache2/mods-available/ | \
		  awk '/php[0-9]+/{print $9;}' | \
		  sed 's/php//g;s/\.conf//g;s/\.load//g' | \
		  sort -Vr | head -1)
echo "Note: using php version $php_version"
apt install php$php_version php$php_version-gd

# Enable the prerequisite modules.
a2enmod ssl \
	auth_openidc \
	headers \
	include \
	php$php_version \
	proxy \
	proxy_http \
	proxy_connect \
	rewrite

cert='/etc/ssl/certs/ls-cat.org.crt'
key='/etc/ssl/private/ls-cat.org.key'
if [ ! -f $cert ] || [ ! -f $key ]; then
    error "Error: The TLS certificate has not been properly installed." \
	  "Please obtain the certificate, private key file, and certificate" \
	  "chain, then install them to the following locations, respectively:"
    error
    error "  $cert"
    error "  $key"
    error
    exit 1
fi

# Install the website and enable it.
cd apache-conf
for site_config in *; do
    cp -f $site_config /etc/apache2/sites-available/
    a2ensite $site_config
done
cd - >/dev/null

systemctl restart apache2 ||  \
    abort "Error: ls-cat.org has been installed, but apache2 is not running." \
	  "Please see the error messages above for more information."

echo
echo "ls-cat.org has been successfully installed and is now running" \
     "under apache2. Please open https://ls-cat.org in your browser to" \
     "confirm the TLS certificate is valid and that the website's full" \
     "functionality works as intended."
