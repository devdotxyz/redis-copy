<?php
require_once(__DIR__ . '/vendor/autoload.php');

$rc = new RedisCopy($argv[1], $argv[2]);

$rc->setIgnoredPrefixes(array(
    'ipligence_',
    'aba_',
    'zip_',
    'integraCreditHardReject'
));

$rc->copy();
