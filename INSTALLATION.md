# MBC Finance Loan Management System - Installation Guide

## 📋 System Requirements

### PHP Requirements
- **PHP Version**: 8.1 or higher (Recommended: PHP 8.2)
- **Memory Limit**: Minimum 256MB (Recommended: 512MB)
- **Max Execution Time**: 300 seconds
- **Upload Max Filesize**: 50MB
- **Post Max Size**: 50MB

### Required PHP Extensions
```bash
# Core Extensions
php-bcmath
php-ctype
php-curl
php-dom
php-fileinfo
php-json
php-mbstring
php-openssl
php-pcre
php-pdo
php-tokenizer
php-xml
php-zip

# Database Extensions
php-mysql
php-pdo-mysql

# Additional Extensions
php-gd
php-intl
php-exif
php-imagick (optional)
```

### Database Requirements
- **MySQL**: 5.7 or higher (Recommended: MySQL 8.0)
- **MariaDB**: 10.3 or higher
- **Database Charset**: utf8mb4
- **Database Collation**: utf8mb4_unicode_ci

### Node.js Requirements (for PWA and FinanceFlow)
- **Node.js**: 18.x or higher
- **npm**: 9.x or higher
- **Memory**: 2GB RAM minimum

### Web Server Requirements
- **Apache**: 2.4+ with mod_rewrite enabled
- **Nginx**: 1.18+ with proper PHP-FPM configuration
- **SSL Certificate**: Required for PWA features

## 🚀 Installation Steps

### Step 1: Clone Repository
```bash
git clone https://github.com/mxo2/mbc-finance-loan-management.git
cd mbc-finance-loan-management
```

### Step 2: Laravel Backend Setup

#### Install PHP Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Configure Database (.env file)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbc_finance
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE mbc_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

#### Storage and Permissions
```bash
# Create storage link
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Set permissions (Windows)
# Grant full control to IIS_IUSRS on storage and bootstrap/cache folders
```

### Step 3: PWA Application Setup

```bash
cd LoanApp-PWA

# Install dependencies
npm install

# Build for production
npm run build

# Or run development server
npm run dev
```

### Step 4: FinanceFlow Application Setup

```bash
cd FinanceFlow

# Install dependencies
npm install

# Build for production
npm run build

# Or run development server
npm run dev
```

## 🌐 Deployment Options

### Option 1: Shared Hosting

#### Requirements Check
```php
<?php
// Create check.php in public folder
phpinfo();

// Check required extensions
$required = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pcre', 'pdo', 'tokenizer', 'xml', 'zip', 'mysql'];
foreach($required as $ext) {
    echo $ext . ': ' . (extension_loaded($ext) ? 'OK' : 'MISSING') . "\n";
}
?>
```

#### Upload Files
1. Upload all files to public_html or www folder
2. Move Laravel files outside public folder for security
3. Update paths in index.php

### Option 2: VPS/Dedicated Server

#### Ubuntu/Debian Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-intl

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Install Nginx
sudo apt install nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/mbc-finance/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Option 3: Docker Deployment

#### Create Dockerfile
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage
```

#### Docker Compose
```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www
    depends_on:
      - db
  
  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: mbc_finance
      MYSQL_ROOT_PASSWORD: password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

## 🔧 Configuration

### Environment Variables (.env)
```env
# Application
APP_NAME="MBC Finance"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbc_finance
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="MBC Finance"

# File Storage
FILESYSTEM_DISK=local

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file

# Queue
QUEUE_CONNECTION=sync
```

### Web Server Configuration

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Nginx (production)
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/mbc-finance/public;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## 🔒 Security Configuration

### File Permissions
```bash
# Set correct permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

### Security Headers (Nginx)
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
```

## 📱 PWA Configuration

### SSL Certificate (Required for PWA)
```bash
# Using Let's Encrypt
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### Service Worker
Ensure the PWA service worker is properly configured in `LoanApp-PWA/public/sw.js`

## 🧪 Testing Installation

### Backend Tests
```bash
# Run Laravel tests
php artisan test

# Check application status
php artisan about
```

### Frontend Tests
```bash
# Test PWA
cd LoanApp-PWA
npm run test

# Test FinanceFlow
cd FinanceFlow
npm run test
```

### Health Check URLs
- Backend: `https://your-domain.com/health`
- PWA: `https://your-domain.com:3001/`
- FinanceFlow: `https://your-domain.com:3000/`

## 🚨 Troubleshooting

### Common Issues

#### 1. Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/mbc-finance
sudo chmod -R 775 storage bootstrap/cache
```

#### 2. Database Connection Failed
```bash
# Check MySQL service
sudo systemctl status mysql

# Test connection
mysql -u username -p -h localhost
```

#### 3. PHP Extensions Missing
```bash
# Install missing extensions
sudo apt install php8.2-extension-name
sudo systemctl restart php8.2-fpm
```

#### 4. Composer Install Fails
```bash
# Clear composer cache
composer clear-cache

# Install with verbose output
composer install -vvv
```

#### 5. NPM Install Fails
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

## 📞 Support

For installation support:
1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check web server error logs
4. Create an issue on GitHub repository

## 🔄 Updates

### Updating the Application
```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Update frontend
cd LoanApp-PWA && npm install && npm run build
cd ../FinanceFlow && npm install && npm run build
```

---

**Note**: This installation guide covers all major deployment scenarios. Choose the method that best fits your hosting environment and technical expertise.