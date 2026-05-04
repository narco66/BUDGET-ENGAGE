<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::factory()->create();
echo "Name type: " . gettype($user->name) . "\n";
echo "Name value: " . print_r($user->name, true) . "\n";
echo "Is array: " . (is_array($user->name) ? "YES" : "NO") . "\n";
echo "\nAll attributes:\n";
print_r($user->getAttributes());
?>

