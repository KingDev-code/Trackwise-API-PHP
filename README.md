# Trackwise PHP API (Symfony)

## Overview

This repository contains the Trackwise API developed in PHP using the Symfony framework, responsible for user management.

## Prerequisites

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)

## Installation and Running Locally

1. Clone the repository:

    ```bash
    git clone https://github.com/KingDev-code/Trackwise-API-PHP.git
    cd Trackwise-API-PHP
    ```

2. Copy the example environment file and configure the environment variables:

    ```bash
    cp .env.example .env
    ```

    Edit the `.env` file to configure your database connection and other environment variables. For example:

    ```env
    APP_ENV=dev
    APP_SECRET=your_secret_key
    DATABASE_URL=mysql://user:password@127.0.0.1:3306/user_project_management
    ```

3. Install the dependencies:

    ```bash
    composer install
    ```

4. Generate the JWT keys:

    Create a directory for the JWT keys:

    ```bash
    mkdir -p config/jwt
    ```

    Generate the private key:

    ```bash
    openssl genpkey -algorithm RSA -out config/jwt/private.pem -aes256
    ```

    Generate the public key:

    ```bash
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    ```

5. Start the server:

    ```bash
    symfony server:start
    ```

6. Access the API at `http://localhost:8000`.

## Running with Docker

1. Clone the repositories:

    ```bash
    git clone https://github.com/KingDev-code/Trackwise-API.git
    git clone https://github.com/KingDev-code/Trackwise-API-PHP.git
    cd Trackwise-API-PHP
    ```

2. Build and start the containers:

    ```bash
    docker-compose up --build
    ```

3. Access the APIs at `http://localhost:3000` for Node.js and `http://localhost:8000` for Symfony.

    **Note:** Reset the Node.js container before testing.

## Docker Configuration

Ensure your `docker-compose.yml` is correctly configured. Here is an example setup:

```yaml
version: '3.8'
services:
  api-php:
    build: ./ # Path to the directory with PHP/Symfony Dockerfile
    volumes:
      - ./:/var/www/html # Ensure the path './php' is correct for your Symfony code
    ports:
      - "8000:8000" # Map port 8000 from the container to port 8000 on the host
    networks:
      - app-network
    environment:
      APP_ENV: prod # Set the Symfony application environment
      DATABASE_URL: mysql://user:password@db:3306/user_project_management # MySQL connection URL

  api-node:
    build: ../Trackwise-Node # Path to the directory with Node.js Dockerfile
    ports:
      - "3000:3000" # Map port 3000 from the container to port 3000 on the host
    volumes:
      - ../Trackwise-Node:/usr/src/app # Path to the directory with Node.js code
    networks:
      - app-network

  db:
    image: mysql:8.0 # Specify the MySQL version
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: user_project_management
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "3306:3306" # Map port 3306 for MySQL access
    volumes:
      - dbdata:/var/lib/mysql # Named volume for MySQL data persistence
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
