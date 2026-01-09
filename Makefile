.PHONY: migrations

#Aliases
SYMFONY= symfony
CONSOLE= ${SYMFONY} console


help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Server ————————————————————————————————————————————————————————————
up: ## Bring the server up
	${SYMFONY} serve -d

down: ## Bring the server down
	${SYMFONY} server:stop

## —— DB ————————————————————————————————————————————————————————————
migrations: ## Create migrations
	${CONSOLE} make:migration

migrate: ## Execute the pending migrations
	${CONSOLE} d:m:m
