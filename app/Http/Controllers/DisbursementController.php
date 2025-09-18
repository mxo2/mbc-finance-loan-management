<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanDisbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DisbursementController extends Controller
{
    /**
     * Display a listing of approved loans pending disbursement
     */
    public function index()
    {
        if (!\Auth::user()->can('manage loan')) {
            return redirect()->route('home')->with('error', 'Permission denied. You do not have access to loan disbursement management.');
        }

        // Get approved loans that haven't been disbursed yet
        $loans = Loan::with(['Customers', 'loanType'])
            ->where('status', 'approved')
            ->where('disbursement_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent transactions for display
        $transactions = LoanDisbursement::with(['loan', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('disbursement.index', compact('loans', 'transactions'));
    }

    /**
     * Display the specified loan for disbursement processing
     */
    public function show(Loan $loan)
    {
        if (!\Auth::user()->can('manage loan')) {
            return redirect()->back()->with('error', 'Permission denied. You do not have access to loan disbursement.');
        }

        $loan->load(['Customers', 'loanType', 'disbursements.processedBy']);
        
        return view('disbursement.show', compact('loan'));
    }

    /**
     * Process file charges payment for a loan
     */
    public function payFileCharges(Request $request, Loan $loan)
    {
        if (!\Auth::user()->can('manage loan')) {
            return redirect()->back()->with('error', 'Permission denied. You do not have access to loan disbursement.');
        }

        // Validate the request
        $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online',
            'transaction_reference' => 'nullable|string|max:255',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if loan is approved and file charges are pending
        if ($loan->status !== 'approved' || $loan->file_charges_status !== 'pending') {
            return redirect()->back()->with('error', 'Invalid loan status for file charges payment.');
        }

        // Handle file upload if provided
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('disbursement/file_charges', 'public');
        }

        // Create disbursement record
        LoanDisbursement::create([
            'loan_id' => $loan->id,
            'transaction_type' => 'file_charges',
            'amount' => $loan->file_charges_amount,
            'payment_method' => $request->payment_method,
            'transaction_reference' => $request->transaction_reference,
            'payment_proof' => $paymentProofPath,
            'notes' => $request->notes,
            'status' => 'completed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        // Update loan file charges status
        $loan->update([
            'file_charges_status' => 'paid',
        ]);

        return redirect()->back()->with('success', 'File charges payment recorded successfully.');
    }

    /**
     * Waive file charges for a loan
     */
    public function waiveFileCharges(Request $request, Loan $loan)
    {
        if (!\Auth::user()->can('manage loan')) {
            return redirect()->back()->with('error', 'Permission denied. You do not have access to loan disbursement.');
        }

        // Validate the request
        $request->validate([
            'waive_reason' => 'required|string|max:1000',
        ]);

        // Check if loan is approved and file charges are pending
        if ($loan->status !== 'approved' || $loan->file_charges_status !== 'pending') {
            return redirect()->back()->with('error', 'Invalid loan status for file charges waiver.');
        }

        // Create disbursement record for waiver
        LoanDisbursement::create([
            'loan_id' => $loan->id,
            'transaction_type' => 'file_charges',
            'amount' => $loan->file_charges_amount,
            'payment_method' => 'waived',
            'notes' => 'File charges waived: ' . $request->waive_reason,
            'status' => 'completed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        // Update loan file charges status
        $loan->update([
            'file_charges_status' => 'waived',
        ]);

        return redirect()->back()->with('success', 'File charges waived successfully.');
    }

    /**
     * Disburse the loan amount
     */
    public function disburseLoan(Request $request, Loan $loan)
    {
        if (!\Auth::user()->can('manage loan')) {
            return redirect()->back()->with('error', 'Permission denied. You do not have access to loan disbursement.');
        }

        // Validate the request
        $request->validate([
            'disbursement_method' => 'required|in:bank_transfer,cheque,cash',
            'disbursement_reference' => 'required|string|max:255',
            'disbursement_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'disbursement_notes' => 'nullable|string|max:1000',
        ]);

        // Check if loan can be disbursed
        if ($loan->status !== 'approved' || $loan->disbursement_status !== 'pending') {
            return redirect()->back()->with('error', 'Loan is not eligible for disbursement.');
        }

        // Check if file charges are paid or waived (if applicable)
        if ($loan->loanType->file_charges && $loan->loanType->file_charges > 0) {
            if (!in_array($loan->file_charges_status, ['paid', 'waived'])) {
                return redirect()->back()->with('error', 'File charges must be paid or waived before disbursement.');
            }
        }

        // Handle file upload if provided
        $disbursementProofPath = null;
        if ($request->hasFile('disbursement_proof')) {
            $disbursementProofPath = $request->file('disbursement_proof')->store('disbursement/loan_disbursement', 'public');
        }

        // Create disbursement record
        LoanDisbursement::create([
            'loan_id' => $loan->id,
            'transaction_type' => 'loan_disbursement',
            'amount' => $loan->amount,
            'payment_method' => $request->disbursement_method,
            'transaction_reference' => $request->disbursement_reference,
            'payment_proof' => $disbursementProofPath,
            'notes' => $request->disbursement_notes,
            'status' => 'completed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        // Update loan disbursement status
        $loan->update([
            'disbursement_status' => 'disbursed',
            'disbursed_at' => now(),
            'disbursement_notes' => $request->disbursement_notes,
        ]);

        return redirect()->back()->with('success', 'Loan amount disbursed successfully.');
    }
}
