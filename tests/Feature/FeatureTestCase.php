<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

abstract class FeatureTestCase extends TestCase
{
  protected static Client $client;

  public function __construct(string $name) // @phpstan-ignore-line
  {
    parent::__construct($name);

    $_ENV['APP_URL'] ??= 'http://localhost:61001';

    if (!str_ends_with((string) $_ENV['APP_URL'], '/')) {
      $_ENV['APP_URL'] .= '/';
    }

    self::$client ??= new Client([
      'base_uri' => $_ENV['APP_URL']
    ]);
  }
}
