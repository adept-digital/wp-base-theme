#!/usr/bin/env bash
set -e

BASE_DIR="$(dirname "$(dirname "${BASH_SOURCE[0]}")")"
BIN_DIR="${BASE_DIR}/vendor/bin"
TEST_DIR="${BASE_DIR}/tests"
WP_DIR="${TEST_DIR}/wp"

if [ ! -f "${BIN_DIR}/wp" ]; then
  echo 'Missing `wp` command. Have you run `composer install`?'
  exit 1
fi

PATH="${BIN_DIR}:${PATH}"

if [ ! -d "${WP_DIR}" ]; then
  wp core download --path="${WP_DIR}"
fi

if [ ! -f "${WP_DIR}/wp-config.php" ]; then
  wp config create \
    --path="${WP_DIR}" \
    --skip-check \
    --dbhost=database \
    --dbname=database \
    --dbuser=mysql \
    --dbpass=mysql \
    --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
PHP
fi

if ! wp core is-installed --path="${WP_DIR}"; then
  wp core install \
    --path="${WP_DIR}" \
    --url='http://localhost/' \
    --title='Test Site' \
    --admin_user=test \
    --admin_password=test \
    --admin_email=admin@example.com \
    --skip-email
fi

if [ ! -L "${WP_DIR}/wp-content/themes/test-theme" ]; then
  ln -s "${TEST_DIR}/data/test-theme" "${WP_DIR}/wp-content/themes/test-theme"
fi

if [ ! -L "${WP_DIR}/wp-content/themes/test-theme-child" ]; then
  ln -s "${TEST_DIR}/data/test-theme-child" "${WP_DIR}/wp-content/themes/test-theme-child"
fi

wp theme activate --path="${WP_DIR}" test-theme
