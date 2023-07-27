# Updating ls-cat.org

This repository currently runs on tomato.ls-cat.org, a virtual machine running on wheat.ls-cat.org (the public APS-visible name for asn.ls-cat.org) under /var/www/ls-cat.org (owned by lsadmin).

After committing an update in your local copy of the repository and pushing it to the master branch (git push), please run the following commands:

ssh lsadmin@tomato.ls-cat.org
cd /var/www/ls-cat.org
git pull

Note: For changes to HTML, CSS, Javascript, PHP, and any assets (images or audio files), apache need not be restarted. For the rare cases where a restart is necessary, please run the following command after pulling down the latest changes:

systemctl restart apache2
