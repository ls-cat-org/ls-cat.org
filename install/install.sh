#!/bin/bash
error() {
    echo $@ >&2
}

no_go=
if [ $PWD != "/var/www/ls-cat.org/install" ]; then
    error "Error: Please clone this repo under /var/www and install from" \
	  "there. To do so, please run the following commands:"
    error
    error "cd /var/www"
    error "sudo git clone git@github.com:ls-cat-org/ls-cat.org.git"
    error "cd ls-cat.org"
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
cd apache-conf
for site_config in *; do
    cp -f $site_config /etc/apache2/sites-available/
    a2ensite $site_config
    systemctl restart apache2 || fail=true
done
cd -

if [ -n "$fail" ]; then
    error "Error: ls-cat.org has been installed, but apache2 is not running." \
	  "Please see the error messages above for more information."
else
    echo "ls-cat.org has been successfully installed and is now running" \
	 "under apache2. Please open https://ls-cat.org in your browser to" \
	 "confirm the TLS certificate is valid and that the website's full" \
	 "functionality works as intended."
fi
