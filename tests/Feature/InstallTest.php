<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use PHPUnit\Framework\Attributes\Test;

final class InstallTest extends FeatureTestCase {
    #[Test]
    function it_shows_install_page(): void {
        $response = self::$client->get('./es/v3/install');
        $html = new DOMDocument;
        @$html->loadHTML($response->getBody()->getContents());
        $title = $html->getElementsByTagName('title')->item(0);
        $jumbotron = $html->getElementsByTagName('h1')->item(0);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Instalación', $title->textContent);
        self::assertSame('Instalación', $jumbotron->textContent);
    }
}
