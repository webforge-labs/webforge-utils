set positional-arguments

watch-phpunit *args='':
    find config/ src/ tests/ -type f | entr docker-compose exec --user=www-data php vendor/bin/phpunit "$@"

phpunit *args='':
    docker-compose exec --user=www-data php vendor/bin/phpunit "${@}"

phpstan *args='':
    docker-compose exec --user=www-data php vendor/bin/phpstan "${@}"

rector *args='':
    docker-compose exec --user=www-data php vendor/bin/rector "${@}"


regex := '/.*/'
show-deprecations *args='':
    docker-compose exec -e SYMFONY_DEPRECATIONS_HELPER='{{regex}}' --user=www-data php vendor/bin/phpunit "${@}"

composer *args='':
    docker-compose exec --user=www-data php composer "${@}"

fix:
    docker-compose exec --user=www-data php vendor/bin/php-cs-fixer fix -v

up:
    docker-compose up -d

watch-phpstan *args='':
    find config/ src/ tests/ -type f -name '*.php' | entr j phpstan "${@}"

release-interactive: fix phpunit phpstan
    release-it

