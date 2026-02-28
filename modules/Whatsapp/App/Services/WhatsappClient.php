<?php

namespace Modules\Whatsapp\App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class WhatsappClient
{
    private $apiBaseUrl = 'https://graph.facebook.com';
    private $apiVersion = 'v22.0';

    private $apiClient;

    public function __construct(private ?string $accessToken = null, private ?string $phoneNumberId)
    {
        $this->apiClient = Http::acceptJson()
            ->baseUrl("{$this->apiBaseUrl}/{$this->apiVersion}")
            ->withToken($this->accessToken);
    }

    public function setToken(string $accessToken): PendingRequest
    {
        return $this->apiClient->withToken($accessToken);
    }

    public function cloudMediaUpload(array $attributes): Response
    {
        return $this->apiClient->post(
            "$this->phoneNumberId/media",
            $attributes
        );
    }

    public function getMediaInfo(string $mediaId): Response
    {
        return $this->apiClient->retry(3, 500)->get(
            "/$mediaId",
            [
                'phone_number_id' => $this->phoneNumberId, // optional
            ]
        );
    }

    public function getMedia(string $mediaUrl): Response
    {
        return Http::withToken($this->accessToken)->retry(3, 1000)->get($mediaUrl);
    }

    public function getTemplates(string $businessAccountId): Response
    {
        return $this->apiClient->get("$businessAccountId/message_templates");
    }

    public function postMessage(array $payload): Response
    {
        return $this->apiClient->post("$this->phoneNumberId/messages", $payload);
    }

    public function markAsRead(array $payload): Response
    {
        return $this->apiClient->post("$this->phoneNumberId/messages", $payload);
    }

    /**
     * Creates a new instance of the WhatsappClient.
     *
     * @param string $token
     * @param string|null $phoneNumberId
     * @return WhatsappClient
     */
    public static function make(string $token, string $phoneNumberId = null): WhatsappClient
    {
        return new WhatsappClient($token, $phoneNumberId);
    }
}
