FROM debian:latest
MAINTAINER Maestrano <it@maestrano.com>

# Install Ansible
RUN apt-get -y update &&  \
    apt-get -y upgrade &&  \
    apt-get -y --no-install-recommends install python-yaml \
               python-jinja2 python-httplib2 python-keyczar \
               python-paramiko python-setuptools \
               python-pkg-resources git python-pip &&  \
    mkdir -p /etc/ansible/ &&  \
    pip install ansible

# Add ansible configuration
ADD ansible/playbooks/ /etc/ansible/playbooks/
ADD ansible/templates/ /etc/ansible/templates/
ADD ansible/var/ /etc/ansible/var/
ADD ansible/var/settings.yml /etc/ansible/var/settings.yml
ADD ansible/hosts /etc/ansible/hosts

WORKDIR /etc/ansible

# Run Ansible playbooks to install vTiger6
RUN ansible-playbook /etc/ansible/playbooks/mysql.yml -c local &&  \
    ansible-playbook /etc/ansible/playbooks/apache.yml -c local &&  \
    ansible-playbook /etc/ansible/playbooks/install_vtiger6.yml -c local

# Clean up
RUN apt-get purge --auto-remove -y python2.6 python2.6-minimal &&  \
    rm -rf /var/lib/apt/lists/*

# Expose port 22 and 80
EXPOSE 22 80

# Startup script to run mysql and apache
RUN echo "#!/bin/bash\n/etc/init.d/mysql start\n/etc/init.d/apache2 start\n/bin/bash" > /root/init.sh && \
    chmod 755 /root/init.sh

ENTRYPOINT ["/root/init.sh"]