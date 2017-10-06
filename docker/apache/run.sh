#!/bin/sh
set -xe

# Pull Custom Assets
if [ ${WALLPAPER_CURL_CONFIG} ]; then
	curl $WALLPAPER_CURL_CONFIG --output /var/www/docker/apache/curl.config
fi

pwd="$(pwd)"
cd /var/www/html/assets
rm -f *

curl --config /var/www/docker/apache/curl.config

cd $pwd

# Start Apache with the right permissions
$(dirname "$0")/start_safe_perms -DFOREGROUND
