<?php

namespace app\utils;

use app\middleware\HeaderSecurityMiddleware;
use DOMDocument;
use DOMXPath;

class DocsLogic {

	/** @var string */
	private const DS = DIRECTORY_SEPARATOR;

	/** @var string Path to the base content directory */
	public const CONTENT_DIR = __DIR__ . self::DS . '..' . self::DS . '..' . self::DS . 'content' . self::DS;

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
	public function __construct(protected $app) {
	}

	/**
	 * Returns a list of all learn section names (without .md extension).
	 *
	 * @return array List of section names
	 */
	public function getLearnSectionNames(): array {
		return [
			'Core Components' => [
				['url' => '/learn/routing', 'title' => 'Routing'],
				['url' => '/learn/middleware', 'title' => 'Middleware'],
				['url' => '/learn/autoloading', 'title' => 'Autoloading'],
				['url' => '/learn/requests', 'title' => 'Requests'],
				['url' => '/learn/responses', 'title' => 'Responses'],
				['url' => '/learn/templates', 'title' => 'HTML Templates'],
				['url' => '/learn/security', 'title' => 'Security'],
				['url' => '/learn/configuration', 'title' => 'Configuration'],
				['url' => '/learn/events', 'title' => 'Event Manager'],
				['url' => '/learn/extending', 'title' => 'Extending Flight'],
				['url' => '/learn/filtering', 'title' => 'Method Hooks and Filtering'],
				['url' => '/learn/dependency-injection-container', 'title' => 'Dependency Injection'],
			],
			'Utility Classes' => [
				['url' => '/learn/collections', 'title' => 'Collections'],
				['url' => '/learn/json', 'title' => 'JSON Wrapper'],
				['url' => '/learn/pdo-wrapper', 'title' => 'PDO Wrapper'],
				['url' => '/learn/uploaded-file', 'title' => 'Uploaded File Handler'],
			],
			'Important Concepts' => [
				['url' => '/learn/why-frameworks', 'title' => 'Why a Framework?'],
				['url' => '/learn/flight-vs-another-framework', 'title' => 'Flight vs Others'],
			],
			'Other Topics' => [
				['url' => '/learn/unit-testing', 'title' => 'Unit Testing'],
				['url' => '/learn/ai', 'title' => 'AI & Developer Experience'],
				['url' => '/learn/migrating-to-v3', 'title' => 'Migrating v2 -> v3'],
			]
		];
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

		$startTime = microtime(true);
		if (!empty($params['raw_markdown']) && (str_contains($request->header('Accept'), 'text/plain') || str_contains($request->header('Accept'), 'text/markdown'))) {
			$this->app->response()->header('Content-Type', 'text/markdown; charset=utf-8');
			$this->app->response()->write($params['raw_markdown']);
		} else {

			// Here we can set variables that will be available on any page
			$params['url'] = $request->getScheme() . '://' . $request->getHeader('Host') . $uri;
			$params['nonce'] = HeaderSecurityMiddleware::$nonce;
			$params['q'] = $request->query['q'] ?? '';

			$this->app->latte()->render($latte_file, $params);
		}

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
		$rawMarkdown = $Translator->getMarkdownLanguageFile($section . '.md');
		if ($markdown_html === null) {
			$cacheHit = false;
			$markdown_html = $app->parsedown()->text($rawMarkdown);
			$markdown_html = Text::addClassesToElements($markdown_html);
			$app->cache()->store($cacheKey, $markdown_html, 86400); // 1 day
		}

		$app->eventDispatcher()->trigger('flight.cache.checked', 'compile_single_page_' . $cacheKey, $cacheHit, microtime(true) - $cacheStartTime);

		$markdown_html = $this->wrapContentInDiv($markdown_html);

		$this->renderPage('single_page.latte', [
			'page_title' => $section,
			'markdown' => $markdown_html,
			'version' => $version,
			'language' => $language,
			'raw_markdown' => $rawMarkdown,
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
		$rawMarkdown = $Translator->getMarkdownLanguageFile('/' . $section_file_path . '/' . $sub_section_underscored . '.md');
		if ($markdown_html === null) {
			$cacheHit = false;
			$markdown_html = $app->parsedown()->text($rawMarkdown);

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
			'raw_markdown' => $rawMarkdown,
			'markdown' => $markdown_html,
			'heading_data' => $heading_data,
			'relative_uri' => '/' . $section_file_path,
			'version' => $version,
			'language' => $language,
		];

		// Only add learn_sections dropdown if section is 'learn', sub_section is not 'learn', and version is 'v3'
		if ($section === 'learn' && $sub_section !== 'learn' && $version === 'v3') {
			$params['learn_sections'] = $this->getLearnSectionNames();
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
					|| $element->nodeName === 'ul'
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
				&& $element->nodeName !== 'h5'
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
	public function checkValidLanguage(string $language): bool {
		return in_array($language, self::AVAILABLE_LANGUAGES, true) === true;
	}

	/**
	 * Checks if the provided version is valid.
	 * 
	 * @param string $version The version code to check.
	 * @return bool True if the version is valid, false otherwise.
	 */
	public function checkValidVersion(string $version): bool {
		return in_array($version, ['v3', 'v2'], true) === true;
	}

	/**
	 * Executes a search based on the provided query, language, and version.
	 *
	 * @param string $query    The search query string.
	 * @param string $language The language code for the search (default: 'en').
	 * @param string $version  The documentation version to search in (default: 'v3').
	 * @return array           An array of search results.
	 */
	public function runSearch(string $query, string $language = 'en', string $version = 'v3'): array {

		// if the query is less than 3 characters, return empty array
		if (strlen($query) < 3) {
			return [
				'error' => 'Search query must be at least 3 characters long.',
			];
		}

		$language_directory_to_grep = self::CONTENT_DIR . $version . self::DS . $language . self::DS;
		$grep_command = 'grep -r -i -n --color=never --include="*.md" ' . escapeshellarg($query) . ' ' . escapeshellarg($language_directory_to_grep);
		exec($grep_command, $grep_output);

		$files_found = [];
		foreach ($grep_output as $line) {
			$line_parts = explode(':', $line);
			// Catch the windows C drive letter
			if ($line_parts[0] === 'C') {
				array_shift($line_parts);
			}
			$file_path = str_replace('/', self::DS, $line_parts[0]);
			$line_number = $line_parts[1];
			$line_content = $line_parts[2];

			// Skip test files
			if (substr($file_path, -9) === '__test.md') {
				continue;
			}

			$file_contents = file_exists($file_path)
				? file_get_contents($file_path)
				: '';

			// pull the title from the first header tag in the markdown file.
			preg_match('/# (.+)/', $file_contents, $matches);
			if (empty($matches[1])) {
				continue;
			}
			$title = $matches[1];

			// convert markdown to html and then strip tags to get plain text
			$file_contents = strip_tags($this->app->parsedown()->text($file_contents));

			// need to pull out the first 300 characters of the markdown file $file_contents that contains the search term
			// after skipping the first # heading
			// only do the starting and ending positions at word boundaries
			// so we don't cut off in the middle of a word

			$found_pos = stripos($file_contents, $query);
			if ($found_pos === false) {
				continue;
			}

			// Candidate window centered (approx) around the match
			$start_candidate = max(0, $found_pos - 200);
			$file_len = strlen($file_contents);

			// Find first space at or after start_candidate in a safe way
			$search_offset = min(max(0, $start_candidate), max(0, $file_len - 1));
			$space_pos = strpos($file_contents, ' ', $search_offset);
			if ($space_pos === false) {
				$start_pos = 0;
			} else {
				$start_pos = $space_pos;
			}

			// Determine end position safely: try to find a space at start_pos + 400,
			// but if that offset is past the string, just use the string end.
			$end_search_offset = $start_pos + 400;
			if ($end_search_offset >= $file_len) {
				$end_pos = $file_len;
			} else {
				$end_pos = strpos($file_contents, ' ', $end_search_offset);
				if ($end_pos === false) {
					$end_pos = $file_len;
				}
			}

			$end_pos = min($end_pos, $file_len);
			$start_pos = max(0, $end_pos - 400);

			// Ensure start_pos is a valid offset for strpos()
			$search_offset2 = min(max(0, $start_pos), max(0, $file_len - 1));
			$space_pos2 = strpos($file_contents, ' ', $search_offset2);
			if ($space_pos2 === false) {
				$start_pos = 0;
			} else {
				$start_pos = $space_pos2;
			}

			$excerpt = substr($file_contents, $start_pos, 400);
			$excerpt = preg_replace('/# .+/', '', $excerpt); // remove any
			$excerpt = '...' . trim($excerpt);
			if (strlen($excerpt) > 400) {
				$excerpt = substr($excerpt, 0, 397) . '...';
			}

			// bold the search term in the excerpt
			$excerpt = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<b class="text-info">$1</b>', $excerpt);

			$files_found[$file_path][] = [
				'line_number' => $line_number,
				'line_content' => $line_content,
				'page_name' => $title,
				'excerpt' => $excerpt,
			];
		}

		$final_search = [];
		foreach ($files_found as $file_path => $data) {
			$count = count($files_found[$file_path]);
			$final_search[] = [
				'page_name' => $data[0]['page_name'],
				'search_result' => $data[0]['page_name'] . ' ("' . $query . '" ' . $count . 'x)',
				'url' => '/' . $language . '/' . $version . '/' . str_replace([$language_directory_to_grep, '.md', '_', '\\'], ['', '', '-', '/'], $file_path),
				'hits' => $count,
				'excerpt' => $data[0]['excerpt'],
			];
		}

		// sort by descending order by putting $b first
		usort($final_search, fn($a, $b) => $b['hits'] <=> $a['hits']);

		return $final_search;
	}
}
