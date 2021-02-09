FROM node:alpine as builder

WORKDIR /smr/

# See https://github.com/hollandben/grunt-cache-bust/issues/236
RUN npm i --save grunt grunt-contrib-uglify grunt-contrib-cssmin grunt-cache-bust@1.4.1

# Copy the SMR source code directories
COPY src src

# Perform CSS/JS minification and cache busting
COPY Gruntfile.js .
RUN npx grunt

# Remove local grunt install so it is not copied to the next build stage
RUN rm -rf node_modules

#---------------------------

FROM php:7.4.14-apache
RUN apt-get update \
	&& apt-get install -y zip unzip \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install mysqli opcache

# Set the baseline php.ini version based on the value of PHP_DEBUG
ARG PHP_DEBUG=0
RUN MODE=$([ "$PHP_DEBUG" = "1" ] && echo "development" || echo "production") && \
	echo "Using $MODE php.ini" && \
	mv "$PHP_INI_DIR/php.ini-$MODE" "$PHP_INI_DIR/php.ini"

RUN if [ "$PHP_DEBUG" = "1" ]; \
	then \
		pecl install xdebug-3.0.2 && \
		docker-php-ext-enable xdebug; \
	fi

# Disable apache access logging (error logging is still enabled)
RUN sed -i 's|CustomLog.*|CustomLog /dev/null common|' /etc/apache2/sites-enabled/000-default.conf

# Disable apache .htaccess files (suggested optimization)
RUN sed -i 's/AllowOverride All/AllowOverride None/g' /etc/apache2/conf-enabled/docker-php.conf

WORKDIR /smr/

RUN curl -sS https://getcomposer.org/installer | \
	php -- --install-dir=/usr/local/bin --filename=composer --version=2.0.9

COPY composer.json .
RUN composer install --no-interaction

COPY --from=builder /smr .
RUN rm -rf /var/www/html/ && ln -s "$(pwd)/src/htdocs" /var/www/html

# Make the upload directory writable by the apache user
RUN chown www-data ./src/htdocs/upload

# Leverage browser caching of static assets using apache's mod_headers
COPY apache/cache-static.conf /etc/apache2/conf-enabled/cache-static.conf
RUN a2enmod headers

# Store the git commit hash of the repo in the final image
COPY .git/HEAD .git/HEAD
COPY .git/refs .git/refs
RUN REF="ref: HEAD" && \
	while [ -n "$(echo $REF | grep ref:)" ]; do REF=$(cat ".git/$(echo $REF | awk '{print $2}')"); done && \
	echo $REF > git-commit
