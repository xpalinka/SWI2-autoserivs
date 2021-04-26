Production at: https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/SWI2-autoserivs/Adam-slim/public/home \
Testing at: https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/test/SWI2-autoserivs/Adam-slim/public/login


# Local installation
php composer install \
php -S localhost:8080 -t public \

# Running cypress
./node_modules/.bin/cypress open
