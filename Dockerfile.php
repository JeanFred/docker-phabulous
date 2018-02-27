FROM alpine/git as git
RUN git clone https://github.com/slashsBin/phabulous.git /code

# FROM alpine/git as git_libphutil
# RUN git clone https://github.com/phacility/libphutil /libphutil
# RUN	cd /libphutil/ && \
# 	git checkout d6818e5

FROM node:9.5.0-alpine as bower
RUN apk add --update git
WORKDIR /code
RUN npm install -g bower grunt-cli && \
    echo '{ "allow_root": true }' > /root/.bowerrc
COPY --from=git /code /code
RUN bower install

FROM composer:1.5 as composer
WORKDIR /code
COPY --from=git /code /code
RUN composer install -vv
#RUN rm -rf /code/vendor/phacility/libphutil
#COPY --from=git_libphutil /libphutil /code/vendor/phacility/libphutil

FROM php:7.1-fpm as php-gantt
WORKDIR /code
COPY --from=composer /code /code
COPY --from=bower /code/web/assets/vendor/ /code/web/assets/vendor/
COPY parameters.yml security.yml ./app/config/
COPY app_dev.php ./web/app_dev.php
RUN chown -R www-data:www-data /code/var/
