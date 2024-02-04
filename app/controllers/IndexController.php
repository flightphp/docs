<?php

namespace app\controllers;

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
		$this->app->latte()->render($latte_file, $params);
	}

	public function licenseGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('license_html', function() use ($app)  {
			return $app->parsedown()->text((self::CONTENT_DIR . $this->language . '/license.md'));
		}, 86400); // 1 day
		$this->renderPage('single_page.latte', [
			'page_title' => 'license',
			'markdown' => $markdown_html,
		]);
	}

	public function aboutGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('about_html_'.$this->language, function() use ($app)  {
			return $app->parsedown()->text($this->Translator->getMarkdownLanguageFile('about.md'));
		}, 86400); // 1 day
		$this->renderPage('single_page.latte', [
			'page_title' => 'about',
			'markdown' => $markdown_html,
		]);
	}

	public function examplesGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('examples_html_'.$this->language, function() use ($app)  {
			return $app->parsedown()->text($this->Translator->getMarkdownLanguageFile('examples.md'));
		}, 86400); // 1 day
		$this->renderPage('single_page.latte', [
			'page_title' => 'examples',
			'markdown' => $markdown_html,
		]);
	}
	public function installGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('install_html_'.$this->language, function() use ($app)  {
			return $app->parsedown()->text(file_get_contents(self::CONTENT_DIR . $this->language . '/install.md'));
		}, 86400); // 1 day
		$this->renderPage('single_page.latte', [
			'page_title' => 'install',
			'markdown' => $markdown_html,
		]);
	}

	public function learnGet() {
		$app = $this->app;
		$heading_data = $app->cache()->retrieve('learn_heading_data_'.$this->language);
		$markdown_html = $app->cache()->refreshIfExpired('learn_html', function() use ($app, &$heading_data)  {
			$learn_files_order = [
				'routing.md',
				'extending.md',
				'overriding.md',
				'filtering.md',
				'variables.md',
				'views.md',
				'errorhandling.md',
				'redirects.md',
				'requests.md',
				'stopping.md',
				'httpcaching.md',
				'json.md',
				'configuration.md',
				'frameworkmethods.md',
				'frameworkinstance.md'
			];
			$text = '';
			foreach($learn_files_order as $file) {
				$text .= file_get_contents(self::CONTENT_DIR . $this->language . '/learn/' . $file) . "\n\n";
			}
			$parsed_text = $app->parsedown()->text($text);

			// Find all the heading tags and add an id attribute to them
			$heading_data = [];
			$parsed_text = Text::generateAndConvertHeaderListFromHtml($parsed_text, $heading_data, 'h[12]');
			$app->cache()->store('learn_heading_data_'.$this->language, $heading_data, 86400); // 1 day
			return $parsed_text;
		}, 86400); // 1 day
		$this->renderPage('single_page_scrollspy.latte', [
			'page_title' => 'learn',
			'markdown' => $markdown_html,
			'heading_data' => $heading_data,
		]);
	}

	public function awesomePluginsGet() {
		$app = $this->app;
		$heading_data = $app->cache()->retrieve('plugins_heading_data_'.$this->language);
		$markdown_html = $app->cache()->refreshIfExpired('plugins_html', function() use ($app, &$heading_data)  {
			$parsed_text = $app->parsedown()->text(file_get_contents(self::CONTENT_DIR . $this->language . '/awesome-plugins/index.md'));
			$heading_data = [];
			$parsed_text = Text::generateAndConvertHeaderListFromHtml($parsed_text, $heading_data, 'h2');
			$app->cache()->store('plugins_heading_data_'.$this->language, $heading_data, 86400); // 1 day
			return $parsed_text;
		}, 86400); // 1 day

		$this->renderPage('single_page_scrollspy.latte', [
			'page_title' => 'awesome_plugins',
			'markdown' => $markdown_html,
			'heading_data' => $heading_data,
		]);
	}

	public function pluginGet(string $plugin_name) {
		$app = $this->app;
		$plugin_name_underscored = str_replace('-', '_', $plugin_name);
		$heading_data = $app->cache()->retrieve($plugin_name_underscored.'_heading_data_'.$this->language);
		$markdown_html = $app->cache()->refreshIfExpired($plugin_name_underscored.'_html', function() use ($app, $plugin_name_underscored, &$heading_data)  {
			$parsed_text = $app->parsedown()->text(file_get_contents(self::CONTENT_DIR . $this->language . '/awesome-plugins/' . $plugin_name_underscored . '.md'));

			$heading_data = [];
			$parsed_text = Text::generateAndConvertHeaderListFromHtml($parsed_text, $heading_data, 'h2');
			$app->cache()->store($plugin_name_underscored.'_heading_data_'.$this->language, $heading_data, 86400); // 1 day

			return $parsed_text;
		}, 86400); // 1 day

		// pull the title out of the first h1 tag
		$plugin_title = '';
		preg_match('/\<h1\>(.*)\<\/h1\>/i', $markdown_html, $matches);
		if (isset($matches[1])) {
			$plugin_title = $matches[1];
		}

		$Translator = new Translator($this->language);

		$this->renderPage('single_page_scrollspy.latte', [
			'custom_page_title' => $plugin_title.' - '.$Translator->translate('awesome_plugins'),
			'markdown' => $markdown_html,
			'heading_data' => $heading_data,
		]);
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