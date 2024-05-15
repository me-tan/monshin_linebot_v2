FROM php:7.2-apache 

WORKDIR /var/www/html

# デッドリンクになっているライブラリのリンク先を変更する
RUN echo "deb http://archive.debian.org/debian/ stretch main" > /etc/apt/sources.list
RUN echo "deb http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

RUN  a2enmod rewrite

COPY . /var/www/html

# Composer をインストール
COPY --from=composer /usr/bin/composer /usr/bin/composer
