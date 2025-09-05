<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage document type')) {
            $documentTypes = DocumentType::where('parent_id', parentId())->orderBy('id', 'DESC')->get();
            return view('document_type.index', compact('documentTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('document_type.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create document type') ) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $documentType = new DocumentType();
            $documentType->title = $request->title;
            $documentType->parent_id = parentId();
            $documentType->save();

            return redirect()->back()->with('success', __('Document type successfully created.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(DocumentType $documentType)
    {
        //
    }


    public function edit($id)
    {
        $documentType= DocumentType::find(decrypt($id));
        return view('document_type.edit',compact('documentType'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit document type') ) {
            $documentType= DocumentType::find(decrypt($id));
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $documentType->title = $request->title;
            $documentType->save();

            return redirect()->back()->with('success', __('Document type successfully updated.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete document type') ) {
            $documentType= DocumentType::find(decrypt($id));
            $documentType->delete();
            return redirect()->back()->with('success', 'Document type successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
