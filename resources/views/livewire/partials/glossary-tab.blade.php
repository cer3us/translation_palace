<div class=" mb-4">
    <button wire:click="createGlossary" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">+ New Term</button>
</div>

<table class="w-full border">
    <thead>
        <tr>
            <th class="border px-4 py-2">Term</th>
            <th class="border px-4 py-2">Translation</th>
            <th class="border px-4 py-2">Source→Target</th>
            <th class="border px-4 py-2">Context Priority</th>
            <th class="border px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($glossaries as $entry)
        <tr class="text-center">
            <td class="border px-4 py-2">{{ $entry->term }}</td>
            <td class="border px-4 py-2">{{ $entry->translation }}</td>
            <td class="border px-4 py-2">{{ $entry->source_lang }} → {{ $entry->target_lang }}</td>
            <td class="border px-4 py-2">
                @php
                    $priority = $entry->context_priority;
                @endphp
                @if(is_array($priority) && count($priority))
                    @foreach ($priority as $tag => $trans)
                        <span class="text-xs bg-dark-surface px-1">{{ $tag }}: {{ $trans }}</span>
                    @endforeach
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </td>
            <td class="flex items-center justify-center border px-4 py-2 whitespace-nowrap">
                <button wire:click="editGlossary({{ $entry->id }})" class="text-accent hover:underline">Edit</button>
                <button wire:click="showGlossaryFullText({{ $entry->id }})" class="text-accent hover:underline ml-2">View</button>
                <button wire:click="deleteGlossary({{ $entry->id }})" class="text-rose-400 hover:underline ml-2">Delete</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-4">
    {{ $glossaries->links() }}
</div>

<!-- Modal backdrop -->
<div x-data="{ open: @entangle('showGlossaryForm') }"
     x-show="open"
     x-cloak
     class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center"
     style="z-index:50;"
     wire:key="glossary-form-modal">
    <div class="bg-dark-bg p-6 rounded shadow-lg w-1/2">
        <h3 class="text-lg mb-4">{{ $editingGlossaryId ? 'Edit' : 'Create' }} Glossary Entry</h3>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="bg-dark-surface text-rose-500 p-2 mb-4 rounded">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="space-y-4">
            <input type="text" wire:model="glossaryTerm" placeholder="Term" class="border p-2 w-full">
            <input type="text" wire:model="glossaryTranslation" placeholder="Translation" class="border p-2 w-full">

            <div class="flex space-x-2">
                <select wire:model="glossarySourceLang" class="bg-dark-bg border p-2 w-full">
                    @foreach (config('translation.languages') as $code => $name)
                        <option value="{{ $code }}" class="bg-dark-surface">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model="glossaryTargetLang" class="bg-dark-bg border p-2 w-full">
                    @foreach (config('translation.languages') as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Palace hierarchy -->
            <select wire:model.live="glossaryDomain" class="bg-dark-bg border p-2 w-full">
                <option value="">Select Domain</option>
                @foreach ($availableDomains as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($glossaryDomain)
            <select wire:model.live="glossaryWing" class="bg-dark-bg border p-2 w-full">
                <option value="">Select Wing</option>
                @foreach (config('palace.wings.'.$glossaryDomain, []) as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($glossaryWing)
            <select wire:model="glossaryRoom" class="bg-dark-bg border p-2 w-full">
                <option value="">Select Room</option>
                @foreach (config('palace.rooms.'.$glossaryWing, []) as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            <textarea wire:model="glossaryContextPriority" placeholder='JSON: {"laravel":"промежуточный слой"}' class="border p-2 w-full"></textarea>
        </div>

        <div class="mt-4 flex justify-end space-x-2">
            <button wire:click="resetAllForms" class="bg-accent hover:bg-accent-hover text-white px-2 py-1 rounded">Cancel</button>
            <button wire:click="saveGlossary" class="bg-accent hover:bg-accent-hover text-white px-2 py-1 rounded ml-1">Save</button>
        </div>
    </div>
</div>