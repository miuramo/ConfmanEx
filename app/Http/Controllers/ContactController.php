<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    //
    public function index()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        // update all contacts, even if paper is soft-deleted
        // \App\Models\PaperContact::truncate();
        // $papers = \App\Models\Paper::withTrashed()->get();
        // foreach ($papers as $paper) {
        //     $paper->updateContacts();
        // }
        $top_30 = \App\Models\Contact::top_n(30);

        // count of contacts
        $count = \App\Models\Contact::count();
        $count_valid = \App\Models\Contact::where('valid', true)->count();
        $count_invalid = \App\Models\Contact::where('valid', false)->count();

        $unused_contacts = \App\Models\Contact::unused();
        $invalid_contacts = \App\Models\Contact::where('valid', false)->get();
        $count_unused = $unused_contacts->count();
        return view('contact.index', compact('top_30', 'unused_contacts', 'invalid_contacts', 'count', 'count_valid', 'count_invalid', 'count_unused'));
    }

    public function call_method(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        $method = $req->method;
        if (!$method) {
            return response()->json(['error' => 'Contact not found'], 404);
        }
        \App\Models\Contact::$method();

        return redirect()->route('contact.index')->with('feedback.success', "Contact method {$method} called successfully");
    }

    // 誤っていると思われるcontactのemailを修正する
    public function modify_email(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        $pre = $req->pre;
        $post = $req->post;
        // 基本は、preがcontactemails にふくまれるPaperをすべて取得して、そのpreをpostに変更する
        if (!$pre) {
            $target_papers = new \Illuminate\Database\Eloquent\Collection();
            return view('contact.modify_email', compact('pre', 'post', 'target_papers'));
        }
        $target_papers = \App\Models\Paper::with('paperowner')->where('contactemails', 'like', "%{$pre}%")->get();
        foreach ($target_papers as $paper) {
            if (strlen($post) > 2) {
                $contactemails = $paper->contactemails;
                $new_contactemails = str_replace($pre, $post, $contactemails);
                $paper->contactemails = $new_contactemails;
                $paper->save();

                $paper->updateContacts();
            } else {
                // postが空文字の場合は、状況を表示するだけにするので、なにもしない。
            }
        }

        $target_contacts = \App\Models\Contact::with('papers')->where('email', $pre)->get();
        $target_users = \App\Models\User::with('papers')->where('email', $pre)->get();

        return view('contact.modify_email', compact('pre', 'post', 'target_papers', 'target_contacts', 'target_users'));

    }
    public function show($id)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        $contact = \App\Models\Contact::find($id);
        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }
        return view('contact.show', compact('contact'));
    }

}
