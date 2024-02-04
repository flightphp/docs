<?php

// This script is used to translate all markdown files from English to the specified languages

// You'll need your own chatgpt key to run this script
$chatgpt_key = getenv('CHATGPT_KEY');

$languages = [
	'es',
	'fr',
	'lv',
	'pt',
	'de'
];

$top_level_files = glob(__DIR__ . '/content/en/*.md');

$files = array_merge($top_level_files, glob(__DIR__ . '/content/en/**/*.md'));

// pull all markdown files our of the content/en/ folder and each subdirectory
foreach($files as $file) {
	echo $file . PHP_EOL;
	
	foreach($languages as $languageAbbreviation) {

		$full_response = '';
		$messages = [
			[
				"role" => "system",
				"content" => "You are a gifted translator focusing on the tech space. You are going to receive content that is like a markdown file. When you receive the content you'll translate it from english to the two letter language code that is specified. When you generate a response, you are going to ONLY send back the translated markdown content, no other replies or 'here is your translated markdown' type statements back, only the translated markdown content in markdown format. If you get a follow up response, you need to continue to markdown translation from the very character you left off at and complete the translation until the full page is done. THIS NEXT ITEM IS VERY IMPORTANT! Make sure that when you are translating any code in the markdown file that you ONLY translate the comments of the code and not the classes/methods/variables/links/urls/etc. I will lose my job if this is not done correctly!"
			],
			[
				"role" => "user",
				"content" => "Translate the following text from English to the two letter language code of {$languageAbbreviation}:\n\n" . file_get_contents($file)
			]
		];
		do {
			$ch = curl_init('https://api.openai.com/v1/chat/completions');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
				"model" => "gpt-3.5-turbo-0125",
				"messages" => $messages
			]));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $chatgpt_key,
				'Content-Type: application/json'
			]);
			$response = curl_exec($ch);
			curl_close($ch);
			
			$response = json_decode($response, true);

			$full_response .= $response['choices'][0]['message']['content'];

			$messages[] = [
				'role' => 'assistant',
				'content' => $response['choices'][0]['message']['content']
			];

		} while($response['usage']['completion_tokens'] === 4096);

		// save the translated content to the appropriate file
		$translatedFilePath = str_replace('/en/', '/' . $languageAbbreviation . '/', $file);

		$directory = dirname($translatedFilePath);
		if(is_dir($directory) === false) {
			mkdir($directory, 0775, true);
		}
		file_put_contents($translatedFilePath, $full_response);

		echo "Updated: " . $translatedFilePath . PHP_EOL;
	}
}