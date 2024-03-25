# Installation
Before running the installer script, you must first obtain a valid TLS certificate for ls-cat.org from the
LS-CAT administrator.

Once you have a valid TLS certificate, please install ls-cat.org by cloning this repository and running the
installer script as shown below:
```
cd /var/www
git clone <this repo>
cd <the clone>/install
sudo ./install.sh
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
sudo systemctl restart apache2
```

# Contributing
Please read the following document for LS-CAT's rules before making a commit:
https://docs.google.com/document/d/19kHNF1ftZzzbU9xP23YgZaCSBp3vcEQdH6kL8HE0L4k/edit?usp=drive_link

Good, working, tested changes should then be pushed from the local machine into the github repo, and then
pulled from the github repo onto the production server.

For development, install.sh copies assets to /var/www and installs the necessary dependencies. Please run the
following command to perform first-time setup:

```
cd install
sudo ./install.sh
```

Upon making changes, apply them to the running apache2 server by running update.sh as follows:

```
cd install
sudo ./update.sh
```
