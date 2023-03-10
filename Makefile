# @see https://www.thapaliya.com/en/writings/well-documented-makefiles/
.DEFAULT_GOAL := help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "Usage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-40s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

PHP_BIN ?= 'php'

.PHONY: build
build: ## Build docker-ws.phar
	$(PHP_BIN) -d phar.readonly=false bin/build -v$(V)





