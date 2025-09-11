<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontPageController extends Controller
{
    /**
     * Display the standalone homepage without CMS integration.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Simple, clean homepage without CMS dependencies
        return view('front.homepage');
    }
    
    /**
     * Handle loan application from the front page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function applyLoan(Request $request)
    {
        // Validate basic application data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'loan_amount' => 'required|numeric|min:5000|max:50000',
            'loan_purpose' => 'required|string|max:255',
        ]);
        
        if (Auth::check()) {
            // If user is logged in, redirect to loan creation
            return redirect()->route('loan.create')->with('application_data', $request->all());
        } else {
            // Store application data in session and redirect to register
            session(['loan_application' => $request->all()]);
            return redirect()->route('register')->with('message', 'Please register or login to complete your loan application');
        }
    }
    
    /**
     * Calculate EMI for the loan calculator.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calculateEMI(Request $request)
    {
        $loanAmount = $request->input('loan_amount', 25000);
        $tenure = $request->input('tenure', 6); // in months
        $interestRate = 5; // 5% per month
        
        // Simple EMI calculation
        $monthlyInterest = $interestRate / 100;
        $emi = ($loanAmount * $monthlyInterest * pow(1 + $monthlyInterest, $tenure)) / 
               (pow(1 + $monthlyInterest, $tenure) - 1);
        
        $totalAmount = $emi * $tenure;
        $totalInterest = $totalAmount - $loanAmount;
        
        return response()->json([
            'emi' => round($emi, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2),
            'loan_amount' => $loanAmount,
            'tenure' => $tenure,
            'interest_rate' => $interestRate
        ]);
    }
}