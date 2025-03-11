<?php

namespace app\utils;

use flight\Engine;
use Wruczek\PhpFileCache\PhpFileCache;
use Latte\Engine as LatteEngine;
use Parsedown;
use app\utils\Translator;

/**
 * This is only for autocomplete help.
 * 
 * @method PhpFileCache cache()
 * @method LatteEngine latte()
 * @method Parsedown parsedown()
 * @method Translator translator()
 * @deprecated
 */
class CustomEngine extends Engine {
}
