<?php

namespace app\controllers;

use app\utils\CustomEngine;
use flight\Engine;
use Michelf\Markdown;
use Michelf\MarkdownExtra;

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
	 * @var CustomEngine
	 */
	protected CustomEngine $app;

	public function __construct(CustomEngine $app) {
		$this->app = $app;
	}

	public function licenseGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('license_html', function() use ($app)  {
			return $app->parsedown()->text(file_get_contents(self::CONTENT_DIR . $this->language . '/license.md'));
		}, 86400); // 1 day
		$this->app->latte()->render('single_page.latte', [
			'page_title' => 'License',
			'markdown' => $markdown_html,
		]);
	}

	public function aboutGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('about_html', function() use ($app)  {
			return $app->parsedown()->text(file_get_contents(self::CONTENT_DIR . $this->language . '/about.md'));
		}, 86400); // 1 day
		$this->app->latte()->render('single_page.latte', [
			'page_title' => 'About',
			'markdown' => $markdown_html,
		]);
	}

	public function installGet() {
		$app = $this->app;
		$markdown_html = $app->cache()->refreshIfExpired('install_html', function() use ($app)  {
			return $app->parsedown()->text(file_get_contents(self::CONTENT_DIR . $this->language . '/install.md'));
		}, 86400); // 1 day
		$this->app->latte()->render('single_page.latte', [
			'page_title' => 'Installation',
			'markdown' => $markdown_html,
		]);
	}

	public function learnGet() {
		$app = $this->app;
		$heading_data = $app->cache()->retrieve('learn_heading_data');
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
				$text .= file_get_contents(self::CONTENT_DIR . $this->language . '/' . $file) . "\n\n";
			}
			$parsed_text = $app->parsedown()->text($text);

			// This function expects the input to be UTF-8 encoded.
			$slugify = function($text) {
				// Swap out Non "Letters" with a -
				$text = preg_replace('/[^\\pL\d]+/u', '-', $text); 
			
				// Trim out extra -'s
				$text = trim($text, '-');
			
				// Convert letters that we have left to the closest ASCII representation
				$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
			
				// Make text lowercase
				$text = strtolower($text);
			
				// Strip out anything we haven't been able to convert
				$text = preg_replace('/[^-\w]+/', '', $text);
			
				return $text;
			};

			// Find all the heading tags and add an id attribute to them
			$parsed_text = preg_replace_callback( '/(\<h[1](.*?))\>(.*)(<\/h[1]>)/i', function( $matches ) use ($slugify, &$heading_data, &$last_h1) {
				if ( ! stripos( $matches[0], 'id=' ) ) {
					$title = strip_tags( $matches[3] );
					$slugged_title = $slugify( $title );
					$heading_data[$slugged_title] = [ 'title' => $title, 'id' => $slugged_title, 'type' => $matches[2] ];
					$matches[0] = $matches[1] . $matches[2] . ' id="' . $slugged_title . '">' . $title . $matches[4];
				}
				return $matches[0];
			}, $parsed_text );
			$app->cache()->store('learn_heading_data', $heading_data, 86400); // 1 day
			return $parsed_text;
		}, 86400); // 1 day
		$this->app->latte()->render('single_page_scrollspy.latte', [
			'page_title' => 'Learn',
			'markdown' => $markdown_html,
			'heading_data' => $heading_data,
		]);
	}
}