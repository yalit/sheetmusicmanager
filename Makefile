SYMFONY = symfony
CONSOLE = symfony console
ENV     = dev

.DEFAULT_GOAL := help

.PHONY: help up down migrations migrate test-init tests install reset checkout

help: ## List available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

## Server
up: ## Bring the server up
	$(SYMFONY) serve -d
	docker compose up -d

down: ## Bring the server down
	$(SYMFONY) server:stop
	docker compose down

## DB
migrations: ## Create migrations
	$(CONSOLE) make:migration

migrate: ## Execute the pending migrations
	$(CONSOLE) d:m:m --no-interaction

## Test
test-init: ## Init test environment
	rm -f var/data_test.db
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=test
	$(CONSOLE) doctrine:fixtures:load --no-interaction --env=test

tests: ## Run tests
	bin/phpunit --testdox

## Setup
install: ## Full project setup (install, migrate, load fixtures)
	composer install
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=$(ENV)
	$(CONSOLE) doctrine:fixtures:load --no-interaction --env=$(ENV)
	mkdir -p public/uploads/sheets
	$(MAKE) copy-testfiles

reset: ## Drop and recreate the database, then reload fixtures
	rm -f var/data_dev.db
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=$(ENV)
	$(CONSOLE) doctrine:fixtures:load --no-interaction --env=$(ENV)
	$(MAKE) copy-testfiles

copy-testfiles: ## Copy sheet PDF test files to public/uploads/sheets
	rm -rf public/uploads/sheets/*
	cp tests/public/uploads/sheets/* public/uploads/sheets/

## Branch switching
checkout: ## Switch to a demo branch and reload fixtures (usage: make checkout BRANCH=epic/07-filters)
ifndef BRANCH
	@printf 'Usage: make checkout BRANCH=<branch>\n\nAvailable branches:\n'
	@printf '  epic/01-setup\n  epic/02-entities\n  epic/03-easyadmin\n  epic/04-authentication\n'
	@printf '  epic/07-filters\n  epic/08-actions\n  epic/09-custom-fields\n'
	@printf '  epic/10-dnd-reorder\n  epic/11-advanced\n  epic/13-presentation\n  main\n'
	@exit 1
endif
	git stash
	git checkout "$(BRANCH)"
	composer install --quiet
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --env=$(ENV)
	$(CONSOLE) doctrine:fixtures:load --no-interaction --env=$(ENV)
	$(MAKE) copy-testfiles
