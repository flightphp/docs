<?php
declare(strict_types=1);

namespace app\middleware;

use Flight;

class HeaderSecurityMiddleware
{
	public function before()
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		//Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	}
}