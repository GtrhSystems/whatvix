<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Traits\Dumpable;

class GraphApiService
{
    use Dumpable;

    /**
     * @param string $apiBaseUrl The base URL of the API.
     * @param string $apiVersion The version of the API.
     */
    public function __construct(
        private string $apiBaseUrl,
        private string $apiVersion,
        private ?string $accessToken = null
    ) {
    }

    /**
     * Returns a new instance of the service with the given API base URL and version.
     *
     * @param string $apiBaseUrl The base URL of the API.
     * @param string $apiVersion The version of the API.
     * @return static
     */
    public static function new($apiBaseUrl, string $apiVersion): self
    {
        return new self($apiBaseUrl, $apiVersion);
    }

    /**
     * Returns a new instance of the service with the API base URL and version for Facebook Messenger.
     *
     * @param string $apiBaseUrl The base URL of the API.
     * @param string $apiVersion The version of the API.
     * @return static
     */
    public static function facebook(string $apiBaseUrl = 'https://graph.facebook.com', string $apiVersion = 'v22.0'): self
    {
        return self::new($apiBaseUrl, $apiVersion);
    }

    /**
     * Returns a new instance of the service with the API base URL and version for Instagram.
     *
     * @param string $apiBaseUrl The base URL of the API.
     * @param string $apiVersion The version of the API.
     * @return static
     */
    public static function instagram(string $apiBaseUrl = 'https://graph.instagram.com', string $apiVersion = 'v22.0'): self
    {
        return self::new($apiBaseUrl, $apiVersion);
    }

    /**
     * Creates a new instance of the HTTP client with the base URL and token.
     *
     * @param string|null $accessToken
     * @return PendingRequest
     */
    public function client(?string $accessToken = null): PendingRequest
    {
        $httpClient = Http::baseUrl($this->apiBaseUrl);

        if ($accessToken) {
            $this->accessToken = $accessToken;
        }

        if ($this->accessToken) {
            $httpClient->withToken($this->accessToken);
        }

        return $httpClient;
    }

    /**
     * Retrieves the base API URL.
     *
     * @return string The base API URL.
     */

    public function getApiUrl(): string
    {
        return $this->apiBaseUrl;
    }

    /**
     * Sets the base API URL.
     *
     * @param string $url The new base URL to be set.
     * @return self
     */

    public function setApiUrl(string $url): self
    {
        $this->apiBaseUrl = $url;
        return $this;
    }

    /**
     * Constructs a URL for the given endpoint and query parameters.
     *
     * @param string $endpoint The API endpoint.
     * @param array $query The query parameters.
     * @return string The constructed URL.
     */
    public function url(string $endpoint, array $query = []): string
    {
        $baseUrl = $this->getApiUrl();
        $endpoint = ltrim($endpoint, '/');
        return "$baseUrl/$endpoint?" . http_build_query($query);
    }

}
