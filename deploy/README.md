# Build vTiger6 application container

## Build Docker container with default vTiger6 installation
`sudo docker build -t "maestrano:vtiger-6.2.0" .`

## Start Docker container
`sudo docker run -t -i --add-host application.maestrano.io:172.17.42.1 --name=vtiger6_container maestrano:vtiger-6.2.0`

--add-host application.maestrano.io:172.17.42.1

## Retrieve container details (IP address...)
`sudo docker inspect vtiger6_container`

And then access the container with http://[IP_ADDRESS] to check vTiger is running

## Apply the maestrano patch (SSO and Connec! data sharing)
ansible-playbook /etc/ansible/playbooks/configure_vtiger6.yml -c local --extra-vars='{"sso_enabled": "true", "connec_enabled": "true", "maestrano_environment": "local", "server_hostname": "vtiger6.app.dev.maestrano.io", "api_key": "94cd736d57484d5e42ed1a194de0af7508b1163a35909ab7fe3b713a90816661", "api_secret": "baa59b5b-cb6b-4e4b-8682-b3966877840e"}'

### Maestrano configuration variables:
 - sso_enabled
 - connec_enabled
 - maestrano_environment (production, uat, local)
 - server_hostname (cube uid)
 - api_key
 - api_secret
 - innodb_additional_mem_pool_size (4M, 8M, 16M) based on container allocate PU
 - innodb_buffer_pool_size (64M, 128M, 256M) based on container allocate PU
 - php_memory_limit (64M, 128M, 256M) based on container allocate PU

## TODO
Map container mysql data and vtiger directory as volumes and do backups:
-v /path/in/host:/var/lib/mysql -v /path/in/host:/var/lib/vtiger/webapp


# Docker cheat-sheet

## List docker containers
`sudo docker ps`

## Stop and remove all containers
sudo docker stop $(docker ps -a -q)
sudo docker rm $(docker ps -a -q)

## Remove untagged images
sudo docker images -q --filter "dangling=true" | xargs docker rmi
