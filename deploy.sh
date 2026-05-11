#!/bin/bash

# Bonehacker CI4 Docker Deployment Script
# Author: Antigravity

echo "🚀 Starting deployment process..."

# 1. Check for .env file
if [ ! -f .env ]; then
    echo "📄 .env file not found. Creating from template..."
    cp env .env
    # Set default docker DB credentials in .env
    sed -i 's/# database.default.hostname = localhost/database.default.hostname = db/g' .env
    sed -i 's/# database.default.database = ci4/database.default.database = bonehacker_db/g' .env
    sed -i 's/# database.default.username = root/database.default.username = user_bonehacker/g' .env
    sed -i 's/# database.default.password = root/database.default.password = user_password/g' .env
    sed -i 's/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = production/g' .env
    echo "✅ .env created with Docker defaults."
fi

# 2. Build and Start Containers
echo "🏗️ Building and starting containers..."
docker-compose up -d --build

# 3. Wait for DB to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# 4. Run Migrations
echo "🗄️ Running database migrations..."
docker-compose exec app php spark migrate

echo "✨ Deployment finished successfully!"
echo "🌐 App is running at http://localhost:8080"
echo "🛠️ phpMyAdmin is running at http://localhost:8081"
