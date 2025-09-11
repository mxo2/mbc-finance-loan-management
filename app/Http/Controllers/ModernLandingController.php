<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModernLandingController extends Controller
{
    /**
     * Display the modern landing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = Subscription::get();
        $FAQs = FAQ::where('enabled', 1)->get();
        
        return view('layouts.modern_landing', compact('subscriptions', 'FAQs'));
    }
    
    /**
     * Handle loan application submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function applyLoan(Request $request)
    {
        // This is a placeholder for loan application processing
        // Will redirect to login/register if user is not authenticated
        if (\Auth::check()) {
            // Logic for authenticated users applying for loan
            return redirect()->route('loans.create');
        } else {
            // Store application data in session and redirect to register
            session(['loan_application' => $request->all()]);
            return redirect()->route('register')->with('message', 'Please register or login to complete your loan application');
        }
    }
}