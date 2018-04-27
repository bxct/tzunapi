#!/bin/bash

date > /etc/vagrant_box_build_time

SSH_USER=${SSH_USERNAME:-vagrant}
SSH_USER_HOME=${SSH_USER_HOME:-/home/${SSH_USER}}
VAGRANT_INSECURE_KEY="ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCvC+WK1WlrZo707J0gtOZh2b3CcKcsD8Hsrjj6vfGS6L+5LWk2wzOkNZCt+dOO2y/OmhCTqLbXqZpbOSFugY2x0HgQD2Vo7BNxJSKpwzuaPuiOhcMMnRN86lSZh7LJwGl51yHGzrCD7iRFNL50bYLTGV9I1vwwlDCBWg+5El9qT8yq3RTeS8+EC8zyEnlOu97uw66Zly/QmEDKnDe3cB+RjNeZM+pXgBP3LkWqCeM7jAZ8C0KgU53dcmJnictXKEXzk/tNSVs6ao2Cn5v99jDrsBvT/J2CcLmyIiok9BiFfGWNLtZO/zEt/BF5Nmvq6wNdiGorBxEVkdomX9mcIPEN parallels@ubuntu"

# Packer passes boolean user variables through as '1', but this might change in
# the future, so also check for 'true'.
if [ "$INSTALL_VAGRANT_KEY" = "true" ] || [ "$INSTALL_VAGRANT_KEY" = "1" ]; then
    # Create Vagrant user (if not already present)
    if ! id -u $SSH_USER >/dev/null 2>&1; then
        echo "==> Creating $SSH_USER user"
        /usr/sbin/groupadd $SSH_USER
        /usr/sbin/useradd $SSH_USER -g $SSH_USER -G sudo -d $SSH_USER_HOME --create-home
        echo "${SSH_USER}:${SSH_USER}" | chpasswd
    fi

    # Set up sudo
    echo "==> Giving ${SSH_USER} sudo powers"
    echo "${SSH_USER}        ALL=(ALL)       NOPASSWD: ALL" >> /etc/sudoers

    echo "==> Installing vagrant key"
    mkdir $SSH_USER_HOME/.ssh
    chmod 700 $SSH_USER_HOME/.ssh
    cd $SSH_USER_HOME/.ssh

    # https://raw.githubusercontent.com/mitchellh/vagrant/master/keys/vagrant.pub
    echo "${VAGRANT_INSECURE_KEY}" > $SSH_USER_HOME/.ssh/authorized_keys
    chmod 600 $SSH_USER_HOME/.ssh/authorized_keys
    chown -R $SSH_USER:$SSH_USER $SSH_USER_HOME/.ssh
fi