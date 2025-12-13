<?php

namespace modules\ai;

use core\App;
use core\view\View;

class OpenAi {
    public const API_KEY = 'OPENAI_KEY';

    public static function fromEnv(?string $field = null): ?static {
        if (is_null($code = App::getEnvStatic()->get($field ?? self::API_KEY))) {
            return null;
        }

        return new static($code);
    }



    public function __construct(
        protected string $apiKey
    ) {}



    public function chat(View $body): bool|string {
        $curl = curl_init("https://api.openai.com/v1/responses");

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer ". $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $body->render()
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function parseResponse(bool|string $result): ?array {
        if ($result === false) {
            return [];
        }

        $json = json_decode($result, associative: true);
        if (!isset($json['output'][0]['content'][0]['text'])) {
            return [];
        }

        return json_decode($json['output'][0]['content'][0]['text'], associative: true);
    }
}