serve:
	@symfony serve --dir=vendor/sylius/test-application/public --daemon
server.start: serve
server.stop:
	@symfony server:stop --dir=tests/Application
frontend.install:
	@cd vendor/sylius/test-application && yarn install
	vendor/bin/console assets:install
frontend.build:
	@cd vendor/sylius/test-application && yarn build
frontend.setup: frontend.install frontend.build
setup:
	@composer.phar update
	@make frontend.setup
	vendor/bin/console doctrine:database:create --if-not-exists
	vendor/bin/console doctrine:migration:migrate -n
	vendor/bin/console sylius:fixtures:load -n
	APP_ENV=test vendor/bin/console doctrine:database:create --if-not-exists
	APP_ENV=test vendor/bin/console doctrine:migration:migrate -n
	APP_ENV=test vendor/bin/console sylius:fixtures:load -n
ecs:
	@vendor/bin/ecs
ecs.fix:
	@vendor/bin/ecs --fix
phpstan:
	@vendor/bin/phpstan
phpunit:
	@make phpunit.api
	@make phpunit.e2e
	@make phpunit.unit
phpunit.api:
	@vendor/bin/phpunit --testsuite api
phpunit.e2e:
	@vendor/bin/phpunit --testsuite e2e
phpunit.unit:
	@vendor/bin/phpunit --testsuite unit
phpunit.contract_external:
	@vendor/bin/phpunit --testsuite contract_external
qa.static-analysis: ecs phpstan
qa.tests: phpunit
ci: qa.static-analysis qa.tests
