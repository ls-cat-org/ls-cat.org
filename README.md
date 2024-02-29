# Installation
Before running the installer script, you must first obtain a valid TLS certificate for ls-cat.org from the
LS-CAT administrator.

Once you have a valid TLS certificate, please install ls-cat.org by cloning this repository and running the
installer script as shown below:
```
cd /var/www
git clone <this repo>
cd <the clone>
sudo ./install.sh
```

# Contributing
Please do not do development directly off of the production environment. Instead, changes should be made and
tested on your local machine.

Good, working, tested changes should then be pushed from your local machine into the github repo, and then
pulled from the github repo onto the production server.

For development, you likely do not want to be forced to work directly under /var/www and do everything as root
on your local machine. Instead, it suffices to clone this repository under your home directory and to rsync
your clone to /var/www as follows:
```
sudo rsync /my/dir/ls-cat.org /var/www/ls-cat.org
```

# Updating the Production Environment
This repository currently runs on tomato.ls-cat.org, a virtual machine running on wheat.ls-cat.org (the public
APS-visible name for asn.ls-cat.org) under /var/www/ls-cat.org (owned by lsadmin).

To pull the latest version of the website into production from the git repository, please run the following
commands:
```
ssh lsadmin@tomato.ls-cat.org
cd /var/www/ls-cat.org
git pull
```

Note: For changes to HTML, CSS, Javascript, PHP, and any assets (images or audio files), apache need not be
restarted. However, when installing a new TLS certificate or making any changes to apache config files, please
run the following command after pulling down the latest changes:
```
systemctl restart apache2
```
