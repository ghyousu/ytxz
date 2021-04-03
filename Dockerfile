## created based on: https://wiki.alpinelinux.org/wiki/Setting_Up_Apache_with_PHP

FROM alpine:3.12

# RUN echo http://dl-cdn.alpinelinux.org/alpine/v$(cat /etc/alpine-release | cut -d'.' -f1,2)/main > /etc/apk/repositories  && \
#     echo http://dl-cdn.alpinelinux.org/alpine/v$(cat /etc/alpine-release | cut -d'.' -f1,2)/community >> /etc/apk/release && \
#     apk update && \
#     export phpverx=$(alpinever=$(cat /etc/alpine-release|cut -d '.' -f2);[ $alpinever -ge 9 ] && echo  7|| echo 5) && \
#     apk add apache2 php${phpverx}-apache2

RUN apk update && \
    apk add --no-cache \
       apache2 \
       ffmpeg \
       php7-apache2 \
       python3 \
       py-pip \
       && \
    rm -rfv /var/cache/apk/* && \
    pip3 install --no-cache-dir --no-cache youtube_dl

RUN echo '<?php phpinfo(); ?>' > /var/www/localhost/htdocs/index.php

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

WORKDIR /var/www/localhost/htdocs

EXPOSE 80

# CMD [ "/usr/sbin/httpd", "-D", "BACKGROUND" ]
CMD [ "/usr/sbin/httpd", "-D", "FOREGROUND" ]

