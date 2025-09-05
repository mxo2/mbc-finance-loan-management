<?php

namespace App\Http\Controllers;

use App\Models\NoticeBoard;
use Illuminate\Http\Request;

class NoticeBoardController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage note')) {
            $notes = NoticeBoard::where('parent_id', parentId())->orderBy('id', 'DESC')->get();

            return view('note.index', compact('notes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('note.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create note')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $note = new NoticeBoard();
            $note->title = $request->title;
            $note->description = $request->description;
            if ($request->hasFile('attachment')) {
                $uploadResult = handleFileUpload($request->file('attachment'), 'upload/attachment/');

                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $note->attachment = $uploadResult['filename'];
            }
            $note->parent_id = \Auth::user()->id;
            $note->save();

            return redirect()->back()->with('success', __('Note successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(NoticeBoard $noticeBoard)
    {
        //
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit note')) {
            $note = NoticeBoard::find(decrypt($id));
            return view('note.edit', compact('note'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit note')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $note = NoticeBoard::find(decrypt($id));
            if ($request->hasFile('attachment')) {
                $uploadResult = handleFileUpload($request->file('attachment'), 'upload/attachment/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                if (!empty($note->attachment)) {
                    deleteOldFile($note->attachment, 'upload/attachment/');
                }
                $note->attachment = $uploadResult['filename'];
            }
            $note->title = $request->title;
            $note->description = $request->description;
            $note->save();

            return redirect()->back()->with('success', __('Note successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete note')) {
            $note = NoticeBoard::find(decrypt($id));
            if ($note) {
                if (!empty($note->attachment)) {
                    deleteOldFile($note->attachment, 'upload/attachment/');
                }
                $note->delete();
            }

            return redirect()->back()->with('success', 'Note successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
