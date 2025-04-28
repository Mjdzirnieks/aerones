<?php

namespace App;

use React\EventLoop\Factory;
use React\HttpClient\Client;
use React\HttpClient\Request;
use Monolog\Logger;
use React\Promise\Deferred;

class Downloader
{
    private array $urls;
    private string $tmpDir;
    private string $completedDir;
    private Logger $logger;
    private int $maxRetries = 5;

    public function __construct(array $urls, string $tmpDir, string $completedDir, Logger $logger)
    {
        $this->urls = $urls;
        $this->tmpDir = $tmpDir;
        $this->completedDir = $completedDir;
        $this->logger = $logger;
    }

    public function downloadAll(): void
    {
        $loop = Factory::create();
        $client = new Client($loop);

        foreach ($this->urls as $url) {
            $this->downloadWithRetry($client, $loop, $url);
        }

        $loop->run();
    }

    private function downloadWithRetry(Client $client, $loop, string $url, int $attempt = 1): void
    {
        $filename = basename(parse_url($url, PHP_URL_PATH));
        $tmpFile = "{$this->tmpDir}/{$filename}.part";
        $completedFile = "{$this->completedDir}/{$filename}";

        $startByte = file_exists($tmpFile) ? filesize($tmpFile) : 0;

        $headers = [
            'Range' => "bytes=$startByte-",
        ];

        $this->logger->info("Starting download attempt {$attempt} for: $url");

        $request = $client->request('GET', $url, $headers);

        $request->on('response', function ($response) use ($tmpFile, $completedFile, $url, $client, $loop, $attempt) {
            $fp = fopen($tmpFile, 'ab');

            $totalSize = $response->getHeaders()['Content-Length'] ?? null;
            $downloaded = filesize($tmpFile);

            $response->on('data', function ($chunk) use ($fp, &$downloaded, $totalSize, $url) {
                fwrite($fp, $chunk);
                $downloaded += strlen($chunk);

                if ($totalSize) {
                    $percent = round(($downloaded / $totalSize) * 100, 2);
                    echo "[{$url}] Downloaded: {$percent}%\n";
                } else {
                    echo "[{$url}] Downloaded: " . $this->formatBytes($downloaded) . "\n";
                }
            });

            $response->on('end', function () use ($fp, $tmpFile, $completedFile, $url) {
                fclose($fp);
                rename($tmpFile, $completedFile);
                echo "✅ Finished downloading: {$url}\n";
                $this->logger->info("Finished downloading: $url");
            });
        });

        $request->on('error', function (\Exception $e) use ($url, $client, $loop, $attempt) {
            $this->logger->error("Download error for $url: {$e->getMessage()}");

            if ($attempt < $this->maxRetries) {
                $delay = pow(2, $attempt); // Exponential backoff: 2^attempt
                echo "⚠️  Error downloading {$url}. Retrying in {$delay}s...\n";
                $this->logger->info("Retrying {$url} in {$delay} seconds...");

                $loop->addTimer($delay, function () use ($client, $loop, $url, $attempt) {
                    $this->downloadWithRetry($client, $loop, $url, $attempt + 1);
                });
            } else {
                echo "❌ Failed to download {$url} after {$this->maxRetries} attempts.\n";
                $this->logger->error("Giving up on $url after {$this->maxRetries} retries.");
            }
        });

        $request->end();
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
