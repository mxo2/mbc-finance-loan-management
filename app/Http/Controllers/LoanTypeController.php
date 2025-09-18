<?php

namespace App\Http\Controllers;

use App\Models\LoanType;
use Illuminate\Http\Request;

class LoanTypeController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage loan type') || \Auth::user()->can('show loan type')) {
            // If customer, get loan types from parent
            if (\Auth::user()->type == 'customer') {
                $loanTypes = LoanType::where('parent_id', \Auth::user()->parent_id)->orderBy('id', 'DESC')->get();
            } else {
                $loanTypes = LoanType::where('parent_id', \Auth::user()->id)->orderBy('id', 'DESC')->get();
            }
            return view('loan_type.index', compact('loanTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $interestType=LoanType::$interestType;
        $termPeroid=LoanType::$termPeroid;
        return view('loan_type.create',compact('interestType','termPeroid'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create loan type') ) {
            $validator = \Validator::make(
                $request->all(), [
                    'type' => 'required',
                    'min_loan_amount' => 'required',
                    'max_loan_amount' => 'required',
                    'interest_type' => 'required',
                    'interest_rate' => 'required',
                    'max_loan_term' => 'required',
                    'loan_term_period' => 'required',
                    'payment_frequency' => 'required|in:daily,weekly,monthly,yearly',
                    'payment_day' => 'required|integer|min:1|max:31',
                    'penalty_type' => 'required|in:percentage,fixed',
                    'penalties' => 'required',
                    'file_charges_type' => 'required|in:percentage,fixed',
                    'file_charges' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $loanType = new LoanType();
            $loanType->type = $request->type;
            $loanType->min_loan_amount = $request->min_loan_amount;
            $loanType->max_loan_amount = $request->max_loan_amount;
            $loanType->interest_type = $request->interest_type;
            $loanType->interest_rate = $request->interest_rate;
            $loanType->max_loan_term = $request->max_loan_term;
            $loanType->loan_term_period = $request->loan_term_period;
            $loanType->payment_frequency = $request->payment_frequency;
            $loanType->payment_day = $request->payment_day;
            $loanType->auto_start_date = $request->has('auto_start_date') ? 1 : 0;
            $loanType->penalty_type = $request->penalty_type;
            $loanType->penalties = $request->penalties;
            $loanType->file_charges_type = $request->file_charges_type;
            $loanType->file_charges = $request->file_charges;
            $loanType->status = 1;
            $loanType->notes = $request->notes;
            $loanType->parent_id = parentId();
            $loanType->save();

            return redirect()->back()->with('success', __('Loan Type successfully created.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        if (\Auth::user()->can('manage loan type') || \Auth::user()->can('show loan type')) {
            $loanType=LoanType::find(decrypt($id));
            return view('loan_type.show',compact('loanType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($id)
    {
        $loanType=LoanType::find(decrypt($id));
        $interestType=LoanType::$interestType;
        $termPeroid=LoanType::$termPeroid;
        return view('loan_type.edit',compact('interestType','loanType','termPeroid'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit loan type') ) {
            $loanType=LoanType::find(decrypt($id));
            $validator = \Validator::make(
                $request->all(), [
                    'type' => 'required',
                    'min_loan_amount' => 'required',
                    'max_loan_amount' => 'required',
                    'interest_type' => 'required',
                    'interest_rate' => 'required',
                    'max_loan_term' => 'required',
                    'loan_term_period' => 'required',
                    'payment_frequency' => 'required|in:daily,weekly,monthly,yearly',
                    'payment_day' => 'required|integer|min:1|max:31',
                    'penalty_type' => 'required|in:percentage,fixed',
                    'penalties' => 'required',
                    'file_charges_type' => 'required|in:percentage,fixed',
                    'file_charges' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }


                $loanType->type = $request->type;
                $loanType->min_loan_amount = $request->min_loan_amount;
                $loanType->max_loan_amount = $request->max_loan_amount;
                $loanType->interest_type = $request->interest_type;
                $loanType->interest_rate = $request->interest_rate;
                $loanType->max_loan_term = $request->max_loan_term;
                $loanType->loan_term_period = $request->loan_term_period;
                $loanType->payment_frequency = $request->payment_frequency;
                $loanType->payment_day = $request->payment_day;
                $loanType->auto_start_date = $request->has('auto_start_date') ? 1 : 0;
                $loanType->penalty_type = $request->penalty_type;
                $loanType->penalties = $request->penalties;
                $loanType->file_charges_type = $request->file_charges_type;
                $loanType->file_charges = $request->file_charges;
                $loanType->status = 1;
                $loanType->notes = $request->notes;
                $loanType->save();

                return redirect()->back()->with('success', __('Loan Type successfully updated.'));

            } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete loan type') ) {
            $loanType=LoanType::find(decrypt($id));
            $loanType->delete();
            return redirect()->back()->with('success', 'Loan Type successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
