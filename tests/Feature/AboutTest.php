<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class AboutTest extends FeatureTestCase {
    #[Test]
    #[DataProvider('aboutPageUrisDataProvider')]
    function it_should_return_the_about_page(string $uri): void {
        $response = self::$client->get(str_replace('//', '/', "./es/v3/$uri"));
        $html = new DOMDocument;
        @$html->loadHTML($response->getBody()->getContents());
        $title = $html->getElementsByTagName('title')->item(0);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Acerca de', $title->textContent);

        self::assertStringContainsString(
            'Flight PHP Framework',
            $title->textContent
        );
    }

    static function aboutPageUrisDataProvider(): array {
        return [
            ['/'],
            ['/about'],
        ];
    }
}
