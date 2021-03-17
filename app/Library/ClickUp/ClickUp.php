<?php

namespace App\Library\ClickUp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ClickUp
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $token;

    /**
     * @var PendingRequest
     */
    private $client;

    /**
     * @var Task
     */
    public $task;

    public function __construct() {
        $this->url = config('clickup.url');
        $this->token = config('clickup.token');
        $this->client = Http::baseUrl($this->url)
            ->withHeaders([
                'Authorization' => $this->token,
            ]);

        $this->task = new Task($this);
    }

    public function post($uri, $payload = [])
    {
        return $this->client->post($uri, $payload)->json();
    }
}
