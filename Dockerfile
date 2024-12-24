# https://github.com/wikimedia/mediawiki-docker/blob/main/1.42/fpm/Dockerfile
FROM php:8.2-fpm

# == Start copied from mediawiki:1.42-fpm ==
# System dependencies
RUN set -eux; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		git \
		librsvg2-bin \
		imagemagick \
		# Required for SyntaxHighlighting
		python3 \
	; \
	rm -rf /var/lib/apt/lists/*

# Install the PHP extensions we need
RUN set -eux; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libicu-dev \
		libonig-dev \
		liblua5.1-0-dev \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		calendar \
		intl \
		mbstring \
		mysqli \
		opcache \
	; \
	\
	pecl install APCu-5.1.24; \
	pecl install LuaSandbox-4.1.2; \
	docker-php-ext-enable \
		apcu \
		luasandbox \
	; \
	rm -r /tmp/pear; \
	\
	# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' \
		| sort -u \
		| xargs -r dpkg-query --search \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual;
	# \
	# apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	# rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# SQLite Directory Setup
RUN set -eux; \
	mkdir -p /var/www/data; \
	chown -R www-data:www-data /var/www/data

# == End copied from mediawiki:1.42-fpm ==

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN set -eux; \
		apt-get update -q; \
		apt-get install -qy --no-install-recommends \
			unzip \
			libmagickwand-dev \
			libzstd-dev; \
		apt-get autoremove -qy; \
		apt-get clean; \
		rm -rf /var/lib/apt/lists/*

RUN set -eux; \
		pecl install excimer imagick redis; \
		docker-php-ext-install -j $(nproc) exif pcntl; \
		docker-php-ext-enable imagick pcntl redis; \
		rm -rf /tmp/pear

CMD ["php-fpm"]
