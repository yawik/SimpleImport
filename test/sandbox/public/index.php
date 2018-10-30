<?php

require __DIR__.'/../../../vendor/autoload.php';

use Core\Bootstrap;

// Retrieve configuration
$appConfig = include __DIR__.'/../../config/config.php';

// Run the application!
Bootstrap::runApplication($appConfig);
