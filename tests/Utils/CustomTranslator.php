<?php

declare(strict_types=1);

namespace Tests\Utils;

use app\utils\Translator;

final class CustomTranslator extends Translator {
    function getLanguage(): string {
        return $this->language;
    }

    function getVersion(): string {
        return $this->version;
    }
}
