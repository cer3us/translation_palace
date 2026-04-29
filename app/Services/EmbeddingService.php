<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected string $model;
    protected string $host;

    public function __construct()
    {
        $this->model = config('translation.embedding_model', 'nomic-embed-text');
        $this->host = config('translation.embedding_host', 'http://172.27.240.1:11434');
    }

    /**
     * Получить векторное представление текста.
     *
     * @param string $text
     * @return array
     * @throws \Exception
     */
    public function generate(string $text): array
    {
        $response = Http::post($this->host . '/api/embeddings', [
            'model' => $this->model,
            'prompt' => $text
        ]);

        if ($response->successful()) {
            return $response->json('embedding');
        }

        Log::error('Embedding failed', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception('Failed to get embedding: ' . $response->body());
    }
}
