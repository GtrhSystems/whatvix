<?php

namespace Modules\WhatsappWeb\App\Services;

use App\Models\Chat;
use App\Models\Customer;
use App\Models\Platform;
use App\Models\PlatformLog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class WhatsAppWebService
{
    public function apiClient()
    {
        $baseApiUrl = Config::get('whatsapp-web.base_url') ?? '';
        return Http::baseUrl(url: $baseApiUrl)->withHeaders([
            'X-API-Key' => '12345',
        ]);
    }

    public function sessionList()
    {
        return $this->apiClient()->get('/sessions');
    }
    public function setJid(string $jid)
    {
        return "{$jid}@s.whatsapp.net";
    }

    public function addSession(string $sessionId, ...$args): array
    {
        $parameters = [
            'sessionId' => $sessionId,
            'userId' => request()->user()->id,
        ];
        if ($this->findSession($sessionId)->successful()) {
            $this->deleteSession($sessionId);
            sleep(5);
        }
        if (count($args) > 0) {
            $parameters = array_merge($parameters, ...$args);
        }
        $response = $this->apiClient()->post("/sessions/add", $parameters);
        return $response->json();
    }

    public function deleteSession(string $sessionId)
    {
        $response = $this->apiClient()->delete("/sessions/{$sessionId}");
        return $response;
    }

    public function findSession(string $sessionId)
    {
        $response = $this->apiClient()->get("/sessions/{$sessionId}");
        return $response;
    }
    public function getSessionQr(string $sessionId): array
    {
        return $this->apiClient()->get("/sessions/{$sessionId}/qr")->json();
    }

     public function configClear(){
               config(['app.env' => 'local']);
       return \Artisan::call('config:clear');
    }

    public function execute($call){
        config(['app.env' => 'local']);
       return \Artisan::call($call);
    }

    public function getSessionsStatus(string $sessionId)
    {
        return $this->apiClient()->get("/sessions/{$sessionId}/status");
    }

    public function checkNumber(string $sessionId, $number)
    {
        return $this->apiClient()->get("/{$sessionId}/contacts/{$this->setJid($number)}");
    }

    public function sendMessage(string $sessionId, string $jid, array $message, string $messageType = 'text', string $type = 'number', array $options = [])
    {

        $data = [
            'jid' => $jid,
            'type' => $type,
            'message' => [],
        ];

        if (!empty($options)) {
            $data['options'] = $options;
        }

        $data["message"] = match ($messageType) {
            'text' => ['text' => $this->replaceShortCodes($message['text'], $jid)],
            'location' => [
                'location' => [
                    'degreesLatitude' => $message['latitude'],
                    'degreesLongitude' => $message['longitude'],
                ]
            ],
            'video' => [
                'video' => ['url' => $message['video']],
                'caption' => $message['caption'] ?? null,
                'gifPlayback' => $message['gifPlayback'] == 1 ? true : false,
                'ptv' => false
            ],
            'audio' => [
                'audio' => ['url' => $message['audio']],
                'mimetype' => 'audio/mp4',
                'ptt' => false,
            ],
            'voice' => [
                'audio' => [
                    'url' => $message['voice'],
                ],
                'mimetype' => 'audio/ogg',
                'ptt' => true,
            ],
            'image' => [
                'image' => ['url' => $message['image']],
                'caption' => $message['caption'] ?? null,
            ],
            'document' => [
                'document' => ['url' => $message['document']],
                'caption' => $message['caption'] ?? null,
            ],
            'poll' => [
                'poll' => [
                    'name' => $message['name'],
                    'values' => $message['values'],
                    'selectableCount' => $message['selectableCount'] ?? 1,
                ]
            ],
            default => throw new \InvalidArgumentException("Unsupported message type: {$messageType}"),
        };


        $response = $this->apiClient()->post("/{$sessionId}/messages/send", $data);

        if ($response->successful()) {
            $this->addPlatformLog($sessionId, $messageType, $response);
        }

        return $response;
    }

    private function addPlatformLog($sessionId, $messageType, $response)
    {
        $platform = Platform::query()->where('uuid', $sessionId)->firstOrFail();
        return $platform->logs()->create([
            'module' => 'whatsapp-web',
            'owner_id' => $platform->owner_id,
            'direction' => 'out',
            'message_type' => $messageType,
            'message_text' => $response->json('message.extendedTextMessage.text'),
            'meta' => $response->json(),
        ]);
    }

    public function getChats(string $sessionId, array $queryParams = [])
    {
        return $this->apiClient()->get("$sessionId/chats", $queryParams)->json();
    }

    public function getChatMessages(string $sessionId, string $chatId, array $queryParams = [])
    {
        return $this->apiClient()->get("$sessionId/chats/$chatId", $queryParams)->json();
    }

    public function readChat(string $sessionId, string $jid, array $lastMessage = [])
    {
        return $this->apiClient()->post("$sessionId/chats/$jid/read", $lastMessage)->json();
    }

    public function readMessages(string $sessionId, array $keys = [])
    {
        return $this->apiClient()->post("$sessionId/messages/read", $keys)->json();
    }

    public function getGroupMeta(string $sessionId, string $groupId, array $queryParams = [])
    {
        return $this->apiClient()->get("$sessionId/groups/$groupId", $queryParams)->json();
    }

    public function getMedia(string $sessionId, array $queryParams): Response
    {
        return $this->apiClient()
            ->withHeaders([
                'Accept' => 'application/octet-stream',
            ])
            ->post("$sessionId/messages/download", $queryParams);
    }

    public function getContactPhoto(string $sessionId, string $jid)
    {
        return $this->apiClient()->get("$sessionId/contacts/$jid/photo");
    }

    public function replaceShortCodes($text, $jid)
    {
        $customerName = Chat::where('id', $jid)->value('name');
        return str($text)->replace('{name}', $customerName ?? '{name}')->toString();
    }
}
