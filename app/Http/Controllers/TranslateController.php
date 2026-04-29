<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TranslationPalaceService;
use Illuminate\Support\Facades\Request;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class TranslateController extends Controller
{
    public function __invoke(Request $request, TranslationPalaceService $palace)
    {
        $sourceText = $request->input('text');
        $sourceLang = $request->input('source_lang', config('translation.default_source'));
        $targetLang = $request->input('target_lang', config('translation.default_target'));
        $contextTag = $request->input('context'); // optional

        // 1. Build the augmented prompt
        $prompt = $palace->buildTranslationPrompt(
            sourceText: $sourceText,
            sourceLang: $sourceLang,
            targetLang: $targetLang,
            contextTag: $contextTag
        );

        // 2. Call Ollama via Prism (using your existing pattern)
        $response = Prism::text()
            ->using(Provider::Ollama, config('translation.model'))
            ->withMessages([
                new SystemMessage("You are a professional translator."),
                new UserMessage($prompt)
            ])
            ->withClientOptions(['timeout' => 300])
            ->asText();

        return response()->json(['translation' => $response->text]);
    }
}