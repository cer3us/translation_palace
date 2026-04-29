<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">Memory Palace Administration</h2>
        <a href="{{ route('translate') }}" class="text-sm outline hover:bg-accent-hover text-white px-2 py-1 rounded">Home</a>
    </div>

    <!-- Search bar -->
    <div class="mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search in {{ $activeTab }}..."
            class="border p-2 w-full md:w-1/2 bg-dark-bg text-white">
    </div>
    <!-- Tab navigation -->
    <div class="flex space-x-4 mb-6 ">
        <button wire:click="$set('activeTab', 'Glossary')"
                class="px-4 py-2 rounded {{ $activeTab === 'Glossary' ? 'bg-accent hover:bg-accent-hover text-white' : 'bg-gray-500 hover:bg-gray-400' }}">
            Glossary
        </button>
        <button wire:click="$set('activeTab', 'Difficult cases')"
                class="px-4 py-2 rounded {{ $activeTab === 'Difficult cases' ? 'bg-accent hover:bg-accent-hover text-white' : 'bg-gray-500 hover:bg-gray-400' }}">
            Difficult Cases
        </button>
        <button wire:click="$set('activeTab', 'Memories')"
                class="px-4 py-2 rounded {{ $activeTab === 'Memories' ? 'bg-accent hover:bg-accent-hover text-white' : 'bg-gray-500 hover:bg-gray-400' }}">
            Translation Memory
        </button>
    </div>

    @if ($activeTab === 'Glossary')
        @include('livewire.partials.glossary-tab')
    @elseif ($activeTab === 'Difficult cases')
        @include('livewire.partials.difficult-tab')
    @elseif ($activeTab === 'Memories')
        @include('livewire.partials.memory-tab')
    @endif

    <!-- Full-text viewer model -->
    @if ($viewingFullText)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-dark-surface p-6 rounded shadow-lg w-full max-w-2xl">
            <h3 class="text-lg font-semibold mb-4">Full Text</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-bold mb-1">Source Text</p>
                    <div class="bg-dark-bg p-3 rounded break-words" x-ref="source">{{ $viewingSource }}</div>
                    <button @click="navigator.clipboard.writeText($refs.source.innerText)"
                        class="mt-1 text-xs text-accent hover:underline">Copy</button>
                </div>
                <div>
                    <p class="text-sm font-bold mb-1">Translated Text</p>
                    <div class="bg-dark-bg p-3 rounded break-words">{{ $viewingTranslation }}</div>
                    <button onclick="navigator.clipboard.writeText('{{ addslashes($viewingTranslation) }}')"
                            class="mt-1 text-xs text-accent hover:underline">Copy</button>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="closeFullText" class="bg-dark-bg hover:bg-black text-white px-4 py-2 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>