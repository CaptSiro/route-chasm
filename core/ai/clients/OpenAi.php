<?php

namespace core\ai\clients;

use core\ai\AiRequest;
use core\ai\Client;
use core\App;
use core\view\View;

class OpenAi implements Client {
    public const ENV_API_KEY = 'OPENAI_KEY';
    public const ENV_MODEL = 'OPENAI_MODEL';


    public static function fromEnv(?string $apiKeyField = null, ?string $modelField = null): ?static {
        if (is_null($code = App::getEnvStatic()->get($apiKeyField ?? self::ENV_API_KEY))) {
            return null;
        }

        if (is_null($model = App::getEnvStatic()->get($modelField ?? self::ENV_MODEL))) {
            return null;
        }

        return new static($code, $model);
    }

    public static function addGenericJsonFormat(AiRequest $request): AiRequest {
        return $request->set('text', ["format" => ["type" => "json_object"]]);
    }



    public function __construct(
        protected string $apiKey,
        protected string $model
    ) {}



    public function createRequest(): AiRequest {
        return new AiRequest($this->model);
    }

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