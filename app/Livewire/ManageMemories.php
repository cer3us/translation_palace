<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Glossary;
use App\Models\DifficultCase;
use App\Models\TranslationMemory;
use App\Services\TranslationPalaceService;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class ManageMemories extends Component
{
    use WithPagination;

    public $activeTab = 'Glossary';

    // --- Common palace hierarchy ---
    public $availableDomains = [];
    public $availableWings   = [];
    public $availableRooms   = [];

    // --- Glossary form ---
    public $showGlossaryForm = false;
    public $editingGlossaryId = null;
    public $glossaryTerm = '';
    public $glossaryTranslation = '';
    public $glossarySourceLang = 'en';
    public $glossaryTargetLang = 'ru';
    public $glossaryDomain = '';
    public $glossaryWing = '';
    public $glossaryRoom = '';
    public $glossaryContextPriority = '';

    // --- Difficult Cases form ---
    public $showDifficultForm = false;
    public $editingDifficultId = null;
    public $difficultSourcePhrase = '';
    public $difficultTargetTranslation = '';
    public $difficultExplanation = '';
    public $difficultSourceLang = 'en';
    public $difficultTargetLang = 'ru';
    public $difficultTags = [];
    public $difficultDomain = '';
    public $difficultWing = '';
    public $difficultRoom = '';

    // --- Translation Memory form ---
    public $showMemoryForm = false;
    public $editingMemoryId = null;
    public $memorySourceText = '';
    public $memoryTranslatedText = '';
    public $memorySourceLang = 'en';
    public $memoryTargetLang = 'ru';
    public $memoryIsGold = false;
    public $memoryDomain = '';
    public $memoryWing = '';
    public $memoryRoom = '';

    // --- Full‑text viewer modal ---
    public $viewingFullText = false;
    public $viewingSource = '';
    public $viewingTranslation = '';

    // --- Search feature ---
    public $search = '';

    public function mount()
    {
        $this->availableDomains = config('palace.domains', []);
    }

    // ----------------------------------------------------------
    // Tab Switching Helper
    // ----------------------------------------------------------
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->search = '';  // clear search when switching tabs
        $this->resetPage();  // Livewire resets the current page name
        $this->resetAllForms();
    }

    public function resetAllForms()
    {
        $this->reset([
            'showGlossaryForm', 'editingGlossaryId', 'glossaryTerm', 'glossaryTranslation',
            'glossarySourceLang', 'glossaryTargetLang', 'glossaryDomain', 'glossaryWing',
            'glossaryRoom', 'glossaryContextPriority',

            'showDifficultForm', 'editingDifficultId', 'difficultSourcePhrase',
            'difficultTargetTranslation', 'difficultExplanation', 'difficultSourceLang',
            'difficultTargetLang', 'difficultTags', 'difficultDomain', 'difficultWing', 'difficultRoom',

            'showMemoryForm', 'editingMemoryId', 'memorySourceText', 'memoryTranslatedText',
            'memorySourceLang', 'memoryTargetLang', 'memoryIsGold', 'memoryDomain',
            'memoryWing', 'memoryRoom',
        ]);
        $this->resetErrorBag();
    }

    // ----------------------------------------------------------
    // Wing / Room cascading
    // ----------------------------------------------------------
    public function updatedGlossaryDomain($value)
    {
        $this->glossaryWing = '';
        $this->glossaryRoom = '';
        $this->availableWings = config("palace.wings.{$value}", []);
    }

    public function updatedGlossaryWing($value)
    {
        $this->glossaryRoom = '';
        $this->availableRooms = config("palace.rooms.{$value}", []);
    }

    // Similar cascade for difficult and memory forms...
    public function updatedDifficultDomain($value)
    {
        $this->difficultWing = '';
        $this->difficultRoom = '';
    }

    public function updatedDifficultWing($value)
    {
        $this->difficultRoom = '';
    }

    public function updatedMemoryDomain($value)
    {
        $this->memoryWing = '';
        $this->memoryRoom = '';
    }

    public function updatedMemoryWing($value)
    {
        $this->memoryRoom = '';
    }

    // ----------------------------------------------------------
    // SEARCH features
    // ----------------------------------------------------------
    /**
     * Resets page when search changes
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ----------------------------------------------------------
    // FULL TEXT VIEWER MODAL
    // ----------------------------------------------------------
    /**
     * Load a translation memory record by ID and show it in the modal.
     */
    public function showFullText($memoryId)
    {
        $memory = TranslationMemory::findOrFail($memoryId);
        $this->viewingSource = $memory->source_text;
        $this->viewingTranslation = $memory->translated_text;
        $this->viewingFullText = true;
    }

    // ----------------------------------------------------------
    // PAGE NAME for pagination
    // ----------------------------------------------------------
    protected function getPageNameProperty()
    {
        return $this->activeTab . 'Page';
    }

    /**
     * Load a difficult case by ID and show it in the modal.
     */
    public function showDifficultFullText($caseId)
    {
        $case = DifficultCase::findOrFail($caseId);
        $this->viewingSource = $case->source_phrase;
        $this->viewingTranslation = $case->target_translation;
        $this->viewingFullText = true;
    }

    /**
     * Load a glossary by ID and show it in the modal.
     */
    public function showGlossaryFullText($glossaryId)
    {
        $glossary = Glossary::findOrFail($glossaryId);
        $this->viewingSource = $glossary->term;
        $this->viewingTranslation = $glossary->translation;
        $this->viewingFullText = true;
    }


    public function closeFullText()
    {
        $this->viewingFullText = false;
        $this->viewingSource = '';
        $this->viewingTranslation = '';
    }

    // ----------------------------------------------------------
    // RENDER (+ search)
    // ----------------------------------------------------------
    public function render()
    {
        $search = '%' . $this->search . '%';

        // Initialize empty collections for all tabs
        $glossaries     = collect();
        $difficultCases = collect();
        $memories       = collect();

        // Only query the model for the active tab
        if ($this->activeTab === 'Glossary') {
            $glossaries = Glossary::where('term', 'ilike', $search)
                                ->orWhere('translation', 'ilike', $search)
                                ->orderBy('term')
                                ->paginate(15, ['*'], 'glossaryPage');
        } elseif ($this->activeTab === 'Difficult cases') {
            $difficultCases = DifficultCase::where('source_phrase', 'ilike', $search)
                                            ->orWhere('target_translation', 'ilike', $search)
                                            ->orderBy('source_phrase')
                                            ->paginate(15, ['*'], 'difficultPage');
        } elseif ($this->activeTab === 'Memories') {
            $memories = TranslationMemory::where('source_text', 'ilike', $search)
                                        ->orWhere('translated_text', 'ilike', $search)
                                        ->orderBy('created_at', 'desc')
                                        ->paginate(15, ['*'], 'memoryPage');
        }

        return view('components.manage-memories', compact('glossaries', 'difficultCases', 'memories'));
    }

    // ==========================================================
    // GLOSSARY CRUD
    // ==========================================================
    public function createGlossary()
    {
        $this->resetAllForms();
        $this->showGlossaryForm = true;
    }

    public function editGlossary($id)
    {
        $this->resetAllForms();
        $entry = Glossary::findOrFail($id);
        $this->editingGlossaryId = $id;
        $this->glossaryTerm = $entry->term;
        $this->glossaryTranslation = $entry->translation;
        $this->glossarySourceLang = $entry->source_lang;
        $this->glossaryTargetLang = $entry->target_lang;
        $this->glossaryDomain = $entry->metadata['domain'] ?? '';
        $this->glossaryWing = $entry->metadata['wing'] ?? '';
        $this->glossaryRoom = $entry->metadata['room'] ?? '';
        $this->glossaryContextPriority = json_encode(
            $entry->context_priority,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        $this->showGlossaryForm = true;
    }

    public function saveGlossary()
    {
        $this->validate([
            'glossaryTerm'       => 'required|string',
            'glossaryTranslation'=> 'required|string',
            'glossarySourceLang' => 'required',
            'glossaryTargetLang' => 'required',
        ]);

        Glossary::updateOrCreate(
            [
                'id'          => $this->editingGlossaryId,
            ],
            [
                'term'             => $this->glossaryTerm,
                'translation'      => $this->glossaryTranslation,
                'source_lang'      => $this->glossarySourceLang,
                'target_lang'      => $this->glossaryTargetLang,
                'context_priority' => json_decode($this->glossaryContextPriority, true) ?: null,
                'context_tag'      => $this->glossaryDomain ?: null,
                'metadata'         => array_filter([
                    'domain' => $this->glossaryDomain,
                    'wing'   => $this->glossaryWing,
                    'room'   => $this->glossaryRoom,
                ]),
            ]
        );

        $this->resetAllForms();
        session()->flash('message', 'Glossary entry saved successfully.');
    }

    public function deleteGlossary($id)
    {
        Glossary::destroy($id);
        session()->flash('message', 'Glossary entry deleted.');
    }

    // ==========================================================
    // DIFFICULT CASES CRUD
    // ==========================================================
    public function createDifficultCase()
    {
        $this->resetAllForms();
        $this->showDifficultForm = true;
    }

    public function editDifficultCase($id)
    {
        $this->resetAllForms();
        $entry = DifficultCase::findOrFail($id);
        $this->editingDifficultId = $id;
        $this->difficultSourcePhrase = $entry->source_phrase;
        $this->difficultTargetTranslation = $entry->target_translation;
        $this->difficultExplanation = $entry->explanation ?? '';
        $this->difficultSourceLang = $entry->source_lang;
        $this->difficultTargetLang = $entry->target_lang;
        $this->difficultTags = $entry->tags ?? [];
        $this->difficultDomain = $entry->metadata['domain'] ?? '';
        $this->difficultWing = $entry->metadata['wing'] ?? '';
        $this->difficultRoom = $entry->metadata['room'] ?? '';
        $this->showDifficultForm = true;
    }

    public function saveDifficultCase()
    {
        $this->validate([
            'difficultSourcePhrase'      => 'required|string',
            'difficultTargetTranslation'  => 'required|string',
        ]);

        DifficultCase::updateOrCreate(
            ['id' => $this->editingDifficultId],
            [
                'source_phrase'      => $this->difficultSourcePhrase,
                'target_translation' => $this->difficultTargetTranslation,
                'explanation'        => $this->difficultExplanation,
                'source_lang'        => $this->difficultSourceLang,
                'target_lang'        => $this->difficultTargetLang,
                'tags'               => $this->difficultTags,
                'metadata'           => array_filter([
                    'domain' => $this->difficultDomain,
                    'wing'   => $this->difficultWing,
                    'room'   => $this->difficultRoom,
                ]),
            ]
        );

        $this->resetAllForms();
        session()->flash('message', 'Difficult case saved successfully.');
    }

    public function deleteDifficultCase($id)
    {
        DifficultCase::destroy($id);
        session()->flash('message', 'Difficult case deleted.');
    }

    // ==========================================================
    // TRANSLATION MEMORY CRUD
    // ==========================================================
    public function createMemory()
    {
        $this->resetAllForms();
        $this->showMemoryForm = true;
    }

    public function editMemory($id)
    {
        $this->resetAllForms();
        $entry = TranslationMemory::findOrFail($id);
        $this->editingMemoryId = $id;
        $this->memorySourceText = $entry->source_text;
        $this->memoryTranslatedText = $entry->translated_text;
        $this->memorySourceLang = $entry->source_lang;
        $this->memoryTargetLang = $entry->target_lang;
        $this->memoryIsGold = (bool) $entry->is_gold;
        $this->memoryDomain = $entry->metadata['domain'] ?? '';
        $this->memoryWing = $entry->metadata['wing'] ?? '';
        $this->memoryRoom = $entry->metadata['room'] ?? '';
        $this->showMemoryForm = true;
    }

    public function saveMemory(TranslationPalaceService $palace)
    {
         $this->validate([
            'memorySourceText'    => 'required|string',
            'memoryTranslatedText' => 'required|string',
        ]);

        // Use the service to store/update memory with auto-generated embedding
        $embeddingService = app(\App\Services\EmbeddingService::class);
        $embedding = $embeddingService->generate($this->memorySourceText);

        $palace->storeMemory(
            sourceText: $this->memorySourceText,
            translatedText: $this->memoryTranslatedText,
            embedding: $embedding,
            sourceLang: $this->memorySourceLang,
            targetLang: $this->memoryTargetLang,
            metadata: array_filter([
                'domain' => $this->memoryDomain,
                'wing'   => $this->memoryWing,
                'room'   => $this->memoryRoom,
            ]),
            isGold: $this->memoryIsGold         // from the admin UI
        );

        // If we are editing an existing memory (updateOrCreate will match by source_text+langs)
        if ($this->editingMemoryId) {
            TranslationMemory::where('id', $this->editingMemoryId)->update(['is_gold' => $this->memoryIsGold]);
        }

        $this->resetAllForms();
        session()->flash('message', 'Translation memory saved successfully.');
    }

    public function deleteMemory($id)
    {
        TranslationMemory::destroy($id);
        session()->flash('message', 'Memory deleted.');
    }
}