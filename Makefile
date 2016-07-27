usage:
	@echo "make composer"

composer:
	@wget https://getcomposer.org/download/1.2.0/composer.phar

install:
	@php composer.phar install

	@cp vendor/nicklasw/pkm-go-api/.env.example vendor/nicklasw/pkm-go-api/.env

server:
	@php -S localhost:8080 -t public

dev-assets:
	@npm run dev

prod-assets: clean
	@npm run prod

clean:
	@rm -rf public/build
