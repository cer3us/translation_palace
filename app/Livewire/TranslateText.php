<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TranslationPalaceService;
use App\Services\EmbeddingService;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class TranslateText extends Component
{
    // Input
    public $sourceText = '';
    public $sourceLang = 'en';
    public $targetLang = 'ru';
    public $domain = null;
    public $wing = null;
    public $room = null;

    // Output
    public $translatedText = '';
    public $originalTranslation = ''; // copy for correction tracking
    public $isLoading = false;
    public $error = '';

    // Options
    public $autoApprove = false;     // if true, automatically store as gold memory
    public $saveToMemory = false;    // store as a memory (not gold)

    protected $rules = [
        'sourceText' => 'required|string|min:2|max:12000',
    ];

    public function render()
    {
        return view('components.translate-text', [
            'languages' => config('translation.languages'),
            'domains'   => config('palace.domains', []),
            'wings'     => $this->domain ? config("palace.wings.{$this->domain}", []) : [],
            'rooms'     => $this->wing ? config("palace.rooms.{$this->wing}", []) : [],
        ]);
    }

    // Reset lower levels when parent changes
    public function updatedDomain()
    {
        $this->wing = null;
        $this->room = null;
    }

    public function updatedWing()
    {
        $this->room = null;
    }

    public function translate(TranslationPalaceService $palace, EmbeddingService $embeddingService)
    {
        $this->validate();
        $this->isLoading = true;
        $this->error = '';

        try {
            // 1. Build prompt with palace context
            $prompt = $palace->buildTranslationPrompt(
                sourceText: $this->sourceText,
                sourceLang: $this->sourceLang,
                targetLang: $this->targetLang,
                domain: $this->domain,
                wing: $this->wing,
                room: $this->room
            );

            // 2. Call Ollama via Prism
            $response = Prism::text()
                ->using(Provider::Ollama, config('translation.model'))
                ->withMessages([
                    new SystemMessage("You are a professional translator."),
                    new UserMessage($prompt)
                ])
                ->withClientOptions(['timeout' => 300])
                ->asText();

            $this->translatedText = $response->text;
            $this->originalTranslation = $this->translatedText;

            // 3. Optionally auto‑store as gold memory
            if ($this->autoApprove || $this->saveToMemory) {
                $embedding = $embeddingService->generate($this->sourceText);
                $palace->storeMemory(
                    sourceText: $this->sourceText,
                    translatedText: $this->translatedText,
                    embedding: $embedding,
                    sourceLang: $this->sourceLang,
                    targetLang: $this->targetLang,
                    metadata: array_filter([
                        'domain' => $this->domain,
                        'wing'   => $this->wing,
                        'room'   => $this->room,
                    ]),
                    isGold: $this->autoApprove,
                );
            }

        } catch (\Exception $e) {
            $this->error = 'Translation failed: ' . $e->getMessage();
        }

        $this->isLoading = false;
    }

    /**
     * Called when user edits the output and clicks “Save Correction”.
     */
    public function saveCorrection(TranslationPalaceService $palace, EmbeddingService $embeddingService)
    {
        if ($this->translatedText === $this->originalTranslation) {
            session()->flash('message', 'No changes were made.');
            return;
        }

        // Generate embedding for the source text
        $embedding = $embeddingService->generate($this->sourceText);

        // Store the corrected pair as a golden memory
        $palace->storeMemory(
            sourceText: $this->sourceText,
            translatedText: $this->translatedText,
            embedding: $embedding,
            sourceLang: $this->sourceLang,
            targetLang: $this->targetLang,
            metadata: array_filter([
                'domain' => $this->domain,
                'wing'   => $this->wing,
                'room'   => $this->room,
            ]),
            isGold: true                        // correction = always gold
        );

        // Optionally, you could try to extract glossary terms from the correction
        // and call $palace->storeGlossaryTerm(...) – that’s an advanced feature.

        $this->originalTranslation = $this->translatedText; // reset baseline
        session()->flash('message', 'Correction saved! This translation is now gold.');
    }

    /**
     * Accept the current translation (with possible minor edits) as gold.
     */
    public function acceptTranslation(TranslationPalaceService $palace, EmbeddingService $embeddingService)
    {
        $embedding = $embeddingService->generate($this->sourceText);

        $palace->storeMemory(
            sourceText: $this->sourceText,
            translatedText: $this->translatedText,
            embedding: $embedding,
            sourceLang: $this->sourceLang,
            targetLang: $this->targetLang,
            metadata: array_filter([
                'domain' => $this->domain,
                'wing'   => $this->wing,
                'room'   => $this->room,
            ]),
            isGold: true
        );

        $this->originalTranslation = $this->translatedText;
        session()->flash('message', 'Translation accepted and saved as gold.');
    }
}