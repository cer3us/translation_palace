<?php

namespace App\Services;

use App\Models\TranslationMemory;
use App\Models\Glossary;
use App\Models\DifficultCase;
use Pgvector\Laravel\Vector;
use Pgvector\Laravel\Distance;
use Illuminate\Support\Facades\Log;

class TranslationPalaceService
{
    protected EmbeddingService $embeddingService;
    protected int $embeddingDimension;
    protected string $model;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
        $this->embeddingDimension = config('translation.embedding_dimension', 768);
        $this->model = config('translation.model', 'qwen2:4b');
    }

    /**
     * Core method: build a fully augmented translation prompt.
     *
     * @param string $sourceText
     * @param string $sourceLang
     * @param string $targetLang
     * @param string|null $domain      e.g. 'laravel' → scopes all retrievals to this domain
     * @param string|null $wing        e.g. 'tech'
     * @param string|null $room        e.g. 'framework'
     * @param string|null $hall        e.g. 'idioms' (currently not in metadata but ready)
     * @return string
     */
    public function buildTranslationPrompt(
        string $sourceText,
        string $sourceLang,
        string $targetLang,
        ?string $domain = null,
        ?string $wing = null,
        ?string $room = null,
        ?string $hall = null
    ): string {
        $sourceEmbedding = $this->embeddingService->generate($sourceText);

        $sourceLangName = config("translation.languages.{$sourceLang}", $sourceLang);
        $targetLangName = config("translation.languages.{$targetLang}", $targetLang);

        // 1. Glossary Atrium – scoped by palace hierarchy
        $glossaryTerms = $this->retrieveGlossaryTerms(
            $sourceText, $sourceLang, $targetLang,
            $domain, $wing, $room, $hall
        );

        // 2. Idiom Gallery (vector search) – same scoping
        $similarExamples = $this->retrieveSimilarExamples(
            $sourceEmbedding, $sourceLang, $targetLang,
            $domain, $wing, $room, $hall
        );

        // 3. Difficult Cases Library – same scoping
        $difficultHints = $this->retrieveDifficultCases(
            $sourceText, $sourceLang, $targetLang,
            $domain, $wing, $room, $hall
        );

        // 4. Assemble the final prompt
        return $this->assemblePrompt(
            $sourceText,
            $sourceLangName,
            $targetLangName,
            $glossaryTerms,
            $similarExamples,
            $difficultHints
        );
    }

    // ----------------------------------------------------------------
    // Private retrieval methods
    // ----------------------------------------------------------------

    protected function retrieveGlossaryTerms(
        string $text,
        string $sourceLang,
        string $targetLang,
        ?string $domain,
        ?string $wing,
        ?string $room,
        ?string $hall
    ): array {
        $query = Glossary::where('source_lang', $sourceLang)
            ->where('target_lang', $targetLang);

        $this->applyPalaceFilters($query, $domain, $wing, $room, $hall);

        $allTerms = $query->orderByRaw('LENGTH(term) DESC')->get();

        $matched = [];
        $remainingText = $text;

        foreach ($allTerms as $entry) {
            $term = $entry->term;
            if (stripos($remainingText, $term) !== false) {
                $translation = $entry->translation;
                // domain is still used for context_priority override
                if ($domain && isset($entry->context_priority[$domain])) {
                    $translation = $entry->context_priority[$domain];
                } elseif (isset($entry->context_priority['default'])) {
                    $translation = $entry->context_priority['default'];
                }
                $matched[$term] = $translation;
                $remainingText = str_ireplace($term, '', $remainingText);
            }
        }
        return $matched;
    }

    protected function retrieveSimilarExamples(
        array $embedding,
        string $sourceLang,
        string $targetLang,
        ?string $domain,
        ?string $wing,
        ?string $room,
        ?string $hall,
        ?float $threshold = null
    ): array {
        $vector = new Vector($embedding);

        // Try golden examples first (with threshold)
        $golden = $this->vectorSearch(
            $vector, $sourceLang, $targetLang,
            true,             // onlyGold
            $domain, $wing, $room, $hall,
            3,                // limit
            $threshold
        );

        if ($golden->isNotEmpty()) {
            return $golden->map(fn($item) => [
                'source' => $item->source_text,
                'target' => $item->translated_text,
            ])->toArray();
        }

        // Fallback to non‑gold memories (still with threshold)
        $recent = $this->vectorSearch(
            $vector, $sourceLang, $targetLang,
            false,            // any memory
            $domain, $wing, $room, $hall,
            3,
            $threshold
        );

        return $recent->map(fn($item) => [
            'source' => $item->source_text,
            'target' => $item->translated_text,
        ])->toArray();
    }

    /**
     * Vector search with palace filters.
     */
    protected function vectorSearch(
        Vector $queryVector,
        string $sourceLang,
        string $targetLang,
        bool $onlyGold,
        ?string $domain,
        ?string $wing,
        ?string $room,
        ?string $hall,
        int $limit,
        ?float $threshold = null
    ) {
        $query = TranslationMemory::query()
            ->nearestNeighbors('embedding', $queryVector, Distance::Cosine)
            ->where('source_lang', $sourceLang)
            ->where('target_lang', $targetLang);

        // Apply cosine distance threshold if configured
        $thresholdValue = $threshold ?? config('translation.vector_threshold');
        if ($thresholdValue !== null) {
            $vectorJson = json_encode($queryVector->toArray());
            $query->whereRaw('embedding <=> ? < ?', [$vectorJson, $thresholdValue]);
        }

        $this->applyPalaceFilters($query, $domain, $wing, $room, $hall);

        if ($onlyGold) {
            $query->where('is_gold', true);
        }

        return $query->take($limit)->get();
    }

    protected function retrieveDifficultCases(
        string $text,
        string $sourceLang,
        string $targetLang,
        ?string $domain,
        ?string $wing,
        ?string $room,
        ?string $hall,
        int $limit = 5,
        ?float $threshold = null
    ): array {
        $embedding = $this->embeddingService->generate($text);
        $vector = new Vector($embedding);

        $query = DifficultCase::query()
            ->nearestNeighbors('embedding', $vector, Distance::Cosine)
            ->where('source_lang', $sourceLang)
            ->where('target_lang', $targetLang);

        // Apply similarity threshold
        $threshold = $threshold ?? config('translation.vector_threshold', 0.4);
        if ($threshold !== null) {
            $vectorJson = json_encode($vector->toArray());
            $query->whereRaw('embedding <=> ? < ?', [$vectorJson, $threshold]);
        }

        $this->applyPalaceFilters($query, $domain, $wing, $room, $hall);

        return $query->take($limit)->get()
            ->map(fn($case) => [
                'source'      => $case->source_phrase,
                'target'      => $case->target_translation,
                'explanation' => $case->explanation,
            ])
            ->toArray();
    }

    /**
     * Apply optional palace hierarchy filters to any Eloquent query.
     */
    private function applyPalaceFilters($query, ?string $domain, ?string $wing, ?string $room, ?string $hall): void
    {
        if ($domain) {
            $query->where('metadata->domain', $domain);
        }
        if ($wing) {
            $query->where('metadata->wing', $wing);
        }
        if ($room) {
            $query->where('metadata->room', $room);
        }
        if ($hall) {
            $query->where('metadata->hall', $hall);
        }
    }

    protected function assemblePrompt(
        string $text,
        string $sourceLang,
        string $targetLang,
        array $glossaryTerms,
        array $similarExamples,
        array $difficultHints
    ): string {
        $prompt = "Translate the following text from {$sourceLang} to {$targetLang}.\n";

        if (!empty($glossaryTerms)) {
            $prompt .= "\n[GLOSSARY] Use these exact translations (do not change them):\n";
            foreach ($glossaryTerms as $term => $translation) {
                $prompt .= "- \"{$term}\" → \"{$translation}\"\n";
            }
        }

        if (!empty($similarExamples)) {
            $prompt .= "\n[EXAMPLES] Here are how I've translated similar content before:\n";
            foreach ($similarExamples as $i => $ex) {
                $prompt .= ($i + 1) . ". Source: {$ex['source']}\n   Translation: {$ex['target']}\n\n";
            }
        }

        if (!empty($difficultHints)) {
            $prompt .= "\n[TRICKY PHRASES]\n";
            foreach ($difficultHints as $hint) {
                $prompt .= "- Source: {$hint['source']}\n";
                $prompt .= "  Translation: {$hint['target']}\n";
                if (!empty($hint['explanation'])) {
                    $prompt .= "  Note: {$hint['explanation']}\n";
                }
                $prompt .= "\n";
            }
        }

        $prompt .= "\nNow translate this text, keeping the same style and following the above rules:\n";
        $prompt .= "Output ONLY the final translation, without any extra text, notes, or alternatives.\n";
        $prompt .= "Text: {$text}\n";
        $prompt .= "Translation:";

        return $prompt;
    }

    protected function extractKeywords(string $text): array
    {
        $words = preg_split('/[^a-zA-Zа-яА-ЯёЁ0-9]+/u', $text);
        return array_filter($words, fn($w) => mb_strlen($w) > 2);
    }

    // -----------------------------------------------------------------
    // Memory storage methods (language‑aware, with unified metadata)
    // -----------------------------------------------------------------

    public function storeMemory(
        string $sourceText,
        string $translatedText,
        array $embedding,
        string $sourceLang,
        string $targetLang,
        ?array $metadata = null,
        ?bool $isGold = null
    ): void {
        $data = array_filter([
            'translated_text' => $translatedText,
            'embedding'       => new Vector($embedding),
            'metadata'        => $metadata,
        ], fn($value) => !is_null($value));

        if (!is_null($isGold)) {
            $data['is_gold'] = $isGold;
        }

        TranslationMemory::updateOrCreate(
            [
                'source_text' => $sourceText,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
            ],
            $data
        );
    }

    //     metadata: [
    //     'domain' => 'laravel',
    //     'wing'   => 'tech',
    //     'room'   => 'framework',
    // ]

    public function storeGlossaryTerm(
        string $term,
        string $translation,
        string $sourceLang,
        string $targetLang,
        ?string $contextTag = null,
        ?array $contextPriority = null,
        ?array $metadata = null,            // unified domain/wing/room
    ): void {
        Glossary::updateOrCreate(
            [
                'term'        => $term,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
            ],
            array_filter([
                'translation'      => $translation,
                'context_tag'      => $contextTag,
                'context_priority' => $contextPriority,
                'metadata'         => $metadata,
            ])
        );
    }

    public function storeDifficultCase(
        string $sourcePhrase,
        string $targetTranslation,
        string $sourceLang,
        string $targetLang,
        ?string $explanation = null,
        ?array $tags = null,
        ?array $metadata = null            // unified domain/wing/room
    ): void {
        DifficultCase::updateOrCreate(
            [
                'source_phrase' => $sourcePhrase,
                'source_lang'   => $sourceLang,
                'target_lang'   => $targetLang,
            ],
            array_filter([
                'target_translation' => $targetTranslation,
                'explanation'        => $explanation,
                'tags'               => $tags,
                'metadata'           => $metadata,
            ])
        );
        // Generate embedding for the source phrase
        $embedding = $this->embeddingService->generate($sourcePhrase);
        DifficultCase::where('source_phrase', $sourcePhrase)
            ->where('source_lang', $sourceLang)
            ->where('target_lang', $targetLang)
            ->update(['embedding' => new Vector($embedding)]);
    }

}