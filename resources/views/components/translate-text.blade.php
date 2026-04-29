<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">AI Translator with Memory Palace</h2>
        <a href="{{ route('memories.manage') }}" class="text-sm outline hover:bg-accent-hover text-white px-2 py-1 rounded ">Manage memories</a>
    </div>
    <!-- Source Text -->
    <label class="block font-semibold mb-1">Source Text</label>

    <div x-data="{ count: 0 }">
        <textarea wire:model="sourceText" rows="4" class="border p-2 w-full"
                x-init="count = $el.value.length"
                @input="count = $el.value.length"
                placeholder="Enter text to translate..."></textarea>

        <div class="text-xs text-right"
            :class="count > 8000 ? 'text-rose-500' : 'text-gray-400'">
            <span x-text="count"></span> / 8000 characters recommended
        </div>
    </div>

    <!-- Language Selection -->
    <div class="flex gap-4 mt-4">
        <div class="flex-1">
            <label class="block font-semibold mb-1">Source Language</label>
            <select wire:model="sourceLang" class="bg-dark-bg border p-2 w-full">
                @foreach ($languages as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="block font-semibold mb-1">Target Language</label>
            <select wire:model="targetLang" class="bg-dark-bg border p-2 w-full">
                @foreach ($languages as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Palace Context (optional) -->
    <div class="mt-6 rounded">
        <h3 class="font-semibold mb-2">Context (Domain → Wing → Room)</h3>
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm">Domain</label>
                <select wire:model.live="domain" class="bg-dark-bg border p-2 w-full">
                    <option value="">Any</option>
                    @foreach ($domains as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if ($domain)
            <div class="flex-1">
                <label class="block text-sm">Wing</label>
                <select wire:model.live="wing" class="bg-dark-bg border p-2 w-full">
                    <option value="">Any</option>
                    @foreach ($wings as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif


            @if ($wing)
            <div class="flex-1">
                <label class="block text-sm">Room</label>
                <select wire:model="room" class="bg-dark-bg border p-2 w-full">
                    <option value="">Any</option>
                    @foreach ($rooms as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    <!-- Translate Button -->
    <div class="mt-4 flex items-center gap-4">
        <button wire:click="translate" wire:loading.attr="disabled"
                class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded">
            <span wire:loading.remove>Translate</span>
            <span wire:loading>Translating...</span>
        </button>

        <div class="flex items-center gap-4">
            <label class="bg-dark-surface flex items-center gap-2 text-sm"
                x-data
                :class="{ 'opacity-50 cursor-not-allowed': $wire.saveToMemory }">
                <input type="checkbox" wire:model="autoApprove"
                    x-bind:disabled="$wire.saveToMemory">
                Auto‑save as gold memory
            </label>

            <label class="bg-dark-surface flex items-center gap-2 text-sm"
                x-data
                :class="{ 'opacity-50 cursor-not-allowed': $wire.autoApprove }">
                <input type="checkbox" wire:model="saveToMemory"
                    x-bind:disabled="$wire.autoApprove">
                Save to memory (not gold)
            </label>
        </div>
    </div>

    <!-- Error -->
    @if ($error)
        <div class="mt-4 p-3 bg-dark-surface text-rose-500 rounded">{{ $error }}</div>
    @endif

   <!-- Result -->
    @if ($translatedText)
        <div class="mt-6 p-4 bg-dark-bg border rounded shadow">
            <h3 class="font-semibold mb-2">Translation</h3>
            <textarea wire:model="translatedText" x-ref="translationOutput" rows="4" class="border p-2 w-full"></textarea>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <!-- Accept button (store as gold even without editing) -->
                <button wire:click="acceptTranslation"
                        class="bg-accent hover:bg-accent-hover text-white px-3 py-1 rounded">
                    Accept as Gold
                </button>

                <!-- Save Correction button (only if text was edited) -->
                <button wire:click="saveCorrection"
                        class="bg-dark-surface hover:bg-dark-bg text-white px-3 py-1 rounded"
                        title="Use this if you've made corrections">
                    Save Correction
                </button>

                <!-- Copy to clipboard (Alpine.js) -->
                <span x-data="{ copied: false }" class="relative">
                    <button @click="
                        navigator.clipboard.writeText($refs.translationOutput.value);
                        copied = true;
                        setTimeout(() => copied = false, 1500)
                        "
                        x-ref="copyBtn"
                        class="bg-dark-surface hover:bg-dark-bg text-white px-3 py-1 rounded"
                    >
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Copied!</span>
                    </button>
                </span>
            </div>

            @if (session()->has('message'))
                <div class="mt-2 p-2 bg-dark-surface text-green-500 rounded">{{ session('message') }}</div>
            @endif
        </div>
    @endif
</div>