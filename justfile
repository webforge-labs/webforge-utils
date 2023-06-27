set positional-arguments

watch-phpunit *args='':
    find config/ src/ tests/ -type f | entr docker-compose exec --user=www-data php vendor/bin/phpunit "$@"

phpunit *args='':
    docker-compose exec --user=www-data php vendor/bin/phpunit "${@}"

regex := '/.*/'
show-deprecations *args='':
    docker-compose exec -e SYMFONY_DEPRECATIONS_HELPER='{{regex}}' --user=www-data php vendor/bin/phpunit "${@}"

composer *args='':
    docker-compose exec --user=www-data php composer "${@}"

up:
    docker-compose up -d
