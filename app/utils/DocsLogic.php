<?php

namespace app\utils;

use app\middleware\HeaderSecurityMiddleware;
use DOMDocument;
use DOMXPath;

class DocsLogic
{
    /**
     * Returns a list of all learn section names (without .md extension).
     *
     * @param string $version The docs version (e.g., 'v3').
     * @param string $language The docs language (e.g., 'en').
     * @return array List of section names
     */
    public function getLearnSectionNames(string $version = 'v3', string $language = 'en'): array
    {
        $baseDir = __DIR__ . '/../../content/' . $version . '/' . $language . '/learn/';
        if (!is_dir($baseDir)) {
            return [];
        }
        $files = scandir($baseDir);
        $sections = [];
        foreach ($files as $file) {
            if (
                str_ends_with($file, '.md') &&
                !str_ends_with($file, '__test.md')
            ) {
                $sections[] = basename($file, '.md');
            }
        }
        sort($sections);
        return $sections;
    }

    const AVAILABLE_LANGUAGES = [
        'en',
        'es',
        'fr',
        'lv',
        'pt',
        'de',
        'ru',
        'zh',
        'ja',
        'ko',
        'uk',
        'id'
    ];

    /**
     * DocsLogic constructor.
     *
     * @param CustomEngine $app Flight Engine
     */
    public function __construct(protected $app)
    {
    }

