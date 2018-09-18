# Ask for the user password
# Script only works if sudo caches the password for a few minutes
sudo true

if [ ! -z "$(which docker)" ]; then
    wget -qO- https://get.docker.com/ | sh
fi

if [ ! -z "$(which git)" ]; then
    # Install git
    sudo apt-get install git -y
fi

if [ ! -z "$(which docker-compose)" ]; then
    # Install docker-compose
    COMPOSE_VERSION=`git ls-remote https://github.com/docker/compose | grep refs/tags | grep -oP "[0-9]+\.[0-9][0-9]+\.[0-9]+$" | tail -n 1`
    sudo sh -c "curl -L https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose"
    sudo chmod +x /usr/local/bin/docker-compose
    sudo sh -c "curl -L https://raw.githubusercontent.com/docker/compose/${COMPOSE_VERSION}/contrib/completion/bash/docker-compose > /etc/bash_completion.d/docker-compose"

    # Install docker-cleanup command
    cd /tmp
    git clone https://gist.github.com/76b450a0c986e576e98b.git
    cd 76b450a0c986e576e98b
    sudo mv docker-cleanup /usr/local/bin/docker-cleanup
    sudo chmod +x /usr/local/bin/docker-cleanup
fi

# Docker container creation
sudo docker-compose up -d

if [ ! -z "$(which composer)" ]; then
    sudo apt install curl php-cli php-mbstring git unzip
    cd ~ && curl -sS https://getcomposer.org/installer -o composer-setup.php
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
fi
sudo docker exec -it rest-api_php_1 chmod 777 -R storage

sudo docker exec -it rest-api_php_1 composer install

cp ./docroot/.env.example ./docroot/.env

sudo docker exec -it rest-api_php_1 php artisan key:generate

sudo docker exec -it rest-api_php_1 php artisan migrate

sudo docker exec -it rest-api_php_1 php artisan db:seed

sudo echo '127.0.0.1 laravel.docker.localhost' | cat >> /etc/hosts

cp docroot/vendor/alexpechkarev/google-maps/src/config/googlemaps.php docroot/config/googlemaps.php

sed -i "s/ADD_YOUR_SERVICE_KEY_HERE/AIzaSyCNPu6CvUjWJqrBWTbP2cfOtUWVdxla7oU/g" docroot/config/googlemaps.php

sed -i "s/View::class,/&\n\t'GoogleMaps' => GoogleMaps\\\Facade\\\GoogleMapsFacade::class,/g" docroot/config/app.php

sed -i "s/RouteServiceProvider::class,/&\n\t\tGoogleMaps\\\ServiceProvider\\\GoogleMapsServiceProvider::class,/g" docroot/config/app.php
