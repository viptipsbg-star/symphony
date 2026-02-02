#!/bin/bash

# Script to update database schema for Reflection entity
# Run this on VPS after uploading all files

echo "=== Starting Database Update ==="

# Clear cache first
echo "Clearing cache..."
rm -rf app/cache/*

# Update database schema
echo "Updating database schema..."
php app/console doctrine:schema:update --force

# Clear cache again
echo "Clearing cache again..."
rm -rf app/cache/*

echo "=== Database Update Complete ==="
echo "Please test the site now!"
