<?php

use flight\Container;
use flight\debug\tracy\TracyExtensionLoader;
use Tracy\Debugger;

// Set the default timezone
date_default_timezone_set('UTC');

// Set the error reporting level
error_reporting(E_ALL);

// Set the default character encoding
mb_internal_encoding('UTF-8');

// Set the default locale
setlocale(LC_ALL, 'en_US.UTF-8');

Flight::set('flight.handle_errors', false); // if you want flight to handle errors, set this to true
Flight::set('flight.views.path', __DIR__ . '/../views'); // set the path to your view/template/ui files

/*
 * Get Tracy up and running
 *
 * There lots of setup options for Tracy! Logs, emails, clicking to
 * open in your editor and a lot more!
 * Check out the docs here:
 * https://tracy.nette.org/
 */
Debugger::enable(); // auto tries to figure out your environment
// Debugger::enable(Debugger::Development); // sometimes you have to be explicit (also Debugger::Production)
// Debugger::enable('23.75.345.200'); // you can also provide an array of IP addresses
Debugger::$logDirectory = __DIR__ . '/../log';
Debugger::$strictMode = true; // display all errors
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // all errors except deprecated notices

// if Debugger bar is visible, then content-length can not be set by Flight
if (Debugger::$showBar) {
    Flight::set('flight.content_length', false);
    (new Container)->get(TracyExtensionLoader::class);
}

/* 
 * This is where you will store database credentials, api credentials
 * and other sensitive information. This file will not be tracked by git
 * as you shouldn't be pushing sensitive information to a public or private
 * repository.
 * 
 * What you store here is totally up to you.
 */

define('ENVIRONMENT', 'development');

return [];
