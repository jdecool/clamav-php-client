.PHONY: help cs stan tests

help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

cs: ## Run coding style analysis
	vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run

stan: ## Run static analysis
	vendor/bin/phpstan analyse src -c phpstan.neon -l max

tests: ## Run tests
	php vendor/bin/phpunit

vendor: composer.lock ## Install vendors
	composer install --prefer-dist --optimize-autoloader --classmap-authoritative
