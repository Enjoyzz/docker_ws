##@ Docker compose commands

export __UNAME = $(shell id -un)
export __UID = $(shell id -u)
export __GID = $(shell id -g)

# Enable buildkit for docker and docker-compose by default for every environment.
# For specific environments (e.g. MacBook with Apple Silicon M1 CPU) it should be turned off to work stable
export COMPOSE_DOCKER_CLI_BUILD ?= 1
export DOCKER_BUILDKIT ?= 1

DOCKER_PATH = $(abspath $(dir $(abspath $(lastword $(MAKEFILE_LIST)))))
PROJECT_NAME ?= $(notdir  $(abspath $(DOCKER_PATH)/..))
DOCKER_COMPOSE_YAML ?= $(DOCKER_PATH)/docker-compose.yml
DOCKER_COMPOSE = docker-compose \
	-p $(PROJECT_NAME) \
	--file $(DOCKER_COMPOSE_YAML) \
	--env-file $(DOCKER_PATH)/.env

# OS is a defined variable for WIN systems, so "uname" will not be executed
OS?=$(shell uname)
# Values of OS:
#   Windows => Windows_NT
#   Mac 	=> Darwin
#   Linux 	=> Linux
ifeq ($(OS),Windows_NT)
	# Windows requires the .exe extension, otherwise the entry is ignored
	# @see https://stackoverflow.com/a/60318554/413531
    SHELL := bash.exe
	# Export MSYS_NO_PATHCONV=1 as environment variable to avoid automatic path conversion
	# (the export does only apply locally to `make` and the scripts that are invoked,
	# it does not affect the global environment)
    # @see http://www.pascallandau.com/blog/setting-up-git-bash-mingw-msys2-on-windows/#fixing-the-path-conversion-issue-for-mingw-msys2
	export MSYS_NO_PATHCONV=1
endif

ifeq ("$(wildcard $(DOCKER_COMPOSE_YAML))","")
	ERROR_DOCKER_COMPOSE_YAML = "\033[0;31mError! The file $(DOCKER_COMPOSE_YAML) not exists\033[0m"
endif

# @see https://www.thapaliya.com/en/writings/well-documented-makefiles/
.DEFAULT_GOAL := help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "Usage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-40s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

.PHONY: check/docker-compose-file
check/docker-compose-file:
	@$(if $(ERROR_DOCKER_COMPOSE_YAML), (echo $(ERROR_DOCKER_COMPOSE_YAML); exit 1))

.PHONY: check/all-checks
check/all-checks: check/docker-compose-file

.PHONY: debug/variables
debug/variables:
	@echo OS = ${OS}
	@echo SHELL = ${SHELL}
	@echo DOCKER_PATH = ${DOCKER_PATH}
	@echo PROJECT_NAME = ${PROJECT_NAME}
	@echo DOCKER_COMPOSE_YAML = ${DOCKER_COMPOSE_YAML}
	@echo DOCKER_COMPOSE = ${DOCKER_COMPOSE}

.PHONY: docker-init
docker-init: check/all-checks
	@cp $(DOCKER_PATH)/.env.docker $(DOCKER_PATH)/.env

.PHONY: docker-up
docker-up: docker-init ## Start all docker containers. To only start one container, use SERVICE=<service>
	@$(DOCKER_COMPOSE) up -d $(SERVICE)

.PHONY: docker-start
docker-start: docker-up

.PHONY: docker-down
docker-down: docker-init ## Stop all docker containers.
	@$(DOCKER_COMPOSE) down --remove-orphans

.PHONY: docker-stop
docker-stop: docker-down

.PHONY: docker-restart
docker-restart: docker-down  ## Restart all docker containers.
	@$(MAKE) -s docker-up

.PHONY: docker-build
docker-build: docker-init ## Build all docker images. Build a specific image by providing the service name via: make docker-build SERVICE=<service>
	@$(DOCKER_COMPOSE) build --parallel $(SERVICE) && \
	$(DOCKER_COMPOSE) up -d --force-recreate $(SERVICE)

.PHONY: docker-build-from-scratch
docker-build-from-scratch: docker-init ## Build all docker images from scratch, without cache etc. Build a specific image by providing the service name via: make docker-build SERVICE=<service>
	@$(DOCKER_COMPOSE) rm -fs $(SERVICE) && \
	$(DOCKER_COMPOSE) build --pull --no-cache --parallel $(SERVICE) && \
	$(DOCKER_COMPOSE) up -d --force-recreate $(SERVICE)

.PHONY: docker-clean
docker-clean: ## Remove the .env file for docker
	@rm -f $(DOCKER_PATH)/.env

.PHONY: docker-prune
docker-prune: ## Remove unused docker resources via 'docker system prune -a -f --volumes'
	@docker system prune -a -f --volumes
