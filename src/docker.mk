

DEFAULT_GOAL := help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-27s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)


.docker/.env:
	cp ./.docker/.env.docker ./.docker/.env

.PHONY: docker-init
docker-init: .docker/.env ## Make sure the .env file exists for docker

.PHONY: docker-up
docker-up: docker-init set-user-environments ## Start all docker containers. To only start one container, use CONTAINER=<service>
	docker-compose --file ./.docker/docker-compose.yml  up --build --remove-orphans -d

.PHONY: docker-down
docker-down: docker-init ## Stop all docker containers. To only stop one container, use CONTAINER=<service>
	docker-compose --file ./.docker/docker-compose.yml  down
