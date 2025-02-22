<?php

declare(strict_types=1);

namespace app\utils;

class Text {
    public static function slugify(string $text) {
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

    public static function generateAndConvertHeaderListFromHtml(string $markdown_html, array &$heading_data = [], $heading_tag = 'h1') {
        $markdown_html = preg_replace_callback('/(\<' . $heading_tag . '(.*?))\>(.*)(<\/' . $heading_tag . '>)/i', function ($matches) use (&$heading_data) {
            if (! stripos($matches[0], 'id=')) {
                $title = strip_tags($matches[3]);
                $slugged_title = Text::slugify($title);
                $heading_data[$slugged_title] = ['title' => $title, 'id' => $slugged_title, 'type' => $matches[2]];
                $matches[0] = $matches[1] . $matches[2] . ' id="' . $slugged_title . '">' . $title . ' <a href="#' . $slugged_title . '" class="bi bi-link-45deg" title="Permalink to this heading"></a>' . $matches[4];
            }

            return $matches[0];
        }, $markdown_html);

        return $markdown_html;
    }
}
