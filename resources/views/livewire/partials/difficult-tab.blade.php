<div class="mb-4">
    <button wire:click="createDifficultCase" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">
        + New Difficult Case
    </button>
</div>

<table class="w-full border">
    <thead>
        <tr>
            <th class="border px-4 py-2">Source Phrase</th>
            <th class="border px-4 py-2">Target Translation</th>
            <th class="border px-4 py-2">Lang</th>
            <th class="border px-4 py-2">Tags</th>
            <th class="border px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($difficultCases as $case)
            <tr class="text-center">
                <td class="border px-4 py-2 truncate max-w-xs">{{ $case->source_phrase }}</td>
                <td class="border px-4 py-2 truncate max-w-xs">{{ $case->target_translation }}</td>
                <td class="border px-4 py-2">{{ $case->source_lang }} → {{ $case->target_lang }}</td>
                <td class="border px-4 py-2">
                    @foreach ($case->tags ?? [] as $tag)
                        <span class="inline-block bg-dark-surface rounded px-2 py-1 text-xs">{{ $tag }}</span>
                    @endforeach
                </td>
                <td class="border px-4 py-2 whitespace-nowrap">
                    <button wire:click="editDifficultCase({{ $case->id }})" class="text-accent hover:underline">Edit</button>
                    <button wire:click="showDifficultFullText({{ $case->id }})" class="text-accent hover:underline ml-2">View</button>
                    <button wire:click="deleteDifficultCase({{ $case->id }})" class="text-rose-400 hover:underline ml-2">Delete</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-4">
    {{ $difficultCases->links() }}
</div>


<div x-data="{ open: @entangle('showDifficultForm') }"
    x-show="open"
    x-cloak
    class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center"
    style="z-index:50;"
    wire:key="difficult-form-modal">

    <div class="bg-dark-bg p-6 rounded shadow-lg w-1/2">
        <h3 class="text-lg mb-4">{{ $editingDifficultId ? 'Edit' : 'Create' }} Difficult Case</h3>
        
        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="bg-dark-surface text-rose-500 p-2 mb-4 rounded">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <div class="space-y-4">
            <textarea wire:model="difficultSourcePhrase" placeholder="Source Phrase" class="border p-2 w-full"></textarea>
            <textarea wire:model="difficultTargetTranslation" placeholder="Target Translation" class="border p-2 w-full"></textarea>
            <textarea wire:model="difficultExplanation" placeholder="Explanation (optional)" class="border p-2 w-full"></textarea>

            <div class="flex space-x-2">
                <select wire:model="difficultSourceLang" class="bg-dark-bg border p-2 w-full">
                    @foreach (config('translation.languages') as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model="difficultTargetLang" class="bg-dark-bg border p-2 w-full">
                    @foreach (config('translation.languages') as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <input type="text" wire:model="difficultTags" placeholder="Tags (comma separated)" class="border p-2 w-full">

            <!-- Palace hierarchy dropdowns -->
            <select wire:model.live="difficultDomain" class="bg-dark-bg border p-2 w-full">
                <option value="">Select Domain</option>
                @foreach ($availableDomains as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($difficultDomain)
                <select wire:model.live="difficultWing" class="bg-dark-bg border p-2 w-full">
                    <option value="">Select Wing</option>
                    @foreach (config("palace.wings.{$difficultDomain}", []) as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif

            @if($difficultWing)
                <select wire:model="difficultRoom" class="bg-dark-bg border p-2 w-full">
                    <option value="">Select Room</option>
                    @foreach (config("palace.rooms.{$difficultWing}", []) as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="mt-4 flex justify-end space-x-2">
            <button wire:click="resetAllForms" class="bg-accent hover:bg-accent-hover text-white px-2 py-1 rounded">Cancel</button>
            <button wire:click="saveDifficultCase" class="bg-accent hover:bg-accent-hover text-white px-2 py-1 rounded ml-1">Save</button>
        </div>
    </div>
</div>