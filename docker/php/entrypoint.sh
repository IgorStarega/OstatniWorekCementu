#!/bin/sh
# Entrypoint kontenera aplikacji.
# Przy każdym starcie generuje /etc/msmtprc na podstawie zmiennych z .env, dzięki
# czemu hasło SMTP nie jest zapisane w obrazie. Gdy SMTP nie jest skonfigurowane
# (np. lokalnie), plik nie powstaje, a PHP mail() po prostu nic nie wyśle.
set -e

if [ -n "${SMTP_HOST:-}" ]; then
    umask 077
    cat > /etc/msmtprc <<EOF
defaults
auth on
tls on
tls_trust_file /etc/ssl/certs/ca-certificates.crt
logfile /var/log/php/msmtp.log

account default
host ${SMTP_HOST}
port ${SMTP_PORT:-587}
user ${SMTP_USER}
password ${SMTP_PASS}
from ${SMTP_FROM}
EOF
    chown www-data:www-data /etc/msmtprc
    chmod 600 /etc/msmtprc
fi

exec docker-php-entrypoint "$@"
