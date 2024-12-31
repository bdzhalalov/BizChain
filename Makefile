build:
	docker-compose build

start:
	docker-compose up -d
	sleep 1
	docker-compose ps
	docker-compose run artisan migrate
	docker-compose run artisan db:seed

run:
	docker-compose up -d
	sleep 1
	docker-compose ps

test:
	docker-compose run artisan test

stop:
	docker-compose stop
