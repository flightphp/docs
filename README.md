# Flight Documentation Site

This is the source code for the Flight Micro Framework (built with Flight!)

## Requirements

This project requires PHP 8.2 or greater. You also need to install [composer](https://getcomposer.org/) to install the dependencies.

## Installation

This was just created from the Flight skeleton app. To install just follow these steps:

```bash
# Clone the repo
git clone https://github.com/flightphp/docs flight-docs/

cd flight-docs/

# Install the dependencies
composer install

# Copy the config file
cp app/config/config_sample.php app/config/config.php

# Start the server (if you have PHP >8.2 installed)
composer start

# Or if you have a different version of PHP installed, you can use the built-in server
php82 -S localhost:8000 -t public/
```

## Additional Translations

Please note that the additional translations are provided by ChatGPT and are not official translations. Please don't make a PR to fix a translation as when the documentation is retranslated, your contribution will be lost. 

If your translation is really not good, please open an issue and we will look into possible having you maintain that translation without the assistance of ChatGPT.
