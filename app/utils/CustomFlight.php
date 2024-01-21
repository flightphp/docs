<?php

namespace app\utils;

use Flight;
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
class CustomFlight extends Flight {}