<?php

namespace App\BTW;

use Illuminate\Support\Facades\Http;

class BTW
{
    private string $baseUrl;

    private string $apiKey;

    private string $apiSecret;
    
    public function __construct()
    {
        $this->baseUrl = config('app.api.url');
        $this->apiKey = config('app.api.key');
        $this->apiSecret = config('app.api.secret');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function searchFlights(array $data)
    {
        $url = $this->baseUrl . "/b2b/flight/search";

        $response = $this->sendRequest("POST", $url, $data);

        return $response['data']['flight'];
    }

    public function sendRequest(string $method = "GET", string $url, $data)
    {
        $http = Http::withHeaders([
            'Accept' => 'application/json',
        ]);

        if ($method == "GET") {
            $response = $http->get($url . "?key={$this->apiKey}&secret={$this->apiSecret}", $data);
        } else {
            $response = $http->post($url . "?key={$this->apiKey}&secret={$this->apiSecret}", $data);
        }

        $response->throw();

        return $response->json();
    }

    public function preBook(array $data)
    {
        $url = $this->baseUrl . "/b2b/flight/prebook";
        
        $response = $this->sendRequest("POST", $url, $data);
        
        return $response['data'];
    }
}