<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Test API endpoint
Route::get('/test-api', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now(),
        'server' => $_SERVER['SERVER_NAME'],
        'version' => '1.0.0'
    ]);
});

// Test auth endpoint for PWA
Route::post('/test-login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');
    
    return response()->json([
        'success' => true,
        'message' => 'Login request received',
        'data' => [
            'email' => $email,
            'credentials_received' => !empty($email) && !empty($password),
            'timestamp' => now()
        ]
    ]);
});

// Register this in routes/api.php