<?php

namespace Modules\WhatsappWeb\App\Services;

use Carbon\Carbon;
use App\Models\Chat;
use App\Models\Platform;
use App\Models\AutoReply;
use App\Models\AiTraining;
use Illuminate\Support\Facades\DB;
use App\Models\AiTrainingCredential;
use App\Services\FineTuningProvider;
use Modules\QAReply\App\Models\AutoResponse;
use Modules\WhatsappWeb\App\Jobs\SendMessageJob;

class AutoReplyService
{

    public function __construct(
        public string $messageText,
        public Platform $platform,
        public Chat $chat
    ) {
    }

    public function handleAutoReply(): bool
    {

        if ($this->sendWelcomeMessage())
            return true;

        if ($this->isAutoReplyEnabled()) {
            $autoReplyMethod = $this->platform->getMeta('auto_reply_method');
            return match ($autoReplyMethod) {
                'auto_response' => $this->handleAutoResponseReply(),
                'trained_ai' => $this->handleTrainedAiReply(),
                default => $this->handleDefaultReply(),
            };
        }

        return false;
    }

    public function sendWelcomeMessage(): bool
    {
        $platform = $this->platform;

        if (!$platform) {
            return false;
        }

        $lastMessageSendAt = $this->chat->wlc_mgs_send_at;
        if (!$lastMessageSendAt) {
            return false;
        }

        $is24hPassed = now()->diffInHours($lastMessageSendAt, true) > 24;

        if (!$is24hPassed) {
            return false;
        }

        $autoReplyEnabled = $platform->getMeta('send_auto_reply', false);
        $welcomeMessageTemplate = $platform->getMeta('welcome_message_template', '');

        if (!$autoReplyEnabled || !$welcomeMessageTemplate) {
            return false;
        }

        $this->dispatchMessage([
            'text' => $welcomeMessageTemplate
        ], isWelcomeMessage: true);

        return true;
    }

    private function handleDefaultReply()
    {
        $bestMatch = $this->findBestMatch($this->messageText);
        if (!$bestMatch) {
            return false;
        }
        $textMessage = $bestMatch->message_template;
        $messageType = $bestMatch->message_type;

        $message = [
            'text' => $textMessage
        ];

        if ($messageType == 'template') {
            $template = $bestMatch->template;
            $message = $template->meta;
            $messageType = $template->type;
        }

        $this->dispatchMessage(
            $message,
            'number',
            $messageType
        );

        return true;
    }

    private function handleTrainedAiReply(): bool
    {

        $platform = $this->platform;

        $aiModel = AiTraining::find(id: $platform->getMeta('trained_ai', 0));

        if (!$aiModel) {
            return false;
        }

        $aiCredential = AiTrainingCredential::query()
            ->where('user_id', $platform->owner_id)
            ->where('provider', $aiModel->provider)
            ->first();

        $aiModelId = $aiModel->model_id;

        if (!$aiCredential)
            return false;

        $chatId = $this->chat->id;

        $aiFineTuning = new FineTuningProvider($aiModel->provider);

        $previousHistory = DB::table('message')
            ->where('remoteJid', $chatId)
            ->where(function ($query) {
                $query->whereJsonContainsKey('message->conversation')
                    ->orWhereJsonContainsKey('message->extendedTextMessage->text');
            })
            ->latest('messageTimestamp')
            ->limit(5)
            ->get()
            ->reverse()
            ->map(function ($item) use ($aiFineTuning) {
                $message = json_decode($item->message);
                $textMessage = data_get($message, 'conversation') ?? data_get($message, 'extendedTextMessage.text');
                $fromMe = json_decode($item->key)->fromMe;
                return $aiFineTuning->generatePrompt(
                    $fromMe ? 'assistant' : 'user',
                    $textMessage ?? ''
                );
            })
            ->values()
            ->toArray();

        $aiFineTuning->getFineTunedCompletion(
            $aiCredential,
            $aiModelId,
            $previousHistory
        );

        $aiMessage = $aiFineTuning->compilationResponse();

        if ($aiMessage) {
            $this->dispatchMessage([
                'text' => $aiMessage
            ]);
            return true;
        }

        return false;
    }

    private function handleAutoResponseReply()
    {

        $inputMessage = $this->messageText;
        $autoResponseId = $this->platform->getMeta('auto_response', 0);

        /**
         * @var \Modules\QAReply\App\Models\AutoResponse
         */
        $autoResponse = AutoResponse::findOrFail($autoResponseId);

        $bestMatchItemValue = $autoResponse->items()
            ->whereFullText(
                'key',
                $inputMessage
            )->value('value');

        if ($bestMatchItemValue) {
            $this->dispatchMessage([
                'text' => $bestMatchItemValue
            ]);
        }
        return false;
    }

    // helper methods
    private function isAutoReplyEnabled(): bool
    {
        return $this->platform &&
            $this->platform->isAutoReplyEnabled() &&
            $this->chat->isAutoReplyEnabled();
    }

    private function findBestMatch(string $searchQuery): ?AutoReply
    {
        $searchTerms = explode(' ', strtolower($searchQuery));
        $potentialMatches = AutoReply::query()
            ->module('whatsapp-web', )
            ->matchKeywords($searchTerms)
            ->get();

        $bestMatch = null;
        $maxMatchCount = 0;

        foreach ($potentialMatches as $potentialMatch) {
            $matchCount = count(array_intersect(
                $searchTerms,
                array_map('strtolower', $potentialMatch->keywords)
            ));

            if ($matchCount > $maxMatchCount) {
                $maxMatchCount = $matchCount;
                $bestMatch = $potentialMatch;
            }
        }

        return $bestMatch;
    }

    private function dispatchMessage(array $message, $sendType = 'number', $messageType = 'text', $isWelcomeMessage = false)
    {
        if ($messageType == 'text')
            $message['text'] = $this->replaceShortCodes($message['text'] ?? '');

        dispatch(
            new SendMessageJob(
                $this->platform->uuid,
                $this->chat->id,
                $message,
                $messageType,
                $sendType,
                $isWelcomeMessage
            )
        );
    }

    private function replaceShortCodes($text)
    {
        return str_replace(
            '{name}',
            $this->chat?->name ?? '{name}',
            $text
        );
    }
}
