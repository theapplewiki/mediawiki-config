FROM mediawiki:1.41-fpm

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN set -eux; \
		apt-get update -q; \
		apt-get install -qy --no-install-recommends \
			unzip \
			liblua5.1-0-dev \
			libmagickwand-dev \
			libzstd-dev; \
		pecl install imagick luasandbox redis; \
		docker-php-ext-install -j $(nproc) pcntl; \
		docker-php-ext-enable imagick luasandbox pcntl redis; \
		apt-get clean; \
		rm -rf /tmp/pear /var/lib/apt/lists/*

CMD ["php-fpm"]
