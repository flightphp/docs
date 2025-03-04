<?php

namespace app\controllers;

use app\utils\CustomEngine;
use app\utils\DocsLogic;
use app\utils\Text;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DocsController {

	/** @var string */
    private const DS = DIRECTORY_SEPARATOR;

	/** @var string Path to the base content directory */
    protected const CONTENT_DIR = __DIR__ . self::DS . '..' . self::DS . '..' . self::DS . 'content' . self::DS;

	protected DocsLogic $DocsLogic;

	/**
	 * DocsController constructor.
	 *
	 * @param CustomEngine $app Flight Engine
	 */
    public function __construct(protected $app) {
		$this->DocsLogic = new DocsLogic($app);
    }

	/**
	 * Handles the retrieval of the license page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function licenseGet(string $language, string $version) {
        $this->DocsLogic->compileSinglePage($language, $version, 'license');
    }

	/**
	 * Handles the retrieval of the about page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function aboutGet(string $language, string $version) {
        $this->DocsLogic->compileSinglePage($language, $version, 'about');
    }

	/**
	 * Handles the retrieval of the examples page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function examplesGet(string $language, string $version) {
        $this->DocsLogic->compileSinglePage($language, $version, 'examples');
    }

	/**
	 * Handles the retrieval of the install page.
	 * 
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function installGet(string $language, string $version) {
		if($version === 'v2') {
			$this->DocsLogic->compileSinglePage($language, $version, 'install');
		} else {
        	$this->DocsLogic->compileScrollspyPage($language, $version, 'install', 'install');
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
			$this->DocsLogic->compileScrollspyPage($language, $version, 'learn', 'learn');
		} else {
			$this->DocsLogic->compileSinglePage($language, $version, 'learn');
		}
    }

	/**
	 * Handles the retrieval of the media page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function mediaGet(string $language, string $version) {
        $this->DocsLogic->compileSinglePage($language, $version, 'media');
    }

	/**
	 * Handles the retrieval of the section within the learn page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function learnSectionsGet(string $language, string $version, string $section_name) {
        $this->DocsLogic->compileScrollspyPage($language, $version, 'learn', $section_name);
    }

	/**
	 * Handles the retrieval of the awesome plugins page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function awesomePluginsGet(string $language, string $version) {
        $this->DocsLogic->compileScrollspyPage($language, $version, 'awesome_plugins', 'awesome_plugins');
    }

	/**
	 * Handles the retrieval of the section within the awesome plugins page.
	 *
	 * @param string $language The language in which the page is requested.
	 * @param string $version The version of the page to retrieve.
	 */
    public function pluginGet(string $language, string $version, string $plugin_name) {
        $this->DocsLogic->compileScrollspyPage($language, $version, 'awesome_plugins', $plugin_name);
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

		$Translator = $this->DocsLogic->setupTranslatorService($language, $version);

        $markdown_html = $app->cache()->refreshIfExpired('single_page_html_' . $language . '_' . $version, function () use ($app, $sections, $Translator) {
            $markdown_html = '';

            foreach ($sections as $section) {
                $slugged_section = Text::slugify($section);
                $markdown_html .= '<h1><a href="/' . $section . '" id="' . $slugged_section . '">' . ucwords($section) . '</a> <a href="/' . $section . '#' . $slugged_section . '" class="bi bi-link-45deg" title="Permalink to this heading"></a></h1>';
                $markdown_html .= $app->parsedown()->text($Translator->getMarkdownLanguageFile($section . '.md'));
            }

            return $markdown_html;
        }, 86400); // 1 day

        $this->DocsLogic->renderPage('single_page.latte', [
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
        exec('cd /var/www/flightphp-docs/ && git pull && /usr/bin/php82 /usr/local/bin/composer install --no-progress -o --no-dev && rm -rf app/cache/*', $output);
		echo join("\n", $output);
    }
}
