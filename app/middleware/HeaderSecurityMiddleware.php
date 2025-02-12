<?php

declare(strict_types=1);

namespace app\middleware;

use Flight;

class HeaderSecurityMiddleware {
	public static string $nonce = '';

	public function before() {
		if (empty(self::$nonce)) {
			$nonce = base64_encode(openssl_random_pseudo_bytes(16));
			self::$nonce = $nonce;
		} else {
			$nonce = self::$nonce;
		}

		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'; script-src 'self' https://api.github.com https://cdn.jsdelivr.net https://buttons.github.io https://unpkg.com https://opengraph.b-cdn.net https://www.googletagmanager.com 'nonce-" . $nonce . "'; font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com; img-src 'self' https://cdn.jsdelivr.net data: https://api.github.com https://raw.githubusercontent.com; connect-src 'self' https://api.github.com; frame-src https://www.youtube.com");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}
