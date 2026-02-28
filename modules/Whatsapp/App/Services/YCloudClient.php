<?php

namespace Modules\Whatsapp\App\Services;

use YCloud\Client\Configuration;
use YCloud\Client\Api\WhatsappMessagesApi;
use YCloud\Client\Model\WhatsappMessageSendRequest;
use YCloud\Client\ObjectSerializer;
use YCloud\Client\ApiException;
use Illuminate\Support\Facades\Log;

class YCloudClient
{
    private $apiInstance;
    private $apiKey;
    private $phoneNumberId;

    public function __construct(string $apiKey, string $phoneNumberId)
    {
        $this->apiKey = $apiKey;
        $this->phoneNumberId = $phoneNumberId;
        $config = Configuration::getDefaultConfiguration()->setApiKey('X-API-Key', $this->apiKey);
        $this->apiInstance = new WhatsappMessagesApi(
            new \GuzzleHttp\Client(),
            $config
        );
    }

    public static function make(string $apiKey, string $phoneNumberId): YCloudClient
    {
        return new YCloudClient($apiKey, $phoneNumberId);
    }

    public function postMessage(array $payload): mixed
    {
        try {
            $payload['from'] = $this->phoneNumberId;
            unset($payload['messaging_product']);
            unset($payload['recipient_type']);

            // Convert payload to object recursively for ObjectSerializer
            $payloadObject = json_decode(json_encode($payload));

            /** @var WhatsappMessageSendRequest $request */
            $request = ObjectSerializer::deserialize(
                $payloadObject, 
                '\YCloud\Client\Model\WhatsappMessageSendRequest'
            );
            
            $response = $this->apiInstance->send($request);
            
            // Return a wrapper that mimics Illuminate\Http\Client\Response
            return new class($response) {
                private $response;
                public function __construct($response) {
                    $this->response = $response;
                }
                public function failed() {
                    return false;
                }
                public function json($key = null) {
                    $data = [
                        'messages' => [
                            ['id' => $this->response->getId()]
                        ],
                        'status' => $this->response->getStatus()
                    ];
                    
                    if ($key) {
                        return data_get($data, $key);
                    }
                    return $data;
                }
            };

        } catch (ApiException $e) {
            Log::error("YCloud send message ApiException: " . $e->getMessage(), [
                'response_body' => $e->getResponseBody()
            ]);
            return new class($e) {
                private $e;
                public function __construct($e) {
                    $this->e = $e;
                }
                public function failed() {
                    return true;
                }
                public function json($key = null) {
                    $body = json_decode($this->e->getResponseBody(), true);
                    $message = $body['message'] ?? $this->e->getMessage();
                    
                    if ($key === 'error.message') {
                        return $message;
                    }
                    return ['error' => ['message' => $message]];
                }
            };
        } catch (\Exception $e) {
            Log::error("YCloud send message error: " . $e->getMessage());
             // Return a failed response wrapper
             return new class($e) {
                private $e;
                public function __construct($e) {
                    $this->e = $e;
                }
                public function failed() {
                    return true;
                }
                public function json($key = null) {
                    if ($key === 'error.message') {
                        return $this->e->getMessage();
                    }
                    return ['error' => ['message' => $this->e->getMessage()]];
                }
            };
        }
    }

    public function getMediaInfo(string $mediaId): mixed
    {
        // TODO: Implement YCloud media retrieval if supported
        throw new \Exception("YCloud media retrieval not implemented yet.");
    }

    public function getMedia(string $mediaUrl): mixed
    {
        // TODO: Implement YCloud media download if supported
        throw new \Exception("YCloud media download not implemented yet.");
    }

    public function getTemplates(?string $wabaId = null): array
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('X-API-Key', $this->apiKey);
        $api = new \YCloud\Client\Api\WhatsappTemplatesApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $params = ['limit' => 100];
        if ($wabaId) {
            $params['filter_waba_id'] = $wabaId;
        }

        try {
            $result = $api->list($params);
            // $result is WhatsappTemplatePage
            // return items as array
            return $result->getItems() ?? [];
        } catch (ApiException $e) {
             Log::error("YCloud get templates error: " . $e->getMessage());
             throw $e;
        }
    }
}
