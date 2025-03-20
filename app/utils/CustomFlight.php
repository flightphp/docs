<?php

namespace app\utils;

use flight\Engine;
use flight\Cache;
use Latte\Engine as LatteEngine;
use Parsedown;
use app\utils\Translator;

/**
 * This is only for autocomplete help.
 * @deprecated
 * @method Cache cache()
 * @method LatteEngine latte()
 * @method Parsedown parsedown()
 * @method Translator translator()
 */
class CustomEngine extends Engine {
}
