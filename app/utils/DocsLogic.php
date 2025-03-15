<?php

namespace app\utils;

use app\middleware\HeaderSecurityMiddleware;
use DOMDocument;
use DOMXPath;
use flight\Engine;

class DocsLogic {
	public function __construct(protected Engine $app) {
        //
	}

	/**
	 * Renders a page using the specified Latte template file and parameters.
	 *
	 * @param string $latte_file The path to the Latte template file to be rendered.
	 * @param array $params An optional array of parameters to be passed to the template.
	 */
    public function renderPage(string $latte_file, array $params = []) {
        $request = $this->app->request();
        $uri = $request->url;

        if (str_contains($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Here we can set variables that will be available on any page
        $params['url'] = $request->getScheme() . '://' . $request->getHeader('Host') . $uri;
        $params['nonce'] = HeaderSecurityMiddleware::$nonce;
        $this->app->latte()->render($latte_file, $params);
    }

	/**
	 * Sets up the translator service with the specified language and version.
	 *
	 * @param string $language The language to be used by the translator.
	 * @param string $version The version of the translation service.
	 * @return Translator The configured translator service.
	 */
	public function setupTranslatorService(string $language, string $version): Translator {
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
    public function compileSinglePage(string $language, string $version, string $section) {
        $app = $this->app;

		$Translator = $this->setupTranslatorService($language, $version);

        $markdown_html = $app->cache()->refreshIfExpired($section . '_html_' . $language . '_' . $version, function() use ($app, $section, $Translator) {
			$markdown_html = $app->parsedown()->text($Translator->getMarkdownLanguageFile($section . '.md'));
			$markdown_html = Text::addClassesToElements($markdown_html);
			return $markdown_html;
		}, 86400); // 1 day

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
    public function compileScrollspyPage(string $language, string $version, string $section, string $sub_section) {
        $app = $this->app;

		$Translator = $this->setupTranslatorService($language, $version);

        $section_file_path = str_replace('_', '-', $section);
        $sub_section_underscored = str_replace('-', '_', $sub_section);
        $heading_data = $app->cache()->retrieve($sub_section_underscored . '_heading_data_' . $language . '_' . $version);
        $markdown_html = $app->cache()->refreshIfExpired($sub_section_underscored . '_html_' . $language . '_' . $version, function () use ($app, $section_file_path, $sub_section, $sub_section_underscored, &$heading_data, $language, $Translator, $version) {
            $parsed_text = $app->parsedown()->text($Translator->getMarkdownLanguageFile('/' . $section_file_path . '/' . $sub_section_underscored . '.md'));

            $heading_data = [];
            $parsed_text = Text::generateAndConvertHeaderListFromHtml($parsed_text, $heading_data, 'h2', $section_file_path.'/'.$sub_section);
			$parsed_text = Text::addClassesToElements($parsed_text);
            $app->cache()->store($sub_section_underscored . '_heading_data_' . $language . '_' . $version, $heading_data, 86400); // 1 day

            return $parsed_text;
        }, 86400); // 1 day

        // pull the title out of the first h1 tag
        $page_title = '';
        preg_match('/\<h1\>(.*)\<\/h1\>/i', (string) $markdown_html, $matches);

        if (isset($matches[1])) {
            $page_title = $matches[1];
        }

        $markdown_html = $this->wrapContentInDiv($markdown_html);

        // replace any (#some-anchor) with /$section_file_path#some-anchor
        $markdown_html = preg_replace("/\"(#[a-zA-Z\-]+)\"/", "\"/{$section_file_path}/{$sub_section}$1\"", $markdown_html);
        $this->renderPage('single_page_scrollspy.latte', [
            'custom_page_title' => ($page_title ? $page_title . ' - ' : '') . $Translator->translate($section),
            'markdown' => $markdown_html,
            'heading_data' => $heading_data,
            'relative_uri' => '/'.$section_file_path,
			'version' => $version,
        ]);
    }

	/**
     * This is necessary to encapsulate contents (<p>, <pre>, <ol>, <ul>)
     * in a div which can be then styled with CSS thanks to the class name `flight-block`
	 * 
	 * @param string $html
	 * @return string
     */
    protected function wrapContentInDiv(string $html): string {
        $dom = new DOMDocument;
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
}
