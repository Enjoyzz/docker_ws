# docker_ws
Generate workspace docker for **develop**
### _don't use this configuration on production_

Run application
```shell
./vendor/bin/docker-ws configure
```

Get help information
```shell
./vendor/bin/docker-ws configure --help
```

Linux
```shell
__UNAME=$(id -un) __UID=$(id -u) __GID=$(id -g) docker-compose --file .docker/docker-compose.yml  up --build --remove-orphans -d
```
or 
```shell
export __UNAME=$(id -un) __UID=$(id -u) __GID=$(id -g) 
docker-compose --file .docker/docker-compose.yml  up --build --remove-orphans -d
```

add to root Makefile 
```makefile
-include .docker/Makefile
```
and run (show all targets (commands))
```shell
make
```

or
```shell
make -f .docker/Makefile
```