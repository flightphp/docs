<?php

declare(strict_types=1);

namespace app\utils;

class Text {

	/**
	 * Slugifies strings that are safe for URLs
	 *
	 * @param string $text [description]
	 * @return string
	 */
    public static function slugify(string $text): string  {
        // Swap out Non "Letters" with a -
        $text = preg_replace('/[^\\pL\d]+/u', '-', $text);

        // Trim out extra -'s
        $text = trim((string) $text, '-');

        // Convert letters that we have left to the closest ASCII representation
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Make text lowercase
        $text = strtolower($text);

        // Strip out anything we haven't been able to convert
        $text = preg_replace('/[^\-\w]+/', '', $text);

        return $text;
    }

	/**
	 * Generates the header list on the side for scrollspy
	 *
	 * @param string $markdown_html     [description]
	 * @param array  $heading_data      [description]
	 * @param string $heading_tag       [description]
	 * @param string $section_file_path [description]
	 * @return string
	 */
	public static function generateAndConvertHeaderListFromHtml(string $markdown_html, array &$heading_data = [], string $section_file_path): string {
		$heading_data = [];
		$last_h2_key = null;

		// First, process h2s and h3s in order
		$markdown_html = preg_replace_callback('/(<h[23](.*?))>(.*)(<\/h[23]>)/i', function ($matches) use (&$heading_data, &$last_h2_key, $section_file_path) {
			$tag = '';
			if (stripos($matches[0], '<h2') === 0) {
				$tag = 'h2';
			} elseif (stripos($matches[0], '<h3') === 0) {
				$tag = 'h3';
			}
			$title = strip_tags($matches[3]);
			$rawTitle = $matches[3];
			$slugged_title = Text::slugify($title);
			// if $slugged_title starts with a number, prepend it with 'section-'
			if (preg_match('/^\d/', $slugged_title)) {
				$slugged_title = 'section-' . $slugged_title;
			}
			$id_attr = 'id="' . $slugged_title . '"';
			$permalink = ' <a href="/'.$section_file_path.'#' . $slugged_title . '" class="bi bi-link-45deg" title="Permalink to this heading"></a>';

			// if the $rawTitle has a <a> tag in it, skip the permalink cause it is a link...
			if(strpos($rawTitle, '<a') !== false) {
				$permalink = '';
			}
			if ($tag === 'h2') {
				$heading_data[$slugged_title] = [
					'title' => $title,
					'id' => $slugged_title,
					'type' => $matches[2],
					'children' => []
				];
				$last_h2_key = $slugged_title;
				// Add id and permalink to h2
			} elseif ($tag === 'h3' && $last_h2_key !== null) {
				// Add h3 as child of last h2
				$heading_data[$last_h2_key]['children'][] = [
					'title' => $title,
					'id' => $slugged_title,
					'type' => $matches[2]
				];
				// Add id and permalink to h3
			} else {
				// If h3 appears before any h2, treat as top-level (rare)
				$heading_data[$slugged_title] = [
					'title' => $title,
					'id' => $slugged_title,
					'type' => $matches[2],
					'children' => []
				];
			}
			$new_html = $matches[1] . $matches[2] . ' ' . $id_attr . '>' . $rawTitle . $permalink . $matches[4];

			return $new_html;
		}, $markdown_html);

		return $markdown_html;
	}

	/**
	 * Adds classes to elements in the html for styling
	 *
	 * @param string $html html
	 * @return string
	 */
	public static function addClassesToElements(string $html): string {
		// add class="table" to all <table> elements
		$html = preg_replace('/<table(.*?)>/', '<table$1 class="table table-striped">', $html);
		return $html;
	}
}
