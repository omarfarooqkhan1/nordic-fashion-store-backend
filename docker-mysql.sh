#!/bin/bash

# Stop and remove existing container if it exists
docker stop nordflex-mysql 2>/dev/null || true
docker rm nordflex-mysql 2>/dev/null || true

# Run MySQL container for development
docker run -d \
  --name nordflex-mysql \
  -e MYSQL_ROOT_PASSWORD=root_password \
  -e MYSQL_DATABASE=nordflex_dev \
  -e MYSQL_USER=nordflex_user \
  -e MYSQL_PASSWORD=nordflex_password \
  -p 3306:3306 \
  -v nordflex_mysql_data:/var/lib/mysql \
  mysql:8.0

echo "MySQL container started!"
echo "Connection details:"
echo "Host: 127.0.0.1"
echo "Port: 3306"
echo "Database: nordflex_dev"
echo "Username: nordflex_user"
echo "Password: nordflex_password"
echo "Root password: root_password"