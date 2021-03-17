<?php

namespace App\Library\ClickUp;

class Task
{
    /**
     * ClickUp client
     *
     * @var ClickUp
     */
    private $client;

    public function __construct(ClickUp $client) {
        $this->client = $client;
    }

    /**
     * Add task to list
     *
     * @param string $listId
     * @param array|Collection $payload
     * @return
     */
    public function create($listId, $payload)
    {
        return $this->client->post("list/{$listId}/task", $payload);
    }
}
