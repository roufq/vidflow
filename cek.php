<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo json_encode(Illuminate\Support\Facades\DB::table('platform_uploads')->where('platform', 'tiktok')->latest('id')->first(), JSON_PRETTY_PRINT);
