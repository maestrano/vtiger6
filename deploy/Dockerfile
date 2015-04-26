FROM bchauvet:nex-prototyping
MAINTAINER Maestrano <it@maestrano.com>

RUN apt-get update

# Add playbooks
ADD ansible/playbooks/ /etc/ansible/playbooks/
ADD ansible/templates/ /etc/ansible/templates/
ADD ansible/var/ /etc/ansible/var/
ADD ansible/var/default-settings.yml /etc/ansible/var/settings.yml
ADD ansible/inventory /etc/ansible/hosts

WORKDIR /etc/ansible

# Run Ansible playbooks
# RUN ansible-playbook /etc/ansible/playbooks/mysql.yml -c local
# RUN ansible-playbook /etc/ansible/playbooks/apache.yml -c local
RUN ansible-playbook /etc/ansible/playbooks/vtiger6.yml -c local

# Start services
# CMD service mysql start && tail -F /var/log/mysql/error.log
# CMD service apache2 start && tail -F /var/log/apache2/error.log

# Expose port 22 and 80
# EXPOSE 22 80
