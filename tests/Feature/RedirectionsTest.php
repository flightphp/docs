<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

final class RedirectionsTest extends FeatureTestCase {
    #[Test]
    function it_redirects_to_default_language_and_version_when_nothing_is_specified(): void {
        $response = self::$client->get('./', [
            'allow_redirects' => false
        ]);

        self::assertSame(303, $response->getStatusCode());

        self::assertStringContainsString(
            '/en/v3/',
            $response->getHeaderLine('location')
        );
    }

    #[Test]
    function it_redirects_to_version_when_only_language_is_specified(): void {
        $response = self::$client->get('/en', [
            'allow_redirects' => false
        ]);

        self::assertSame(303, $response->getStatusCode());

        self::assertStringContainsString(
            '/en/v3/',
            $response->getHeaderLine('location')
        );
    }

    #[Test]
    function it_redirects_to_default_language_and_version_when_only_section_and_subsection_is_specified(): void {
        $response = self::$client->get('/section/subsection', [
            'allow_redirects' => false
        ]);

        self::assertSame(303, $response->getStatusCode());

        self::assertStringContainsString(
            '/en/v3/section/subsection/',
            $response->getHeaderLine('location')
        );
    }
}