    /**
     * Renders a page using the specified Latte template file and parameters.
     *
     * @param string $latte_file The path to the Latte template file to be rendered.
     * @param array $params An optional array of parameters to be passed to the template.
     */
    public function renderPage(string $latte_file, array $params = [])
    {
        $request = $this->app->request();
        $uri = $request->url;

        if (str_contains($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }


        // Here we can set variables that will be available on any page
        $params['url'] = $request->getScheme() . '://' . $request->getHeader('Host') . $uri;
        $params['nonce'] = HeaderSecurityMiddleware::$nonce;
        $startTime = microtime(true);
        $this->app->latte()->render($latte_file, $params);
        $executionTime = microtime(true) - $startTime;
        $this->app->eventDispatcher()->trigger('flight.view.rendered', $latte_file . ':' . $uri, $executionTime);
    }

    /**
     * Sets up the translator service with the specified language and version.
     *
     * @param string $language The language to be used by the translator.
     * @param string $version The version of the translation service.
     * @return Translator The configured translator service.
     */
    public function setupTranslatorService(string $language, string $version): Translator
    {
        $Translator = $this->app->translator();
        $Translator->setLanguage($language);
        $Translator->setVersion($version);
        return $Translator;
    }

    /**
     * Compiles a single page based on the specified language, version, and section.
     *
     * @param string $language The language of the page to compile.
     * @param string $version The version of the page to compile.
     * @param string $section The section of the page to compile.
     *
     * @return void
     */
    public function compileSinglePage(string $language, string $version, string $section)
    {
        $app = $this->app;

        // Check if the language is valid
        if ($this->checkValidLanguage($language) === false) {
            $language = 'en';
        }

        // Check if the version is valid
        if ($this->checkValidVersion($version) === false) {
            $version = 'v3';
        }

        $Translator = $this->setupTranslatorService($language, $version);

        $cacheStartTime = microtime(true);
        $cacheHit = true;
        $cacheKey = $section . '_html_' . $language . '_' . $version;
        $markdown_html = $app->cache()->retrieve($cacheKey);
        if ($markdown_html === null) {
            $cacheHit = false;
            $markdown_html = $app->parsedown()->text($Translator->getMarkdownLanguageFile($section . '.md'));
            $markdown_html = Text::addClassesToElements($markdown_html);
            $app->cache()->store($cacheKey, $markdown_html, 86400); // 1 day
        }

        $app->eventDispatcher()->trigger('flight.cache.checked', 'compile_single_page_' . $cacheKey, $cacheHit, microtime(true) - $cacheStartTime);

        $markdown_html = $this->wrapContentInDiv($markdown_html);

        $this->renderPage('single_page.latte', [
            'page_title' => $section,
            'markdown' => $markdown_html,
            'version' => $version,
        ]);
    }

    /**
     * Compiles the Scrollspy page based on the provided language, version, section, and sub-section.
     *
     * @param string $language The language of the documentation.
     * @param string $version The version of the documentation.
     * @param string $section The main section of the documentation.
     * @param string $sub_section The sub-section of the documentation.
     */
    public function compileScrollspyPage(string $language, string $version, string $section, string $sub_section)
    {
        $app = $this->app;

        // Check if the language is valid
        if ($this->checkValidLanguage($language) === false) {
            $language = 'en';
        }

        // Check if the version is valid
        if ($this->checkValidVersion($version) === false) {
            $version = 'v3';
        }

        $Translator = $this->setupTranslatorService($language, $version);

        $section_file_path = str_replace('_', '-', $section);
        $sub_section_underscored = str_replace('-', '_', $sub_section);
        $heading_data = $app->cache()->retrieve($sub_section_underscored . '_heading_data_' . $language . '_' . $version);

        $cacheStartTime = microtime(true);
        $cacheHit = true;
        $cacheKey = $sub_section_underscored . '_html_' . $language . '_' . $version;
        $markdown_html = $app->cache()->retrieve($cacheKey);
        if ($markdown_html === null) {
            $cacheHit = false;
            $markdown_html = $app->parsedown()->text($Translator->getMarkdownLanguageFile('/' . $section_file_path . '/' . $sub_section_underscored . '.md'));

            $heading_data = [];
            $markdown_html = Text::generateAndConvertHeaderListFromHtml($markdown_html, $heading_data, $section_file_path . '/' . $sub_section);
            $markdown_html = Text::addClassesToElements($markdown_html);
            $app->cache()->store($sub_section_underscored . '_heading_data_' . $language . '_' . $version, $heading_data, 86400); // 1 day
            $app->cache()->store($cacheKey, $markdown_html, 86400); // 1 day
        }

        $app->eventDispatcher()->trigger('flight.cache.checked', 'compile_scrollspy_page_' . $cacheKey, $cacheHit, microtime(true) - $cacheStartTime);

        // pull the title out of the first h1 tag
        $page_title = '';
        preg_match('/<h1>(.*)<\/h1>/i', (string) $markdown_html, $matches);

        if (isset($matches[1])) {
            $page_title = $matches[1];
        }

        $markdown_html = $this->wrapContentInDiv($markdown_html);

        // replace any (#some-anchor) with /$section_file_path#some-anchor
        $markdown_html = preg_replace("/\"(#[a-zA-Z\-]+)\"/", "\"/{$section_file_path}/{$sub_section}$1\"", $markdown_html);

        $params = [
            'custom_page_title' => ($page_title ? $page_title . ' - ' : '') . $Translator->translate($section),
            'markdown' => $markdown_html,
            'heading_data' => $heading_data,
            'relative_uri' => '/' . $section_file_path,
            'version' => $version,
        ];

        // Only add learn_sections dropdown if section is 'learn', sub_section is not 'learn', and version is 'v3'
        if ($section === 'learn' && $sub_section !== 'learn' && $version === 'v3') {
            $params['learn_sections'] = $this->getLearnSectionNames($version, $language);
            $params['current_learn_section'] = $sub_section;
        }

        $this->renderPage('single_page_scrollspy.latte', $params);
    }

    /**
     * This is necessary to encapsulate contents (<p>, <pre>, <ol>, <ul>)
     * in a div which can be then styled with CSS thanks to the class name `flight-block`
     *
     * @param string $html
     * @return string
     */
    protected function wrapContentInDiv(string $html): string
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($dom);
        $elements = $xpath->query('//body/*');
        $d = '';
        $div = null;

        foreach ($elements as $element) {
            $elt_html = $element->C14N();
            if (
                $element->parentNode->nodeName === 'body'
                && (
                    $element->nodeName === 'p'
                    || $element->nodeName === 'pre'
                )
            ) {
                if (is_null($div)) {
                    $d .= '<div class="flight-block">';
                    $div = true;
                }
            } elseif (
                $element->parentNode->nodeName === 'body'
                && $element->nodeName === 'h3'
            ) {
                if (is_null($div)) {
                    $d .= '<div class="flight-block">';
                    $div = true;
                } else {
                    $d .= '</div>';
                    $d .= '<div class="flight-block">';
                    $div = true;
                }
            }
            if (
                $element->nodeName !== 'p'
                && $element->nodeName !== 'pre'
                && $element->nodeName !== 'h3'
                && $element->nodeName !== 'h4'
                && $element->nodeName !== 'ol'
                && $element->nodeName !== 'ul'
                && $element->nodeName !== 'blockquote'
                && $element->nodeName !== 'table'
                && $element->nodeName !== 'dl'
                && is_null($div) === false
            ) {
                $d .= '</div>';
                $div = null;
            }
            if ($element->parentNode->nodeName === 'body') {
                $d .= $elt_html;
            }
        }

        if (!is_null($div)) {
            $d .= '</div>';
        }

        return $d;
    }

    /**
     * Checks if the provided language is valid.
     *
     * @param string $language The language code to check.
     * @return bool True if the language is valid, false otherwise.
     */
    public function checkValidLanguage(string $language): bool
    {
        return in_array($language, self::AVAILABLE_LANGUAGES, true) === true;
    }

    /**
     * Checks if the provided version is valid.
     *
     * @param string $version The version code to check.
     * @return bool True if the version is valid, false otherwise.
     */
    public function checkValidVersion(string $version): bool
    {
        return in_array($version, ['v3', 'v2'], true) === true;
    }
}
