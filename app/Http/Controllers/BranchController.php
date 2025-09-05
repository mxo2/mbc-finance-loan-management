<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage branch')) {
            $branches = Branch::where('parent_id', \Auth::user()->id)->orderBy('id', 'DESC')->get();
            return view('branch.index', compact('branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('branch.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create branch') ) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'email' => 'required',
                    'phone_number' => 'required',
                    'location' => 'required',
                    ]
                );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $branch = new Branch();
            $branch->name = $request->name;
            $branch->email = $request->email;
            $branch->phone_number = $request->phone_number;
            $branch->location = $request->location;
            $branch->parent_id = parentId();
            $branch->save();

            return redirect()->back()->with('success', __('Branch successfully created.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(Branch $branch)
    {
        //
    }


    public function edit($id)
    {
        $branch= Branch::find(decrypt($id));
        return view('branch.edit', compact('branch'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit branch') ) {
            $branch= Branch::find(decrypt($id));
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'email' => 'required',
                    'phone_number' => 'required',
                    'location' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $branch->name = $request->name;
            $branch->email = $request->email;
            $branch->phone_number = $request->phone_number;
            $branch->location = $request->location;
            $branch->save();
            return redirect()->back()->with('success', __('Branch successfully updated.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete branch') ) {

            $branch= Branch::find(decrypt($id));
            $branch->delete();
            return redirect()->back()->with('success', 'Branch successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
