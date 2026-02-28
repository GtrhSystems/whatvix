<?php

namespace App\Http\Controllers\Api;

use Prism\Prism\Prism;
use App\Models\AiTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Support\Facades\Auth;

class AiToolsGenerateController extends Controller
{
    public function text(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'language' => 'required|string',
            'tone' => 'required|string',
            'max_token' => 'required|numeric',
            'qty' => 'required|numeric',
            'creativity' => 'required|numeric',
            'fields.*.value' => 'required|string',
        ], [
            'fields.*.value.required' => 'This field is required',
        ]);

        $template = AiTemplate::findOrFail($request->template_id);

        $aiModel = AiModel::query()->active()->where('id', $template->meta['model'])->value('code');
        $provider = data_get($template, 'meta.provider');

        abort_unless($provider && $aiModel, '403', 'Ai provider or ai model does not exits or not active.');

        $maxToken = $template->meta['max_token'];

        if ($request->max_token < $template->meta['max_token']) {
            $maxToken = $request->input('max_token');
        }

        $totalCharge = ($maxToken * $template->credit_charge) * $request->qty;

        if ($totalCharge > Auth::user()->credits) {
            throw new \Exception('You don\'t have enough credits please purchase some credits');
        }

        $language = $request->language ?? 'English';
        $tone = $request->tone ?? 'neutral';
        $userPrompt = $request->prompt ?? '';
        $qty = (int) ($request->qty ?? 1);
        $creativity = $request->creativity ?? 0.7;
        $maxToken ??= 100;

        $systemPrompt = "You are a text content generator that works strictly as per the user’s given prompt. Do not add extra commentary—only return the generated content.";
        $prompt = sprintf(
            "Please create content in %s language.\nTone: %s.\nPrompt: %s\nGenerate %d variations.",
            $language,
            $tone,
            $userPrompt,
            $qty
        );

        $responseText = Prism::text()
            ->using($provider, $aiModel)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($prompt)
            ->withMaxTokens($maxToken)
            ->usingTemperature($creativity)
            ->asText();

        Auth::user()->aiGeneratedContents()->create([
            'template_id' => $template->id,
            'content' => $responseText->text,
            'result' => $request->qty,
            'length' => $maxToken,
            'charge' => $totalCharge,
        ]);

        $splitText = explode(' ', $responseText->text);

        return response()->json($splitText);
    }
}
