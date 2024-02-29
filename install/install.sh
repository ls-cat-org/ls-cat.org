#!/bin/bash
error() {
    echo $@ >&2
}

fail=
fail_status=0
try() {
    echo ">$@"
    $@
    status=$?
    if [ $status -ne 0 ]; then
	error "\"$@\" failed, see log above for details"
	fail_status=$?
    fi
    echo
    return $status
}

abort() {
    echo "Error: $@" >&2
    exit $fail_status
}

no_go=
if [ $PWD != "/var/www/ls-cat.org/install" ]; then
    error "Error: Please clone this repo under /var/www and install from" \
	  "there. To do so, please run the following commands:"
    error
    error "cd /var/www"
    error "sudo git clone git@github.com:ls-cat-org/ls-cat.org.git"
    error "cd ls-cat.org/install"
    error "sudo ./install.sh"
    error
    no_go=true
fi

if [ $UID -ne 0 ]; then
    error "Error: Please run this script with elevated privileges using sudo."
    error
    no_go=true
fi

cert='/etc/ssl/certs/ls-cat.org.crt'
key='/etc/ssl/private/ls-cat.org.key'
if [ ! -f $cert ] || [ ! -f $key ]; then
    error "Error: The TLS certificate has not been properly installed. Please" \
	  "obtain the certificate, private key file, and certificate chain," \
	  "then install them to the following locations, respectively:"
    error
    error "  $cert"
    error "  $key"
    error
    no_go=true
fi

if [ -n "$no_go" ]; then
    exit 1
fi

fail=
try apt update
try apt install libapache2-mod-php libapache2-mod-python
php_version=$(ls -l /etc/apache2/mods-available/ | awk '/php[0-9]+/{print $9;}' |
		  sed 's/php//g;s/\.conf//g;s/\.load//g' | sort -Vr | head -1)
echo "Note: using php version $php_version"
try a2enmod ssl
try a2enmod headers
try a2enmod include
try a2enmod php$php_version
try a2enmod proxy
try a2enmod proxy_http
try a2enmod python
try a2enmod rewrite
if [ -n "$fail" ]; then
    abort "Error: failed to fetch and install all of the necessary" \
	  "dependencies. Please see the error messages above for more" \
	  "information."
fi
    

cd apache-conf
for site_config in *; do
    try cp -f $site_config /etc/apache2/sites-available/
    try a2ensite $site_config
    try systemctl restart apache2
done
cd - >/dev/null
if [ -n "$fail" ]; then
    abort "Error: ls-cat.org has been installed, but apache2 is not running." \
	  "Please see the error messages above for more information."
fi

echo
echo "ls-cat.org has been successfully installed and is now running" \
     "under apache2. Please open https://ls-cat.org in your browser to" \
     "confirm the TLS certificate is valid and that the website's full" \
     "functionality works as intended."
