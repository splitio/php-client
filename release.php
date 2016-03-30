#!/usr/bin/env php
<?php

if ($argc != 2) {
    die(PHP_EOL."Usage: php release.php <version_number>".PHP_EOL.PHP_EOL);
}

echo <<<EOL

       __      ____        _ _ _
      / /__   / ___| _ __ | (_) |_
     / / \ \  \___ \| '_ \| | | __|
     \ \  \ \  ___) | |_) | | | |_
      \_\ / / |____/| .__/|_|_|\__|
         /_/        |_|

Split PHP SDK Release script - By Split Software


EOL;


function out($msg) {
    echo $msg.PHP_EOL;
}

$rline = readline('This script will release the version '.$argv[1].' Are you sure? [yes/no]  ');

if ($rline != 'yes') {
    exit;
}

out('* Creating version: '.$argv[1]);

out('* Switching to develop branch');
echo shell_exec('git checkout develop');

out('* Checking PHP PSR-2 coding standard');
$psr2Status = shell_exec('./vendor/bin/phpcs --ignore=functions.php --standard=PSR2 src/');

if ($psr2Status !== null) {
    echo $psr2Status;
    exit;
}

$doTests = readline('Would you like run the test integration suite? [yes/no]  ');

if ($doTests == 'yes') {
    $doCoverage = readline('Would you like run the coverage report? [yes/no]  ');
    if ($doCoverage == 'yes') {
        echo shell_exec('./vendor/bin/phpunit -c phpunit.xml -v --testsuite integration --coverage-html "./tests/coverage-report"');
    } else {
        echo shell_exec('./vendor/bin/phpunit -c phpunit.xml -v --testsuite integration');
    }
}

$proceed = readline('Proceed with the release? [yes/no]  ');

if ( $proceed != 'yes' ) {
    out("--- Release canceled by user ---");
    exit;
}

out('* Creating Release branch: release-'.$argv[1]);
echo shell_exec('git checkout -b release-'.$argv[1].' develop');

out('* Writing Version Class');
//Writing Release Version Class.
$versionClass = <<<VC
<?php
namespace SplitIO;

class Version
{
    const CURRENT = '{{version}}';
}

VC;

file_put_contents(__DIR__.'/src/SplitIO/Version.php', str_replace('{{version}}',$argv[1],$versionClass));


out('* Committing Version Class on Release branch');
echo shell_exec('git add src/SplitIO/Version.php');
echo shell_exec('git commit -m "Releasing version '.$argv[1].'"');

$push = readline('Push release branch to origin? [yes/no]  ');

if ( $push == 'yes' ) {
    echo shell_exec('git push origin release-'.$argv[1]);
}


