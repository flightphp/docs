# What is Flight?

Flight is a fast, simple, extensible framework for PHP. It is quite versatile and can be used for building any kind of web application. It is built with simplicity in mind and is written in a way that is easy to understand and use.

Flight is a great beginner framework for those who are new to PHP and want to learn how to build web applications. It is also a great framework for experienced developers who want more control over their web applications. It is engineered to easily build a RESTful API, a simple web application, or a complex web application.

## Quick Start

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Simple enough right?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Learn more about Flight in the documentation!</a>

    </div>
  </div>
</div>

### Skeleton/Boilerplate App

There is an example app that can help you get started with the Flight Framework. Go to [flightphp/skeleton](https://github.com/flightphp/skeleton) for instructions on how to get started! You can also visit the [examples](examples) page for inspiration on some of the things you can do with Flight.

# Community

We're on Matrix Chat with us at [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contributing

There are two ways you can contribute to Flight: 

1. You can contribute to the core framework by visiting the [core repository](https://github.com/flightphp/core). 
1. You can contribute to the documentation. This documentation website is hosted on [Github](https://github.com/flightphp/docs). If you notice an error or want to flesh out something better, feel free to correct it and submit a pull request! We try to keep up on things, but updates and language translations are welcome.

# Requirements

Flight requires PHP 7.4 or greater.

**Note:** PHP 7.4 is supported because at the current time of writing (2024) PHP 7.4 is the default version for some LTS Linux distributions. Forcing a move to PHP >8 would cause a lot of heartburn for those users. The framework also supports PHP >8.

# License

Flight is released under the [MIT](https://github.com/flightphp/core/blob/master/LICENSE) license. 