<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\TranslateText;
use App\Livewire\ManageMemories;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     return view('pages.chat');
// })->name('chat');

Route::get('/', TranslateText::class)->name('translate');
Route::get('/memories', ManageMemories::class)->name('memories.manage');