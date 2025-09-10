<?php

declare(strict_types=1);

namespace app\utils;

use Flight;

class Translator
{
    public function __construct(protected string $language = 'en', protected string $version = 'v3')
    {
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function translate(string $translationKey)
    {
        $translationContent = $this->getTranslationFileContents();
        $language = $this->language;

        // fallback to english if it doesn't exist in the language
        if (isset($translationContent[$language][$translationKey]) === false) {
            $language = 'en';
        }

        return $translationContent[$language][$translationKey];
    }

    protected function getTranslationFileContents(): array
    {
        $translationFilePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;
        $translationFile = $translationFilePath . $this->language . '.php';
        $translationContent = [];

        if (file_exists($translationFile) === true) {
            $translationContent[$this->language] = include $translationFile;
        }

        // english is always a fallback
        $translationContent['en'] = include $translationFilePath . 'en.php';
        return $translationContent;
    }

    public static function getLanguageFromRequest(): string
    {
        $current_language = Flight::get('current_script_language');

        if ($current_language !== null) {
            return $current_language;
        }

        $language = Flight::request()->query->lang;

        if (!empty($language)) {
            $host = ENVIRONMENT !== 'development' ? Flight::request()->getHeader('Host') : 'localhost';

            setcookie('language', (string) $language, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'domain' => $host,
                'secure' => ENVIRONMENT !== 'development',
                'httponly' => true
            ]); // 86400 = 1 day
        }

        // pull it from the cookie
        if (empty($language) && !empty(Flight::request()->cookies->language)) {
            $language = Flight::request()->cookies->language;
        }

        // pull it from the header or default it to en
        if (empty($language)) {
            $language = Flight::request()->getHeader('Accept-Language', 'en');
        }

        $languageAbbreviation = substr((string) $language, 0, 2);

        // This is a temporary cache, so we don't have to do this on every request
        Flight::set('current_script_language', $languageAbbreviation);

        return $languageAbbreviation;
    }

    public function getMarkdownLanguageFile(string $file): string
    {
        $language = $this->language;
        $ds = DIRECTORY_SEPARATOR;
        $markdownFilePath = __DIR__ . $ds . '..' . $ds . '..' . $ds . 'content' . $ds . $this->version . $ds;
        $markdownFile = $markdownFilePath . $language . $ds . $file;

        // fallback to english if it doesn't exist in the language
        if (file_exists($markdownFile) === false) {
            $language = 'en';
            $markdownFile = $markdownFilePath . $language . $ds . $file;
        }

        if (file_exists($markdownFile) === false) {
            Flight::notFound();
        }

        return file_get_contents($markdownFile);
    }
}
