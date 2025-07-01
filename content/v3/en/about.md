# What is Flight?

Flight is a fast, simple, extensible framework for PHP—built for developers who want to get things done quickly, with zero fuss. Whether you're building a classic web app, a blazing-fast API, or experimenting with the latest AI-powered tools, Flight's low footprint and straightforward design make it a perfect fit.

## Why Choose Flight?

- **Beginner Friendly:** Flight is a great starting point for new PHP developers. Its clear structure and simple syntax help you learn web development without getting lost in boilerplate.
- **Loved by Pros:** Experienced devs love Flight for its flexibility and control. You can scale from a tiny prototype to a full-featured app without switching frameworks.
- **AI Friendly:** Flight's minimal overhead and clean architecture make it ideal for integrating AI tools and APIs. Whether you're building smart chatbots, AI-driven dashboards, or just want to experiment, Flight gets out of your way so you can focus on what matters. [Learn more about using AI with Flight](/learn/ai)

## Quick Start

First, install it with Composer:

```bash
composer require flightphp/core
```

Or you can download a zip of the repo [here](https://github.com/flightphp/core). Then you'd have a basic `index.php` file like the following:

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

That's it! You have a basic Flight application. You can now run this file with `php -S localhost:8000` and visit `http://localhost:8000` in your browser to see the output.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Simple enough, right?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Learn more about Flight in the documentation!</a>
      <br>
      <button href="/learn/ai" class="btn btn-primary mt-3">Discover how Flight makes AI easy</button>
    </div>
  </div>
</div>

## Is it fast?

Absolutely! Flight is one of the fastest PHP frameworks out there. Its lightweight core means less overhead and more speed—perfect for both traditional apps and modern AI-powered projects. You can see all the benchmarks at [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

See the benchmark below with some other popular PHP frameworks.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Skeleton/Boilerplate App

There's an example app to help you get started with Flight. Check out [flightphp/skeleton](https://github.com/flightphp/skeleton) for a ready-to-go project, or visit the [examples](examples) page for inspiration. Want to see how AI fits in? [Explore AI-powered examples](/learn/ai).

# Community

We're on Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

And Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contributing

There are two ways you can contribute to Flight:

1. Contribute to the core framework by visiting the [core repository](https://github.com/flightphp/core).
2. Help make the docs better! This documentation website is hosted on [Github](https://github.com/flightphp/docs). If you spot an error or want to improve something, feel free to submit a pull request. We love updates and new ideas—especially around AI and new tech!

# Requirements

Flight requires PHP 7.4 or greater.

**Note:** PHP 7.4 is supported because at the current time of writing (2024) PHP 7.4 is the default version for some LTS Linux distributions. Forcing a move to PHP >8 would cause a lot of heartburn for those users. The framework also supports PHP >8.

# License

Flight is released under the [MIT](https://github.com/flightphp/core/blob/master/LICENSE) license.