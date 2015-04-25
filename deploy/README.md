# Build vTiger6 application container

## Build Docker container with
`sudo docker build -t "vtiger-6.2.0" .`

## Start Docker container
`sudo docker run -t -i --name=vtiger6_container vtiger-6.2.0 /bin/bash`

## Retrieve container details (IP address...)
`sudo docker inspect vtiger6_container`

And then access the container with http://[IP_ADDRESS] to check vTiger is running

# Docker cheat-sheet

## List docker containers
`sudo docker ps`

## Stop and remove all containers
`sudo docker stop $(docker ps -a -q)`
`sudo docker rm $(docker ps -a -q)`