# Build vTiger6 application container

## Build Docker container with default vTiger6 installation
`sudo docker build -t .`

## Start Docker container
`sudo docker run -t -i --name=vtiger6_container maestrano:vtiger-6.2.0`

## Retrieve container details (IP address...)
`sudo docker inspect vtiger6_container`

And then access the container with http://[IP_ADDRESS] to check vTiger is running

## Activate Maestrano customisation on start (SSO and Connec! data sharing)
This is achieved by specifying environment variables

```bash
docker run -it \
  -e "MNO_SSO_ENABLED=true" \
  -e "MNO_CONNEC_ENABLED=true" \
  -e "MNO_MAESTRANO_ENVIRONMENT=local" \
  -e "MNO_SERVER_HOSTNAME=vtiger6.app.dev.maestrano.io" \
  -e "MNO_API_KEY=a518c836057355ef5e5020b5db3b5d18b1f778bd80acb0dc3c6a086645f4aa71" \
  -e "MNO_API_SECRET=c1fb4e69-bb67-48b4-a1a6-c23734b348cc" \
  --add-host application.maestrano.io:172.17.42.1 \
  --add-host connec.maestrano.io:172.17.42.1 \
  --name=mcube-aaa bchauvet/vtiger6
 ```

# Docker cheat-sheet

## List docker containers
`sudo docker ps`

## Stop and remove all containers
sudo docker stop $(docker ps -a -q)
sudo docker rm -v $(docker ps -a -q)

## Remove untagged images
sudo docker images -q --filter "dangling=true" | xargs docker rmi
