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

# パッケージをインストール
RUN apt-get update && apt-get install -y \
cron && \
rm -rf /var/lib/apt/lists/* \
bash \
curl \
tzdata \
unzip \
vim \
libcurl4-gnutls-dev \



# タイムゾーンの設定
RUN cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
    echo "Asia/Tokyo" > /etc/timezone

# cronジョブ用のスクリプトを追加
COPY ./cronjob.sh /root/cronjob.sh
RUN chmod +x /root/cronjob.sh

# cronジョブの設定
RUN echo "* * * * * /root/cronjob.sh >> /var/log/cron.log 2>&1" > /etc/cron.d/cronjob

# cronログファイルを作成
RUN touch /var/log/cron.log

# 環境変数を設定
ENV EDITOR=vim

# cronサービスをフォアグラウンドで実行
CMD ["sh", "-c", "service cron start && apache2-foreground"]