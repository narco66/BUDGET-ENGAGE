<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();
echo route('engagements.workflow.show', App\Models\Engagement::find(1)) . PHP_EOL;
var_export(__('Workflow'));
