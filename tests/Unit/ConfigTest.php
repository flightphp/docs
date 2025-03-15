<?php

declare(strict_types=1);

namespace Tests\Unit;

use app\utils\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Config::class)]
final class ConfigTest extends UnitTestCase {
    #[Test]
    function it_can_set_and_get_a_config(): void {
        $dsn = 'sqlite::memory:';

        $config = new Config([
            'PDO_DSN' => $dsn
        ]);

        self::assertSame($dsn, $config['PDO_DSN']);
    }
}
