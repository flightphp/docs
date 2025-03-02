<?php

namespace app\controllers;

use app\middleware\HeaderSecurityMiddleware;
use app\utils\Text;
use app\utils\Translator;
use Exception;
use flight\Engine;
use DOMDocument;
use DOMXPath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class IndexController {

	/** @var string */
    private const DS = DIRECTORY_SEPARATOR;

	/** @var string Path to the base content directory */
    protected const CONTENT_DIR = __DIR__ . self::DS . '..' . self::DS . '..' . self::DS . 'content' . self::DS;

	/**
	 * IndexController constructor.
	 *
	 * @param Engine $app Flight Engine
	 */
    public function __construct(protected Engine $app) {
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
	protected function setupTranslatorService(string $language, string $version): Translator {
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
    protected function compileSinglePage(string $language, string $version, string $section) {
        $app = $this->app;

		$Translator = $this->setupTranslatorService($language, $version);

        $markdown_html = $app->cache()->refreshIfExpired($section . '_html_' . $language . '_' . $version, fn() => $app->parsedown()->text($Translator->getMarkdownLanguageFile($section . '.md')), 86400); // 1 day

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
    protected function compileScrollspyPage(string $language, string $version, string $section, string $sub_section) {
        $app = $this->app;

		$Translator = $this->setupTranslatorService($language, $version);

        $section_file_path = str_replace('_', '-', $section);
        $sub_section_underscored = str_replace('-', '_', $sub_section);
        $heading_data = $app->cache()->retrieve($sub_section_underscored . '_heading_data_' . $language);
        $markdown_html = $app->cache()->refreshIfExpired($sub_section_underscored . '_html_' . $language . '_' . $version, function () use ($app, $section_file_path, $sub_section, $sub_section_underscored, &$heading_data, $language, $Translator) {
            $parsed_text = $app->parsedown()->text($Translator->getMarkdownLanguageFile('/' . $section_file_path . '/' . $sub_section_underscored . '.md'));

            $heading_data = [];
            $parsed_text = Text::generateAndConvertHeaderListFromHtml($parsed_text, $heading_data, 'h2', $section_file_path.'/'.$sub_section);
            $app->cache()->store($sub_section_underscored . '_heading_data_' . $language, $heading_data, 86400); // 1 day

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
	 * Handles the retrieval of the license page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function licenseGet(string $language, string $version) {
        $this->compileSinglePage($language, $version, 'license');
    }

	/**
	 * Handles the retrieval of the about page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function aboutGet(string $language, string $version) {
        $this->compileSinglePage($language, $version, 'about');
    }

	/**
	 * Handles the retrieval of the examples page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function examplesGet(string $language, string $version) {
        $this->compileSinglePage($language, $version, 'examples');
    }

	/**
	 * Handles the retrieval of the install page.
	 * 
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function installGet(string $language, string $version) {
		if($version === 'v2') {
			$this->compileSinglePage($language, $version, 'install');
		} else {
        	$this->compileScrollspyPage($language, $version, 'install', 'install');
		}
    }

	/**
	 * Handles the retrieval of the learn page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function learnGet(string $language, string $version) {
		if($version === 'v2') {
			$this->compileScrollspyPage($language, $version, 'learn', 'learn');
		} else {
			$this->compileSinglePage($language, $version, 'learn');
		}
    }

	/**
	 * Handles the retrieval of the media page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function mediaGet(string $language, string $version) {
        $this->compileSinglePage($language, $version, 'media');
    }

	/**
	 * Handles the retrieval of the section within the learn page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function learnSectionsGet(string $language, string $version, string $section_name) {
        $this->compileScrollspyPage($language, $version, 'learn', $section_name);
    }

	/**
	 * Handles the retrieval of the awesome plugins page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function awesomePluginsGet(string $language, string $version) {
        $this->compileScrollspyPage($language, $version, 'awesome_plugins', 'awesome_plugins');
    }

	/**
	 * Handles the retrieval of the section within the awesome plugins page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function pluginGet(string $language, string $version, string $plugin_name) {
        $this->compileScrollspyPage($language, $version, 'awesome_plugins', $plugin_name);
    }

	/**
	 * This is if you want all documentation to be viewable in a single page
	 *
	 * @param string $language The language of the page.
	 * @param string $version The version of the page.
	 */
    public function singlePageGet(string $language, string $version) {
        $app = $this->app;

        // recursively look through all the content files, and pull out each section and render it
        $sections = [];
        $language_directory = self::CONTENT_DIR . '/' . $version . '/' . $language . '/';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($language_directory));

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $section = str_replace([$language_directory, '.md'], ['', ''], $file->getPathname());
            $sections[] = $section;
        }

		$Translator = $this->setupTranslatorService($language, $version);

        $markdown_html = $app->cache()->refreshIfExpired('single_page_html_' . $language . '_' . $version, function () use ($app, $sections, $Translator) {
            $markdown_html = '';

            foreach ($sections as $section) {
                $slugged_section = Text::slugify($section);
                $markdown_html .= '<h1><a href="/' . $section . '" id="' . $slugged_section . '">' . ucwords($section) . '</a> <a href="/' . $section . '#' . $slugged_section . '" class="bi bi-link-45deg" title="Permalink to this heading"></a></h1>';
                $markdown_html .= $app->parsedown()->text($Translator->getMarkdownLanguageFile($section . '.md'));
            }

            return $markdown_html;
        }, 86400); // 1 day

        $this->renderPage('single_page.latte', [
            'page_title' => 'single_page_documentation',
            'markdown' => $markdown_html,
			'version' => $version,
        ]);
    }

	/**
	 * Handles the GET request for searching documentation.
	 *
	 * @param string $language The language of the documentation.
	 * @param string $version The version of the documentation.
	 */
    public function searchGet(string $language, string $version) {
        $query = $this->app->request()->query['query'];
        $language_directory_to_grep = self::CONTENT_DIR . $version . self::DS . $language . self::DS;
        $grep_command = 'grep -r -i -n --color=never --include="*.md" '.escapeshellarg($query).' '.escapeshellarg($language_directory_to_grep);
        exec($grep_command, $grep_output);

        $files_found = [];
        foreach($grep_output as $line) {
            $line_parts = explode(':', $line);
            // Catch the windows C drive letter
            if($line_parts[0] === 'C') {
                array_shift($line_parts);
            }
            $file_path = str_replace('/', self::DS, $line_parts[0]);
            $line_number = $line_parts[1];
            $line_content = $line_parts[2];

            $file_contents = file_exists($file_path)
                ? file_get_contents($file_path)
                : '';

            // pull the title from the first header tag in the markdown file.
            preg_match('/# (.+)/', $file_contents, $matches);
            if(empty($matches[1])) {
                continue;
            }
            $title = $matches[1];
            $files_found[$file_path][] = [
                'line_number' => $line_number,
                'line_content' => $line_content,
                'page_name' => $title
            ];
        }

        $final_search = [];
        foreach($files_found as $file_path => $data) {
            $count = count($files_found[$file_path]);
            $final_search[] = [
                'search_result' => $data[0]['page_name'].' ("'.$query.'" '.$count.'x)',
                'url' => '/'.$language.'/'.$version.'/'.str_replace([ $language_directory_to_grep, '.md', '_', '\\' ], [ '', '', '-', '/' ], $file_path),
                'hits' => $count
            ];
        }

        // sort by descending order by putting $b first
        usort($final_search, fn($a, $b) => $b['hits'] <=> $a['hits']);

        $this->app->json($final_search);
    }

	/**
	 * Handles the POST request to update this repo from a webhook from GitHub
	 *
	 * @return void
	 */
    public function updateStuffPost() {
        $secret = $this->app->get('config')['github_webhook_secret'];
        $request = $this->app->request();
        $signature_header = $request->getVar('HTTP_X_HUB_SIGNATURE');
        $signature_parts = explode('=', (string) $signature_header);

        if (count($signature_parts) != 2) {
            throw new Exception('signature has invalid format');
        }

        $known_signature = hash_hmac('sha1', $request->getBody(), (string) $secret);

        if (! hash_equals($known_signature, $signature_parts[1])) {
            throw new Exception('Could not verify request signature ' . $signature_parts[1]);
        }

        // it was successful. Pull the latest changes and update the composer dependencies
        exec('cd /var/www/flightphp-docs/ && git pull && /usr/bin/php82 /usr/local/bin/composer install --no-progress -o --no-dev && rm -rf app/cache/*');
    }
}
