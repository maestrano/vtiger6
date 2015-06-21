#!/bin/bash

# Configure vTiger6 Connec! configuration using Ansible
# This script sets the API keys and applications options such as memory allocated

# Usage: configure.sh maestrano_environment sso_enabled connec_enabled server_hostname api_key api_secret
# e.g. configure.sh production true true abc.mcube.co 9230083896350120 92814-34a43b-2357-23433

maestrano_environment=$1
sso_enabled=$2
connec_enabled=$3
server_hostname=$4
api_key=$5
api_secret=$6

extra_vars="{\"sso_enabled\": \"$sso_enabled\", \"connec_enabled\": \"$connec_enabled\", \"maestrano_environment\": \"$maestrano_environment\", \"server_hostname\": \"$server_hostname\", \"api_key\": \"$api_key\", \"api_secret\": \"$api_secret\"}"
ansible-playbook /etc/ansible/playbooks/configure_vtiger6.yml -c local --extra-vars="${extra_vars}"
