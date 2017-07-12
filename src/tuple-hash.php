#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Commands\Difference;
use Commands\GenerateIndex;
use Symfony\Component\Console\Application;


$application = new Application();

$application->add(new GenerateIndex());
$application->add(new Difference());
$application->setDefaultCommand('generate-index');

$application->run();