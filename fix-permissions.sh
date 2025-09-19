#!/bin/bash

# Laravel Permissions Fix Script
# This script fixes common Laravel permission issues

echo "Fixing Laravel permissions..."

# Set ownership to www-data (web server user)
sudo chown -R www-data:www-data /home/frappe/fix.mbcfinserv.com/storage
sudo chown -R www-data:www-data /home/frappe/fix.mbcfinserv.com/bootstrap/cache

# Set proper permissions
sudo chmod -R 775 /home/frappe/fix.mbcfinserv.com/storage
sudo chmod -R 775 /home/frappe/fix.mbcfinserv.com/bootstrap/cache

# Make sure log file is writable by both web server and CLI
sudo chmod 666 /home/frappe/fix.mbcfinserv.com/storage/logs/laravel.log

echo "Permissions fixed successfully!"
echo "Storage directory owner: $(stat -c '%U:%G' /home/frappe/fix.mbcfinserv.com/storage)"
echo "Log file permissions: $(stat -c '%a' /home/frappe/fix.mbcfinserv.com/storage/logs/laravel.log)"