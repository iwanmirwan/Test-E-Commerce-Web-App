#!/bin/bash

# Update server
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php-fpm php-mysql php-curl php-dom php-mbstring php-xml php-zip unzip

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt install -y nodejs

# Configure MySQL
sudo mysql_secure_installation

# Create database
sudo mysql -e "CREATE DATABASE laravel_ecommerce;"
sudo mysql -e "CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'secure_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON laravel_ecommerce.* TO 'laravel_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Configure Nginx
sudo cp deploy/nginx.conf /etc/nginx/sites-available/laravel
sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Deploy application
git clone https://github.com/yourusername/laravel-ecommerce-full.git /var/www/laravel
cd /var/www/laravel
composer install --optimize-autoloader --no-dev
npm install && npm run build
cp .env.example .env
php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel/storage
sudo chown -R www-data:www-data /var/www/laravel/bootstrap/cache

# Run migrations
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

# Configure supervisor for queues
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/laravel-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Setup cron job
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/laravel && php artisan schedule:run >> /dev/null 2>&1") | crontab -