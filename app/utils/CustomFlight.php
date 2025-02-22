<?php

namespace app\utils;

use flight\Engine;
use Wruczek\PhpFileCache\PhpFileCache;
use Latte\Engine as LatteEngine;
use Parsedown;

/**
 * This is only for autocomplete help.
 * 
 * @method PhpFileCache cache()
 * @method LatteEngine latte()
 * @method Parsedown parsedown()
 */
class CustomEngine extends Engine {
}
