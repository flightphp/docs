<?php

namespace app\commands;

use flight\commands\AbstractBaseCommand;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * @property-read string $fromDate
 * @property-read string $skipFiles
 * @property-read bool $dryRun
 * @property-read int $threads
 */
class TranslateCommand extends AbstractBaseCommand {
    public function __construct(array $config = []) {
        parent::__construct('app:translate', 'Translate markdown documentation files to other languages.', $config);
        $this->option('--from-date', 'Skip files older than this date (YYYY-MM-DD)', null, 0)
            ->option('--skip-files', 'Comma separated list of filenames to skip', null, '')
            ->option('--dry-run', 'Run without making API calls or saving files', null, false)
            ->option('--threads', 'Number of concurrent translation requests', null, 5);
    }

    public function execute() {
        $io = $this->app()->io();
        $chatgpt_key = $this->config['chatgpt_key'] ?? '';

        if (empty($chatgpt_key)) {
            // fallback to env if not in config
            $chatgpt_key = getenv('CHATGPT_KEY');
        }

        if (empty($chatgpt_key) && !$this->dryRun) {
            $io->error('You need to set the chatgpt_key in config or CHATGPT_KEY environment variable to run this script', true);
            return;
        }

        $fromDate = $this->fromDate;
        if ($fromDate) {
            $fromDate = strtotime($fromDate . ' 00:00:00');
        }

        $dryRun = $this->dryRun;

        $io->info("Translating content from " . date('Y-m-d', $fromDate ?: 0), true);
        if ($dryRun) {
            $io->warn('** DRY RUN MODE ** - No files will be modified.', true);
        }

        $skipFilesInput = $this->skipFiles ?? '';
        $filenames_to_skip = array_filter(array_map('trim', explode(',', $skipFilesInput)));

        $languages = [
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

        $projectRoot = dirname(__DIR__, 2);
        $top_level_files = glob($projectRoot . '/content/v3/en/*.md');
        $files = array_merge($top_level_files, glob($projectRoot . '/content/v3/en/**/*.md'));

        // Build queue of translation jobs
        $translationJobs = [];
        foreach ($files as $file) {
            if ($fromDate && filemtime($file) < $fromDate) {
                $io->comment("Skipping " . basename($file) . " because it's older than the from-date", true);
                continue;
            }

            if (in_array(basename($file), $filenames_to_skip)) {
                $io->comment("Skipping " . basename($file) . " because it's in the skip list", true);
                continue;
            }

            foreach ($languages as $languageAbbreviation) {
                $translationJobs[] = [
                    'file' => $file,
                    'language' => $languageAbbreviation,
                    'translatedFilePath' => str_replace('/en/', '/' . $languageAbbreviation . '/', $file)
                ];
            }
        }

        if (empty($translationJobs)) {
            $io->info('No files to translate.', true);
            return;
        }

        $io->info("Total translation jobs: " . count($translationJobs), true);

        if ($dryRun) {
            foreach ($translationJobs as $job) {
                $io->comment("  [DRY RUN] Would translate " . basename($job['file']) . " to {$job['language']}", true);
            }
            return;
        }

        // Process jobs in batches based on thread count
        $maxThreads = (int)$this->threads;
        $totalJobs = count($translationJobs);
        $processedJobs = 0;

        while ($processedJobs < $totalJobs) {
            $batch = array_slice($translationJobs, $processedJobs, $maxThreads);
            $this->processBatch($batch, $chatgpt_key, $io);
            $processedJobs += count($batch);
            $io->info("Progress: {$processedJobs}/{$totalJobs} translations completed", true);
        }

        // Cleanup orphaned files
        if (!$dryRun) {
            $this->cleanupOrphanedFiles($files, $languages, $projectRoot, $io);
        } else {
            $io->comment("[DRY RUN] Skipping orphaned file cleanup check.", true);
        }
    }

    /**
     * Process a batch of translation jobs concurrently using curl_multi
     */
    protected function processBatch(array $batch, string $apiKey, $io) {
        $mh = curl_multi_init();
        $handles = [];
        $jobData = [];

        // Initialize all curl handles for the batch
        foreach ($batch as $index => $job) {
            $fileContent = file_get_contents($job['file']);
            $messages = [
                [
                    "role" => "system",
                    "content" => "You are a gifted translator focusing on the tech space. Today you are translating documentation for a PHP Framework called Flight (so please never translate the word 'Flight' as it's the name of the framework). You are going to receive content that is a markdown file. When you receive the content you'll translate it from english to the two letter language code that is specified. When you generate a response, you are going to ONLY send back the translated markdown content, no other replies or 'here is your translated markdown' type statements back, only the translated markdown content in markdown format. If you get a follow up response, you need to continue to markdown translation from the very character you left off at and complete the translation until the full page is done. THIS NEXT ITEM IS VERY IMPORTANT! Make sure that when you are translating any code in the markdown file that you ONLY translate the comments of the code and not the classes/methods/variables/links/urls/etc. This next part is also incredibly important or it will break the entire page!!!! Please don't translate any URLs or you will break my app and I will lose my job if this is not done correctly!!!!"
                ],
                [
                    "role" => "user",
                    "content" => "Translate the following text from English to the two letter language code of {$job['language']}:\n\n{$fileContent}"
                ]
            ];

            $ch = $this->createCurlHandle($apiKey, $messages);
            curl_multi_add_handle($mh, $ch);

            $handles[(int)$ch] = $ch;
            $jobData[(int)$ch] = [
                'job' => $job,
                'messages' => $messages,
                'full_response' => '',
                'needs_continuation' => false
            ];
        }

        // Execute all handles
        $active = null;
        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        // Collect results and check for continuation needs
        foreach ($handles as $handleId => $ch) {
            $response = curl_multi_getcontent($ch);
            $responseArr = json_decode($response, true);

            $content = $responseArr['choices'][0]['message']['content'] ?? '';

            if (!empty($content)) {
                $jobData[$handleId]['full_response'] .= $content;
                $jobData[$handleId]['messages'][] = [
                    'role' => 'assistant',
                    'content' => $content
                ];

                // Check if we need continuation (response was truncated)
                if (($responseArr['usage']['completion_tokens'] ?? 0) === 4096) {
                    $jobData[$handleId]['needs_continuation'] = true;
                }
            } else {
                $io->error("Empty response for " . basename($jobData[$handleId]['job']['file']) . " ({$jobData[$handleId]['job']['language']})", true);
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);

        // Handle continuation requests for jobs that need them
        foreach ($jobData as $data) {
            while ($data['needs_continuation']) {
                $io->comment("  Continuing translation for " . basename($data['job']['file']) . " ({$data['job']['language']})...", true);

                $ch = $this->createCurlHandle($apiKey, $data['messages']);
                $response = curl_exec($ch);
                curl_close($ch);

                $responseArr = json_decode($response, true);
                $content = $responseArr['choices'][0]['message']['content'] ?? '';

                if (empty($content)) {
                    $io->error("Empty continuation response for " . basename($data['job']['file']), true);
                    break;
                }

                $data['full_response'] .= $content;
                $data['messages'][] = [
                    'role' => 'assistant',
                    'content' => $content
                ];

                // Check if we still need continuation
                $data['needs_continuation'] = ($responseArr['usage']['completion_tokens'] ?? 0) === 4096;
            }

            // Save the translated content
            if (!empty($data['full_response'])) {
                $translatedFilePath = $data['job']['translatedFilePath'];
                $directory = dirname($translatedFilePath);

                if (!is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                file_put_contents($translatedFilePath, $data['full_response']);
                $io->green("  Translated: " . basename($data['job']['file']) . " -> {$data['job']['language']}", true);
            }
        }
    }

    /**
     * Create a configured curl handle for the API request
     */
    protected function createCurlHandle(string $apiKey, array $messages) {
        $ch = curl_init('https://api.x.ai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "model" => "grok-4-fast-non-reasoning",
            "messages" => $messages
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);

        return $ch;
    }

    protected function cleanupOrphanedFiles(array $files, array $languages, string $projectRoot, $io) {
        $enFiles = [];
        foreach ($files as $file) {
            $enFiles[] = ltrim(str_replace(realpath($projectRoot . '/content/v3/en/'), '', realpath($file)), '/\\');
        }

        foreach ($languages as $languageAbbreviation) {
            $langDir = $projectRoot . "/content/v3/{$languageAbbreviation}/";
            if (!is_dir($langDir)) continue;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($langDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $translatedFile) {
                if ($translatedFile->getExtension() !== 'md') continue;

                $relativePath = ltrim(str_replace(realpath($langDir), '', $translatedFile->getRealPath()), '/\\');

                if (!in_array($relativePath, $enFiles)) {
                    $io->error("Deleting orphaned file: {$translatedFile->getRealPath()}", true);
                    unlink($translatedFile->getRealPath());

                    $dir = dirname($translatedFile->getRealPath());
                    while ($dir !== $langDir && is_dir($dir) && count(glob("$dir/*")) === 0) {
                        rmdir($dir);
                        $dir = dirname($dir);
                    }
                }
            }
        }
    }
}
