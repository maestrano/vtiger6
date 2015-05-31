FROM bchauvet/docker-lamp:latest
MAINTAINER Maestrano <it@maestrano.com>

# Add ansible configuration
ADD ansible/playbooks/ /etc/ansible/playbooks/
ADD ansible/templates/ /etc/ansible/templates/
ADD ansible/var/ /etc/ansible/var/
ADD ansible/var/settings.yml /etc/ansible/var/settings.yml
ADD ansible/hosts /etc/ansible/hosts

WORKDIR /etc/ansible

# Install Ansible
RUN apt-get -y update &&  \
    apt-get -y upgrade &&  \
    ansible-playbook /etc/ansible/playbooks/install_vtiger6.yml -c local &&  \
    apt-get clean purge -y python2.6 python2.6-minimal &&  \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
