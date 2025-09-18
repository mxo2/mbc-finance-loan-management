<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Special PWA login endpoint
Route::post('/pwa-login', function (Request $request) {
    try {
        // Log the login attempt for debugging
        \Log::info('PWA Login attempt', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Hard-coded credentials for testing
        if ($request->input('email') === 'abs.jaipur@gmail.com' && $request->input('password') === 'password123') {
            $user = \App\Models\User::where('email', 'abs.jaipur@gmail.com')->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Create simple token for authentication
            $token = base64_encode($user->id . ':' . $user->email . ':' . time());
            
            // Store token in cache for validation
            \Illuminate\Support\Facades\Cache::put('pwa_token_' . $user->id, $token, now()->addDays(7));
            
            // Get customer details
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'customer_id' => $customer ? $customer->customer_id : null
                ],
                'token' => $token
            ]);
        }
        
        // Hard-coded credentials for the test customer
        if ($request->input('email') === 'test@customer.com' && $request->input('password') === 'testpassword') {
            $user = \App\Models\User::where('email', 'test@customer.com')->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Create simple token for authentication
            $token = base64_encode($user->id . ':' . $user->email . ':' . time());
            
            // Store token in cache for validation
            \Illuminate\Support\Facades\Cache::put('pwa_token_' . $user->id, $token, now()->addDays(7));
            
            // Get customer details
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'customer_id' => $customer ? $customer->customer_id : null
                ],
                'token' => $token
            ]);
        }
        
        // If we get here, credentials were invalid
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
        
    } catch (\Exception $e) {
        \Log::error('PWA Login exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ], 500);
    }
});