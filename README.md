# docker_ws
Generate workspace docker for **develop**
### _don't use this configuration on production_

```curl -LO https://github.com/Enjoyzz/docker_ws/releases/latest/download/docker-ws.phar```

Run application
```shell
php docker-ws.phar configure
php docker-ws.phar configure --force
php docker-ws.phar configure --php "^8.1"
```

Get help information
```shell
php docker-ws.phar configure --help
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

.PHONY: configure-docker
configure-docker: ## Configure docker workspace.
	@$(if $(PHAR_NOT_EXIST), curl -LO https://github.com/Enjoyzz/docker_ws/releases/latest/download/docker-ws.phar)
	@php ./docker-ws.phar configure --php "^8.1"
```
and run for show all targets (commands)
```shell
make
```

or
```shell
make -f .docker/Makefile
```

For run make under windows need install [GnuWin32](https://gnuwin32.sourceforge.net/) packages: 
- [Make](https://gnuwin32.sourceforge.net/packages/make.htm)
- [CoreUtils](https://gnuwin32.sourceforge.net/packages/coreutils.htm)
- [Gawk](https://gnuwin32.sourceforge.net/packages/gawk.htm)
