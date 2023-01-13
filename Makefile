.PHONY: it
it: coding-standards static-code-analysis tests ## Runs the coding-standards, static-code-analysis, and tests targets

.PHONY: code-coverage
code-coverage: vendor ## Collects coverage from running tests with phpunit/phpunit
	vendor/bin/phpunit --coverage-text

.PHONY: coding-standards
coding-standards: vendor ## Fixes code style issues with friendsofphp/php-cs-fixer
	.phive/php-cs-fixer fix --diff --verbose

.PHONY: dependency-analysis
dependency-analysis: vendor ## Runs a dependency analysis with maglnet/composer-require-checker
	.phive/composer-require-checker check --config-file=$(shell pwd)/composer-require-checker.json

.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: phar
phar: ## Compiles a phar with humbug/box
	composer install --no-dev --no-progress
	.phive/box validate
	.phive/box build
	php twigcs.phar --version
	composer install --no-progress

.PHONY: static-code-analysis
static-code-analysis: vendor ## Runs a static code analysis with vimeo/psalm
	.phive/psalm --config=psalm.xml --clear-cache
	.phive/psalm --config=psalm.xml --show-info=false --stats

.PHONY: static-code-analysis-baseline
static-code-analysis-baseline: vendor ## Generates a baseline for static code analysis with vimeo/psalm
	.phive/psalm --config=psalm.xml --clear-cache
	.phive/psalm --config=psalm.xml --set-baseline=psalm-baseline.xml

.PHONY: tests
tests: vendor ## Runs unit and functional tests with phpunit/phpunit
	vendor/bin/phpunit --testsuite=unit
	vendor/bin/phpunit --testsuite=functional

vendor: composer.json
	composer install --no-progress
