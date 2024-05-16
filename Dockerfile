# Etapa 1: Instalação das dependências do Composer
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-cli AS composer_stage

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala as dependências necessárias do sistema
RUN apt-get update && apt-get install -y git zip unzip

# Copia os arquivos necessários e instala dependências
WORKDIR /app
COPY composer.json ./
RUN composer install --no-scripts --no-dev --ignore-platform-reqs

# Etapa 2: Configuração do ambiente de execução
FROM php:${PHP_VERSION}-cli

# Instala as dependências necessárias do sistema
RUN apt-get update && apt-get install -y libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instala o Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Copia os arquivos do projeto e dependências do Composer
WORKDIR /var/www/html
COPY --from=composer_stage /app/vendor /var/www/html/vendor
COPY . /var/www/html

# Define as permissões corretas
RUN chown -R www-data:www-data /var/www/html

# Exponha a porta que o Symfony CLI usa por padrão
EXPOSE 8000

# Comando para iniciar o Symfony server
CMD ["symfony", "server:start", "--no-tls", "--dir=/var/www/html", "--port=8000", "--allow-http"]