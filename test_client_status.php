<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/deliverables/1/client-status', 'POST', ['client_status' => 'Sent to Client']);
$request->headers->set('Accept', 'application/json');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
