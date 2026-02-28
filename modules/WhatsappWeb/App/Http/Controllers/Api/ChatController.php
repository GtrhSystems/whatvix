<?php

namespace Modules\WhatsappWeb\App\Http\Controllers\Api;

use DB;
use App\Models\Chat;
use App\Traits\Uploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class ChatController extends Controller
{

    use Uploader;

    private WhatsAppWebService $chatService;

    public function __construct()
    {
        $this->chatService = new WhatsAppWebService();
    }


    public function chats(string $sessionId)
    {
        return $this->chatService->getChats($sessionId, request()->all());
    }

    public function chatMessages(string $sessionId, string $chatId)
    {
        return $this->chatService->getChatMessages($sessionId, $chatId, request()->all());
    }

    public function readChat(Request $request, string $sessionId, string $jid)
    {
        return $this->chatService->readChat(
            $sessionId,
            $jid,
            $request->all()
        );
    }

    public function groupMessages(string $sessionId, string $chatId)
    {
        return $this->chatService->getGroupMeta($sessionId, $chatId, request()->all());
    }

    public function getMedia(string $sessionId)
    {
        $response = $this->chatService->getMedia($sessionId, request()->all());

        // Check if the request was successful
        if ($response->successful()) {
            return response($response->body(), 200)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="media-file"');
        }

        return response()->json(['error' => 'Failed to fetch data'], 500);
    }

    public function sendMessage(Request $request, string $sessionId)
    {

        $message = $request->message;

        if ($request->messageType == 'voice') {
            $request->validate([
                'message.voice' => 'required'
            ], [
                'message.voice.required' => 'Voice message is required',
            ]);

            $file = $request->file('message.voice');
            $directory = 'uploads' . date('/y') . '/' . date('m');
            $uploadedUri = $file->store($directory);
            $message['voice'] = Storage::url($uploadedUri);
        }

        $jid = $request->jid;
        $messageType = $request->messageType;
        $sendType = $request->type;
        $options = $request->options ?? [];

        $res = $this->chatService->sendMessage(
            $sessionId,
            $jid,
            $message,
            $messageType,
            $sendType,
            $options
        );

        return $res->throw()->json();
    }

    public function readMessages(string $sessionId)
    {
        $keys = request()->all();
        return $this->chatService->readMessages($sessionId, $keys);
    }

    public function getContactPhoto(string $sessionId, string $jid)
    {
        $res = $this->chatService->getContactPhoto($sessionId, $jid);
        if ($res->successful()) {
            $fileUrl = $res->json('url');
            $uploadedFileUrl = $this->saveFileFromUrl($fileUrl);

            $chat = Chat::query()
                ->where([
                    'sessionId' => $sessionId,
                    'id' => $jid
                ])
                ->update(['picture' => $uploadedFileUrl]);

            return response()->json([
                'success' => 'File saved successfully',
                'url' => $uploadedFileUrl
            ]);
        }
    }

}
