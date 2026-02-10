<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index()
    {
        return response()->json(ContactMessage::latest()->paginate(30));
    }

    public function markRead(ContactMessage $message)
    {
        $message->update(['is_read' => true]);
        return response()->json(['message' => 'Marked as read.']);
    }
}
