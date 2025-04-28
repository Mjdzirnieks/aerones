<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Downloader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('downloader');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/downloader.log', Logger::DEBUG));

$urls = [
    'https://storage.googleapis.com/public_test_access_ae/output_20sec.mp4',
    'https://storage.googleapis.com/public_test_access_ae/output_30sec.mp4',
    'https://storage.googleapis.com/public_test_access_ae/output_40sec.mp4',
    'https://storage.googleapis.com/public_test_access_ae/output_50sec.mp4',
    'https://storage.googleapis.com/public_test_access_ae/output_60sec.mp4',
];

$tmpDir = __DIR__ . '/../tmp';
$completedDir = __DIR__ . '/../completed';

$downloader = new Downloader($urls, $tmpDir, $completedDir, $logger);
$downloader->downloadAll();
