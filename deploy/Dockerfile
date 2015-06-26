FROM bchauvet/docker-lamp:latest
MAINTAINER Maestrano <it@maestrano.com>

RUN apt-get -y update && apt-get -y upgrade 

# Add ansible configuration
ADD ansible /etc/ansible
WORKDIR /etc/ansible

# Install vTiger6
RUN ansible-playbook -i hosts site.yml

# Map MySQL and Apache volumes
VOLUME ["/etc/mysql", "/var/lib/mysql", "/etc/apache2", "/etc/php5", "/var/lib/vtigercrm"]