set positional-arguments

watch-phpunit *args='':
    find config/ src/ tests/ -type f | entr docker-compose exec --user=www-data php vendor/bin/phpunit "$@"

phpunit *args='':
    docker-compose exec --user=www-data php vendor/bin/phpunit "$@"

up:
    docker-compose up -d