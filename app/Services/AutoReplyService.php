<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Platform;
use App\Models\AutoReply;
use App\Models\AiTraining;
use App\Jobs\SendMessageJob;
use App\Models\Conversation;
use Modules\QAReply\App\Models\AutoResponse;
use Modules\Whatsapp\App\Services\FlowMessageService;

class AutoReplyService
{
    public string $activeModule;
    public Conversation $conversation;
    public Platform $platform;
    public User $owner;
    public ?string $messageText;

    public function __construct(
        public Message $incomingMessage
    ) {
        $this->activeModule = $incomingMessage->module;
        $this->conversation = $incomingMessage->conversation;
        $this->platform = $incomingMessage->platform;
        $this->owner = $this->platform->owner;
        $this->messageText = $this->incomingMessage->getText();
    }

    public function sendWelcomeMessage(): bool
    {
        // return if platform not exists
        $platform = $this->platform;
        if (!$platform)
            return false;

        // return if auto reply not enabled
        $send_welcome_message = $platform->getMeta('send_welcome_message', false);
        if (!$send_welcome_message)
            return false;

        // return if auto reply not enabled or message template not found
        $welcomeMessageTemplate = $platform->getMeta('welcome_message_template', '');
        if (!$welcomeMessageTemplate)
            return false;

        // return if last welcome message is not passed 24 hours
        $lastMsgSend = $this->incomingMessage->conversation->getMeta('wlc_message_send_at');
        if ($lastMsgSend && now()->diffInHours($lastMsgSend) < 24)
            return false;

        $welcomeMessage = [
            'module' => $this->activeModule,
            'platform_id' => $this->platform->id,
            'conversation_id' => $this->conversation->id,
            'owner_id' => $this->owner->id,
            'customer_id' => $this->conversation->customer_id,

            'uuid' => null,
            'direction' => 'out',
            'type' => 'text',
            'body' => [
                'type' => 'text',
                'text' => $welcomeMessageTemplate
            ],
            'status' => 'pending'
        ];

        dispatch(new SendMessageJob($welcomeMessage, true));

        return true;
    }

    public function sendAutoReply(): bool
    {
        // return if auto reply not enabled
        if ($this->isAutoReplyEnabled())
            return false;

        $autoReplyMethod = $this->platform->getMeta('auto_reply_method');

        return match ($autoReplyMethod) {
            'trained_ai' => $this->handleTrainedAiReply(),
            'chat_flow' => $this->handleChatFlowReply(),
            'auto_response' => $this->handleAutoResponseReply(),
            default => $this->handDefaultReply(),
        };
    }

    private function isAutoReplyEnabled(): bool
    {
        return !$this->platform ||
            !$this->platform->isAutoReplyEnabled() ||
            !$this->conversation->isAutoReplyEnabled();
    }

    private function handDefaultReply()
    {
        // return if prompt not found
        $prompt = data_get($this->incomingMessage->body, 'text') ?? data_get($this->incomingMessage->body, 'body') ?? '';
        if (!$prompt)
            return false;

        // return if best match not found
        $bestMatch = $this->findBestMatch($prompt);
        if (!$bestMatch)
            return false;

        $messageTemplate = $bestMatch->message_template;
        $messageType = $bestMatch->message_type;

        SendMessageJob::dispatch([
            'module' => $this->activeModule,
            'platform_id' => $this->platform->id,
            'conversation_id' => $this->conversation->id,
            'owner_id' => $this->owner->id,
            'customer_id' => $this->conversation->customer_id,

            'uuid' => null,
            'direction' => 'out',
            'type' => $messageType,
            'body' => [
                'body' => $messageTemplate
            ],
            'status' => 'pending',
        ]);

        return true;
    }

