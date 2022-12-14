#!/bin/sh

if [ ! -x /usr/bin/python3 ]; then
    echo "Installing python..."
    apt-get update
    apt-get install -y python3 python3-pip
    pip3 install requests bs4 bibtexparser nltk lxml
fi

/usr/local/bin/docker-php-entrypoint
apache2-foreground