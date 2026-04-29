<div class="mb-4">
    <button wire:click="createMemory" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">
        + New Translation Memory
    </button>
</div>

<table class="w-full border">
    <thead>
        <tr>
            <th class="border px-4 py-2">Source Text</th>
            <th class="border px-4 py-2">Translated Text</th>
            <th class="border px-4 py-2">Lang</th>
            <th class="border px-4 py-2">Gold</th>
            <th class="border px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($memories as $mem)
            <tr class="text-center">
                <td class="border px-4 py-2 truncate max-w-xs text-start">{{ $mem->source_text }}</td>
                <td class="border px-4 py-2 truncate max-w-xs text-start">{{ $mem->translated_text }}</td>
                <td class="border px-4 py-2">{{ $mem->source_lang }} → {{ $mem->target_lang }}</td>
                <td class="border px-4 py-2">
                    @if ($mem->is_gold) <span class="text-yellow-400">★</span>
                    @else <span class="text-gray-500">☆</span>
                    @endif
                </td>
                <td class="flex items-center justify-center border px-4 py-2 whitespace-nowrap">
                    <button wire:click="editMemory({{ $mem->id }})" class="text-accent hover:underline">Edit</button>
                    <button wire:click="showFullText({{ $mem->id }})" class="text-accent hover:underline ml-2">View</button>
                    <button wire:click="deleteMemory({{ $mem->id }})" class="text-rose-400 hover:underline ml-2">Delete</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-4">
    {{ $memories->links() }}
</div>

<!-- Memory Modal (always in DOM, visibility toggled with Alpine) -->
<div x-data="{ open: @entangle('showMemoryForm') }"
     x-show="open"
     x-cloak
     class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center"
     style="z-index:50;"
     wire:key="memory-form-modal">
    
    <div class="bg-dark-bg p-6 rounded shadow-lg w-1/2" @click.away="open = false">
        <h3 class="text-lg mb-4">{{ $editingMemoryId ? 'Edit' : 'Create' }} Translation Memory</h3>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="bg-dark-surface text-rose-500 p-2 mb-4 rounded">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="space-y-4">
            <textarea wire:model="memorySourceText" placeholder="Source Text" class="border p-2 w-full"></textarea>
            <textarea wire:model="memoryTranslatedText" placeholder="Translated Text" class="border p-2 w-full"></textarea>

            <div class="flex space-x-2">
                <select wire:model="memorySourceLang" class="bg-dark-bg border p-2 w-full">
                    @foreach (config('translation.languages') as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model="memoryTargetLang" class="bg-dark-bg border p-2 w-full">
                    @foreach (config('translation.languages') as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center space-x-2">
                <input type="checkbox" wire:model="memoryIsGold" class="border p-2">
                <span>Gold (approved)</span>
            </label>

            <!-- Palace hierarchy dropdowns -->
            <select wire:model.live="memoryDomain" class="bg-dark-bg border p-2 w-full">
                <option value="">Select Domain</option>
                @foreach ($availableDomains as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($memoryDomain)
                <select wire:model.live="memoryWing" class="bg-dark-bg border p-2 w-full">
                    <option value="">Select Wing</option>
                    @foreach (config("palace.wings.{$memoryDomain}", []) as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif

            @if($memoryWing)
                <select wire:model="memoryRoom" class="bg-dark-bg border p-2 w-full">
                    <option value="">Select Room</option>
                    @foreach (config("palace.rooms.{$memoryWing}", []) as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="mt-4 flex justify-end space-x-2">
            <button wire:click="resetAllForms" class="bg-accent hover:bg-accent-hover text-white px-2 py-1 rounded">Cancel</button>
            <button wire:click="saveMemory" class="bg-accent hover:bg-accent-hover text-white px-2 py-1 rounded ml-1">Save</button>
        </div>
    </div>
</div>