    private function handleTrainedAiReply(): bool
    {

        $platform = $this->platform;

        $aiModel = AiTraining::find(id: $platform->getMeta('trained_ai', 0));

        if (!$aiModel)
            return false;

        $aiCredential = $this->owner
            ->aiCredentials()
            ->where('provider', $aiModel->provider)
            ->first();

        $aiModelId = $aiModel->model_id;

        if (!$aiCredential)
            return false;

        $conversation = $this->conversation;

        $previousHistories = $conversation->messages()
            ->where('type', 'text')
            ->latest()
            ->limit(10)
            ->get()
            ->reverse();

        $aiFineTuning = new FineTuningProvider($aiModel->provider);

        $prompt = $this->incomingMessage->getText();
        $finalPrompt = "You are an AI assistant that does not reveal its identity under any circumstances. If a user asks for your name, identity, origin, or any related information, politely refuse or redirect the conversation without revealing any details. the user message is : " . $prompt;

        $previousHistory = $previousHistories->map(function ($message) use ($aiFineTuning) {
            return $aiFineTuning->generatePrompt(
                $message->direction == 'in' ? 'user' : 'assistant',
                $message->getText()
            );
        })
            ->values()
            ->push(
                $aiFineTuning->generatePrompt(
                    'user',
                    $finalPrompt
                )
            )
            ->toArray();

        $aiFineTuning->getFineTunedCompletion(
            $aiCredential,
            $aiModelId,
            $previousHistory
        );

        $aiMessage = $aiFineTuning->compilationResponse();

        if ($aiMessage) {
            $message = [
                'module' => $this->activeModule,
                'platform_id' => $this->platform->id,
                'conversation_id' => $this->conversation->id,
                'owner_id' => $this->owner->id,
                'customer_id' => $this->conversation->customer_id,

                'uuid' => null,
                'direction' => 'out',
                'type' => 'text',
                'body' => [
                    'type' => 'text',
                    'text' => $aiMessage
                ],
                'status' => 'pending',
            ];

            SendMessageJob::dispatch($message);
            return true;
        }

        return false;
    }

    private function handleChatFlowReply(): bool
    {

        if ($this->conversation->isFlowBlocked()) {
            return false;
        }

        // handle flow
        $flowMessageService = new FlowMessageService($this->incomingMessage);
        $flowMessageService
            ->composeMessages()
            ->sendMessage();

        return false;
    }

    private function findBestMatch(string $searchQuery): ?AutoReply
    {
        $searchTerms = explode(' ', $searchQuery);

        $potentialMatches = $this->owner
            ->autoReplies()
            ->active()
            ->module($this->activeModule)
            ->matchKeywords($searchTerms)
            ->get();

        $bestMatch = null;
        $maxMatchCount = 0;

        foreach ($potentialMatches as $potentialMatch) {
            $matchCount = count(array_intersect($searchTerms, $potentialMatch->keywords));

            if ($matchCount > $maxMatchCount) {
                $maxMatchCount = $matchCount;
                $bestMatch = $potentialMatch;
            }
        }

        return $bestMatch;
    }

    private function handleAutoResponseReply()
    {

        $prompt = $this->incomingMessage->getText();
        $autoResponseId = $this->platform->getMeta('auto_response', 0);


        /**
         * @var \Modules\QAReply\App\Models\AutoResponse
         */
        $autoResponse = AutoResponse::find($autoResponseId);

        if (!$autoResponse)
            return false;

        $bestMatchItemValue = $autoResponse->items()
            ->whereFullText(
                'key',
                $prompt
            )->value('value');

        if ($bestMatchItemValue) {
            $message = [
                'module' => $this->activeModule,
                'platform_id' => $this->platform->id,
                'conversation_id' => $this->conversation->id,
                'owner_id' => $this->owner->id,
                'customer_id' => $this->conversation->customer_id,

                'uuid' => null,
                'direction' => 'out',
                'type' => 'text',
                'body' => [
                    'type' => 'text',
                    'text' => $bestMatchItemValue
                ],
                'status' => 'pending',
            ];

            SendMessageJob::dispatch($message);
            return true;
        }
        return false;
    }

}
