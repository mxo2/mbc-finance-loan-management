<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage account type')) {
            $accountTypes = AccountType::where('parent_id', parentId())->orderBy('id', 'DESC')->get();
            return view('account_type.index', compact('accountTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create account type')) {

            $termPeroid = AccountType::$termPeroid;
            return view('account_type.create', compact('termPeroid'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {

        if (\Auth::user()->can('create account type')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'interest_rate' => 'required',
                    'interest_duration' => 'required',
                    'min_maintain_amount' => 'required',
                    'maintenance_charges' => 'required',
                    'charges_deduct_month' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $type = new AccountType();
            $type->title = $request->title;
            $type->interest_rate = $request->interest_rate;
            $type->interest_duration = $request->interest_duration;
            $type->min_maintain_amount = $request->min_maintain_amount;
            $type->maintenance_charges = $request->maintenance_charges;
            $type->charges_deduct_month = $request->charges_deduct_month;
            $type->parent_id = parentId();
            $type->save();

            return redirect()->route('account-type.index')->with('success', __('Account Type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(AccountType $accountType)
    {
        //
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit account type')) {
            $accountType = AccountType::find(decrypt($id));
            $termPeroid = AccountType::$termPeroid;
            return view('account_type.edit', compact('accountType', 'termPeroid'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit account type')) {
            $accountType = AccountType::find(decrypt($id));
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'interest_rate' => 'required',
                    'interest_duration' => 'required',
                    'min_maintain_amount' => 'required',
                    'maintenance_charges' => 'required',
                    'charges_deduct_month' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // $type = new AccountType();
            $accountType->title = $request->title;
            $accountType->interest_rate = $request->interest_rate;
            $accountType->interest_duration = $request->interest_duration;
            $accountType->min_maintain_amount = $request->min_maintain_amount;
            $accountType->maintenance_charges = $request->maintenance_charges;
            $accountType->charges_deduct_month = $request->charges_deduct_month;
            $accountType->save();

            return redirect()->route('account-type.index')->with('success', __('Account Type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('edit account type')) {
            $accountType = AccountType::find(decrypt($id));
            $accountType->delete();
            return redirect()->route('account-type.index')->with('success', __('Account Type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
