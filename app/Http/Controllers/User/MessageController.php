<?php

namespace App\Http\Controllers\User;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{

    public function index(Request $request)
    {
        return Message::query()
            ->whereType($request->input('type', 'text'))
            ->where('conversation_id', $request->input('conversation_id'))
            ->where('body->body', $request->input('search'))
            ->paginate();
    }

    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Message
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'type' => ['required'],
        ]);

        // Get the conversation
        $conversation = Conversation::findOrFail($request->input('conversation_id'));

        // Get the chat module
        $chatModule = ucfirst($conversation->module);

        // Get the chat service class
        $moduleChatServiceClass = "Modules\\$chatModule\\App\\Services\\ChatService";

        // Check if the chat service class exists
        throw_if(!class_exists($moduleChatServiceClass), new \Exception("Chat service class: \"{$moduleChatServiceClass}\" not found"));


        try {
            // Create the chat service
            /**
             * @var \App\Contracts\ChatServiceContract
             */
            $moduleChatService = new $moduleChatServiceClass($conversation);

            // Send the message using the chat service
            return $moduleChatService->sendMessage($request);

        } catch (\Throwable $th) {
            return back()->with('danger', $th->getMessage());
        }
    }

}
