Production at: https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/SWI2-autoserivs/Adam-slim/public/home \
Testing at: https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/test/SWI2-autoserivs/Adam-slim/public/login


# Local installation
php composer install \
php -S localhost:8080 -t public \

# Running cypress locally
Run this in the repo: \
./node_modules/.bin/cypress open \
or \
npm install cypress --save-dev \
./node_modules/.bin/cypress run
