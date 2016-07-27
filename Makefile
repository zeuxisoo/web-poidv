usage:
	@echo "make composer"

composer:
	@wget https://getcomposer.org/download/1.2.0/composer.phar

install:
	php composer.phar install

server:
	php -S localhost:8080 -t public
