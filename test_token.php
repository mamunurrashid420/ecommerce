<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the admin customer
$customer = App\Models\Customer::where('role', 'admin')->first();

if ($customer) {
    // Delete old tokens
    $customer->tokens()->delete();
    
    // Create a new token
    $token = $customer->createToken('auth-token')->plainTextToken;
    
    echo "New admin token: " . $token . PHP_EOL;
    echo "Customer: " . $customer->name . " (" . $customer->email . ")" . PHP_EOL;
    echo "Role: " . $customer->role . PHP_EOL;
    echo "Is Admin: " . ($customer->isAdmin() ? 'Yes' : 'No') . PHP_EOL;
} else {
    echo "No admin customer found" . PHP_EOL;
}