<?php

namespace app\controllers;

use app\middleware\HeaderSecurityMiddleware;
use app\utils\Text;
use app\utils\Translator;
use Exception;
use Flight;
use flight\Engine;

class IndexController {

	/**
	 * @var string
	 */
	protected const CONTENT_DIR = __DIR__ . '/../../content/';

	/**
	 * @var string
	 */
	protected string $language = 'en';

	/**
	 * Translator class
	 *
	 * @var Translator
	 */
	protected Translator $Translator;

	/**
	 * @var Engine
	 */
	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
		$this->language = Translator::getLanguageFromRequest();
		$this->Translator = new Translator($this->language);
	}

	protected function renderPage(string $latte_file, array $params = []) {
		$request = $this->app->request();

		$uri = $request->url;
		if(strpos($uri, '?') !== false) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		// Here we can set variables that will be available on any page
		$params['url'] = $request->getScheme() . '://' . $request->getHeader('Host') . $uri;
		$params['nonce'] = HeaderSecurityMiddleware::$nonce;
		$this->app->latte()->render($latte_file, $params);
	}

	protected function compileSinglePage(string $section) {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired($section.'_html_'.$this->language, function() use ($app, $section)  {
			return $app->parsedown()->text($this->Translator->getMarkdownLanguageFile($section . '.md'));
		}, 86400); // 1 day
		$this->renderPage('single_page.latte', [
			'page_title' => $section,
			'markdown' => $markdown_html,
		]);
	}

	protected function compileScrollspyPage(string $section, string $sub_section) {
		$app = $this->app;
		$section_file_path = str_replace('_', '-', $section);
		$sub_section_underscored = str_replace('-', '_', $sub_section);
		$heading_data = $app->cache()->retrieve($sub_section_underscored.'_heading_data_'.$this->language);
		$markdown_html = $app->cache()->refreshIfExpired($sub_section_underscored.'_html_'.$this->language, function() use ($app, $section_file_path, $sub_section_underscored, &$heading_data)  {
			$parsed_text = $app->parsedown()->text($this->Translator->getMarkdownLanguageFile('/'.$section_file_path.'/' . $sub_section_underscored . '.md'));

			$heading_data = [];
			$parsed_text = Text::generateAndConvertHeaderListFromHtml($parsed_text, $heading_data, 'h2');
			$app->cache()->store($sub_section_underscored.'_heading_data_'.$this->language, $heading_data, 86400); // 1 day

			return $parsed_text;
		}, 86400); // 1 day

		// pull the title out of the first h1 tag
		$page_title = '';
		preg_match('/\<h1\>(.*)\<\/h1\>/i', $markdown_html, $matches);
		if (isset($matches[1])) {
			$page_title = $matches[1];
		}

		$Translator = new Translator($this->language);

		$this->renderPage('single_page_scrollspy.latte', [
			'custom_page_title' => ($page_title ? $page_title.' - ' : '').$Translator->translate($section),
			'markdown' => $markdown_html,
			'heading_data' => $heading_data,
		]);
	}

	public function licenseGet() {
		$this->compileSinglePage('license');
	}

	public function aboutGet() {
		$this->compileSinglePage('about');
	}

	public function examplesGet() {
		$this->compileSinglePage('examples');
	}

	public function installGet() {
		$this->compileSinglePage('install');
	}

	public function learnGet() {
		$this->compileSinglePage('learn');
	}

	public function learnSectionsGet(string $section_name) {
		$this->compileScrollspyPage('learn', $section_name);
	}

	public function awesomePluginsGet() {
		$this->compileScrollspyPage('awesome_plugins', 'awesome_plugins');
	}

	public function pluginGet(string $plugin_name) {
		$this->compileScrollspyPage('awesome_plugins', $plugin_name);
	}

	// This is if you want to save everything into a single page
	public function singlePageGet() {
		$app = $this->app;
		// recursively look through all the content files, and pull out each section and render it
		$sections = [];
		$language_directory = self::CONTENT_DIR . $this->language . '/';
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($language_directory));
		foreach($files as $file) {
			if($file->isDir()) {
				continue;
			}
			$section = str_replace([ $language_directory, '.md' ], [ '', '' ], $file->getPathname());
			$sections[] = $section;
		}

		$markdown_html = $app->cache()->refreshIfExpired('single_page_html_'.$this->language, function() use ($app, $sections)  {
			$markdown_html = '';
			foreach($sections as $section) {
				$slugged_section = Text::slugify( $section );
				$markdown_html .= '<h1><a href="/'.$section.'" id="'. $slugged_section.'">'.ucwords($section).'</a> <a href="#' . $slugged_section . '" class="bi bi-link-45deg" title="Permalink to this heading"></a></h1>';
				$markdown_html .= $app->parsedown()->text($this->Translator->getMarkdownLanguageFile($section . '.md'));
			}
			return $markdown_html;
		}, 86400); // 1 day
		$this->renderPage('single_page.latte', [
			'page_title' => 'single_page_documentation',
			'markdown' => $markdown_html,
		]);
	}

	public function searchGet() {
		$query = $this->app->request()->query['query'];
		$language_directory_to_grep = self::CONTENT_DIR . $this->language . '/';
		$grep_command = 'grep -r -i -n --color=never --include="*.md" '.escapeshellarg($query).' '.escapeshellarg($language_directory_to_grep);
		exec($grep_command, $grep_output);

		$files_found = [];
		foreach($grep_output as $line) {
			$line_parts = explode(':', $line);
			$file_path = $line_parts[0];
			$line_number = $line_parts[1];
			$line_content = $line_parts[2];

			// pull the title from the first header tag in the markdown file.
			preg_match('/# (.+)/', file_get_contents($file_path), $matches);
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
				'url' => '/'.str_replace([ $language_directory_to_grep, '.md', '_' ], [ '', '', '-' ], $file_path),
				'hits' => $count
			];
		}

		// sort by descending order by putting $b first
		usort($final_search, fn($a, $b) => $b['hits'] <=> $a['hits']);

		$this->app->json($final_search);
	}

	public function updateStuffPost() {
		$secret = $this->app->get('config')['github_webhook_secret'];
		$request = $this->app->request();
		$signature_header = $request->getVar('HTTP_X_HUB_SIGNATURE');
		$signature_parts = explode('=', $signature_header);
		
        if (count($signature_parts) != 2) {
            throw new Exception('signature has invalid format');
        }
        $known_signature = hash_hmac('sha1', $request->getBody(), $secret);

        if (! hash_equals($known_signature, $signature_parts[1])) {
            throw new Exception('Could not verify request signature ' . $signature_parts[1]);
        }

		// it was successful. Do the stuff
		exec('cd /var/www/flightphp-docs/ && git pull && /usr/bin/php82 /usr/local/bin/composer install --no-progress -o --no-dev && rm -rf app/cache/*');
	}
}