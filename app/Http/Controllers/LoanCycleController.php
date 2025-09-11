<?php

namespace App\Http\Controllers;

use App\Models\LoanCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoanCycleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->can('manage loan cycle')) {
            $cycles = LoanCycle::where('parent_id', parentId())->get();
            return view('loan-cycles.index', compact('cycles'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->can('create loan cycle')) {
            return view('loan-cycles.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create loan cycle')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'frequency' => 'required|in:daily,weekly,monthly,yearly',
                    'payment_day' => 'required|integer|min:1|max:31',
                ]
            );
            
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            
            $cycle = new LoanCycle();
            $cycle->name = $request->name;
            $cycle->description = $request->description;
            $cycle->frequency = $request->frequency;
            $cycle->payment_day = $request->payment_day;
            $cycle->is_active = $request->has('is_active') ? 1 : 0;
            $cycle->parent_id = parentId();
            $cycle->save();
            
            return redirect()->route('loan-cycle.index')->with('success', __('Loan cycle created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Auth::user()->can('edit loan cycle')) {
            $cycle = LoanCycle::find(decrypt($id));
            if (!$cycle) {
                return redirect()->back()->with('error', __('Loan cycle not found.'));
            }
            return view('loan-cycles.edit', compact('cycle'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit loan cycle')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'frequency' => 'required|in:daily,weekly,monthly,yearly',
                    'payment_day' => 'required|integer|min:1|max:31',
                ]
            );
            
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            
            $cycle = LoanCycle::find(decrypt($id));
            if (!$cycle) {
                return redirect()->back()->with('error', __('Loan cycle not found.'));
            }
            
            $cycle->name = $request->name;
            $cycle->description = $request->description;
            $cycle->frequency = $request->frequency;
            $cycle->payment_day = $request->payment_day;
            $cycle->is_active = $request->has('is_active') ? 1 : 0;
            $cycle->save();
            
            return redirect()->route('loan-cycle.index')->with('success', __('Loan cycle updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->can('delete loan cycle')) {
            $cycle = LoanCycle::find(decrypt($id));
            if (!$cycle) {
                return redirect()->back()->with('error', __('Loan cycle not found.'));
            }
            
            $cycle->delete();
            return redirect()->route('loan-cycle.index')->with('success', __('Loan cycle deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
