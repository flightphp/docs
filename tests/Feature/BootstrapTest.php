<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\Attributes\Test;

final class BootstrapTest extends FeatureTestCase {
    #[Test]
    function it_returns_500_when_no_config_file_exists(): void {
        $configFilePath = dirname(__DIR__, 2) . '/app/config/config.php';
        $backupConfigFilePath = "$configFilePath.backup";

        if (file_exists($configFilePath)) {
            rename($configFilePath, $backupConfigFilePath);
        }

        try {
            self::$client->get('./');
        } catch (GuzzleException $exception) {
            $message = $exception->getMessage();

            self::assertStringContainsString('500', $message);

            self::assertStringContainsString(
                'Config file not found. Please create a config.php file in the app/config directory to get started.',
                $message
            );
        }

        rename($backupConfigFilePath, $configFilePath);
    }
}
