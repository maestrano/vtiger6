# Vtiger 6 by Maestrano
This version of Vtiger is customized to provide Single Sing-On and Connec!™ data sharing. By default, these options are not enabled so an instance of the application can be launched in a Docker container and be run as-is.
More information on [Maestrano SSO](https://maestrano.com) and [Connec!™ data sharing](https://maestrano.com/connec)

## Build Docker container with default Vtiger installation
`sudo docker build -t .`

## Activate Maestrano customisation on start (SSO and Connec!™ data sharing)
This is achieved by specifying Maestrano environment variables

```bash
docker run -it \
  -e "MNO_SSO_ENABLED=true" \
  -e "MNO_CONNEC_ENABLED=true" \
  -e "MNO_MAESTRANO_ENVIRONMENT=local" \
  -e "MNO_SERVER_HOSTNAME=vtiger6.app.dev.maestrano.io" \
  -e "MNO_API_KEY=a518c836057355ef5e5020b5db3b5d18b1f778bd80acb0dc3c6a086645f4aa71" \
  -e "MNO_API_SECRET=c1fb4e69-bb67-48b4-a1a6-c23734b348cc" \
  -e "MNO_APPLICATION_VERSION=mno-develop" \
  -e "MNO_POWER_UNITS=4" \
  --add-host application.maestrano.io:172.17.42.1 \
  --add-host connec.maestrano.io:172.17.42.1 \
  maestrano/vtiger6:latest
 ```

## Setup your development environment
Use `docker ps -a` to retrieve the name of the container that you just started, and launch this script:
```bash
ruby ./setup_devenv.rb <container name> <path>
```
It will retrieve the IP of your container, add it to your /etc/hosts file, and link the directory containing vTiger source files (stored inside of the container) to a local path.

Some files will be created in this directory during the installation phase. Consequently, you will have to :
- add the newly created files to your .git/info/excludes for them to be excluded from your next commits
- temporarily untrack the versionned files that have been modified during the installation:
```bash
git update-index --assume-unchanged <file>
```

## Docker Hub
The image can be pulled down from [Docker Hub](https://registry.hub.docker.com/u/maestrano/vtiger6/)
**maestrano/vtiger6:stable**: Production version

**maestrano/vtiger6:latest**: Develomment version
