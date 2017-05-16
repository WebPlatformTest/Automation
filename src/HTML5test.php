<?php

namespace HTML5test\Automate;

use GuzzleHttp\Client;

class HTML5test {

    public function __construct($config) {
        $this->config = $config;

        $this->client = new Client([
            'base_uri'  => $this->config['endpoint'],
            'timeout'   => 20.0
        ]);
    }

    public function getIdentifiers($source) {
        $response = $this->client->get('getIdentifiers', [
            'query' => [ 'source' => $source ]
        ]);

        return json_decode((string) $response->getBody());
    }

    public function getTask($source, $identifier) {
        $response = $this->client->get('getTask', [
            'query' => [ 'source' => $source, 'identifier' => $identifier ]
        ]);

        return json_decode((string) $response->getBody());
    }

    public function hasTask($task) {
        $response = $this->client->get('hasTask', [
            'query' => [ 'task' => $task ]
        ]);

        return json_decode((string) $response->getBody());
    }
}
