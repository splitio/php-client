#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

// Define SPLITIO_SERVICE_HOME
defined('SPLITIO_SERVICE_HOME') || define('SPLITIO_SERVICE_HOME', (getenv('SPLITIO_SERVICE_HOME') ?
    getenv('SPLITIO_SERVICE_HOME') : dirname(__FILE__)));

Requests::register_autoloader();

use SplitIO\Service\Console\Command\ServiceCommand;
use SplitIO\Service\Console\Command\SplitCommand;
use SplitIO\Service\Console\Command\SegmentCommand;
use SplitIO\Service\Console\Command\ImpressionsCommand;
use SplitIO\Service\Console\Command\MetricsCommand;

use SplitIO\Service\Console\ConsoleApp;

$splitApp = new ConsoleApp();
$splitApp->setName("Split Synchronizer Service");

$splitApp->add(new ServiceCommand());
$splitApp->add(new SplitCommand());
$splitApp->add(new SegmentCommand());
$splitApp->add(new ImpressionsCommand());
$splitApp->add(new MetricsCommand());

$splitApp->run();
