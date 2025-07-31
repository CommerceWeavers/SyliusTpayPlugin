serve:
	@symfony serve --dir=tests/Application --daemon
server.start: serve
server.stop:
	@symfony server:stop --dir=tests/Application
mockoon.start:
	@npm run mockoon:start
mockoon.stop:
	@npm run mockoon:stop
frontend.install:
	@npm install
frontend.build:
	@npm run build
frontend.setup: frontend.install frontend.build
setup:
	@composer update
	@make frontend.setup
	@cd tests/Application && bin/console assets:install
	@cd tests/Application && bin/console doctrine:database:create --if-not-exists
	@cd tests/Application && bin/console doctrine:migrations:migrate -n
	@cd tests/Application && bin/console sylius:fixtures:load -n
	@cd tests/Application && APP_ENV=test bin/console doctrine:database:create --if-not-exists
	@cd tests/Application && APP_ENV=test bin/console doctrine:migrations:migrate -n
	@cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load -n
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
