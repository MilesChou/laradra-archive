#!/usr/bin/make -f

CLIENT_ID := some-client
CLIENT_SECRET := some-secret
CLIENT_CALLBACK := http://web.localhost:8080/rp/callback

.PHONY: all clean clean-all check test coverage

# ---------------------------------------------------------------------

all: test

clean:
	rm -rf ./build

clean-all: clean
	rm -rf ./vendor
	rm -rf ./composer.lock

check:
	php vendor/bin/phpcs

test: clean check
	phpdbg -qrr vendor/bin/phpunit

coverage: test
	@if [ "`uname`" = "Darwin" ]; then open build/coverage/index.html; fi

log:
	tail -f storage/logs/*.log

up:
	docker-compose up -d
	docker-compose logs -f

down:
	docker-compose down -v

setup:
	docker-compose exec hydra hydra --endpoint http://127.0.0.1:4445/ clients --skip-tls-verify \
		delete ${CLIENT_ID}
	docker-compose exec hydra hydra --endpoint http://127.0.0.1:4445/ clients --skip-tls-verify \
		create \
		--id ${CLIENT_ID} \
		--secret ${CLIENT_SECRET} \
		--grant-types authorization_code,refresh_token,client_credential \
		--response-types code \
		--scope openid,offline_access \
		--token-endpoint-auth-method client_secret_basic \
		--callbacks ${CLIENT_CALLBACK}
