<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Chat;
use Illuminate\Http\Request;
use App\Events\MessageSendEvent;

class ChatController extends Controller
{
    public function index($friend) 
    {
        $friend = User::find($friend);
        $currentUser = auth()->user();
        return Inertia::render('Chat', ['friend'=> $friend, 'currentUser' => $currentUser]);
    }

    public function getMessages($friendId) 
    {
        $getMessages = Chat::with(['sender', 'receiver'])
        ->where(function ($query) use ($friendId) {
            $query->where('sender_id', auth()->user()->id)
            ->where('receiver_id', $friendId);
        })->orWhere(function ($query) use ($friendId) {
            $query->where('sender_id', $friendId)
            ->where('receiver_id', auth()->user()->id);
        })
        ->get();

        return response()->json(['data' => $getMessages]);
    }

    public function sendMessages(Request $request, $friendId)
    {
        $text = $request->text;

        $message = Chat::create([
            'sender_id' => auth()->user()->id,
            'receiver_id' => $friendId,
            'text' => $text,
        ]);
        

        broadcast(new MessageSendEvent($message));

        return $message;
    }
}
