FROM maestrano/docker-lamp:latest
MAINTAINER Maestrano <it@maestrano.com>

RUN apt-get -y update && apt-get -y upgrade 

# Add ansible configuration
ADD ansible /etc/ansible
WORKDIR /etc/ansible

# Install Vtiger
RUN ansible-playbook -vvvv -i hosts site.yml

# Map Vtiger mutable volumes
VOLUME ["/var/lib/vtigercrm"]