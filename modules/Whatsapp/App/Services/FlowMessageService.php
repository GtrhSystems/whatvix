<?php

namespace Modules\Whatsapp\App\Services;

use App\Models\Flow;
use App\Models\Message;
use App\Models\Platform;
use App\Models\MessageFlow;
use App\Models\Conversation;
use App\Jobs\IncomingMessageJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Dumpable;
use Modules\Whatsapp\App\Services\MessageService;

class FlowMessageService
{
    use Dumpable;

    public Flow $flow;

    public Conversation $conversation;

    public Message $incomingMessage;

    public Platform $platform;

    public $activeNode = null;

    public array $resolvedNodes = [];

    public array $composedMessages = [];
    public array $sendMessages = [];

    public bool $sendWelcomeMessage = false;

    public function __construct(Message $incomingMessage)
    {
        $this->incomingMessage = $incomingMessage;
        $this->conversation = $incomingMessage->conversation;
        $this->platform = $incomingMessage->platform;
        $this->resolveActiveFlow();
        $this->resolveActiveNode();
        $this->resolveTargetNodes();
        $this->setActiveResolvedNode();
        $this->resolveFallbackNode();
    }

    public function resolveActiveFlow()
    {
        $platformFlow = $this->platform->getMeta('chat_flow');
        $chatFlow = $this->conversation->getMeta('flow_id');

        $this->flow = $this->platform->getActiveChatFlow();

        if ($platformFlow !== $chatFlow) {
            $this->restartChatFlow();
        }

        return $this;
    }

    /**
     * Resolves the active node for the conversation flow based on the conversation and flow meta data.
     *
     * @return $this
     */
    public function resolveActiveNode(): static
    {
        $nodes = $this->flow->meta ?? [];
        $nodeId = $this->conversation->getMeta('node_id');
        $replyMessageUuid = data_get($this->incomingMessage->meta, 'context.id');

        if ($replyMessageUuid) {
            $replyMessage = Message::with('flow')
                ->where('uuid', $replyMessageUuid)
                ->first();

            if ($replyMessage) {
                $messageFlowNodeId = $replyMessage->flow?->node_id;
                if ($messageFlowNodeId) {
                    $nodeId = $messageFlowNodeId;
                }
            }
        }


        if ($nodeId && $findNode = collect($nodes)->where('id', $nodeId)->first())
            return $this->setActiveNode($findNode);

        return $this->restartChatFlow();
    }


    public function restartChatFlow()
    {
        $this->sendWelcomeMessage = true;
        return $this->setActiveNode($this->getStarterNode());
    }

    /**
     * Sets the active node for the conversation.
     *
     * @param array $node The node to set as active.
     * @return self The instance of the class for method chaining.
     */
    public function setActiveNode(array $node, bool $withBlock = false)
    {
        // skip fallback node
        if (data_get($node, 'is_fallback'))
            return $this;

        $this->activeNode = $node;
        $newMeta = $this->conversation->meta ?? [];
        $newMeta['flow_id'] = $this->flow->id;
        $newMeta['node_id'] = $node['id'];

        if ($withBlock) {
            $newMeta['blocked_until'] = now()->addMinutes(5);
        }

        $this->conversation->update([
            'meta' => $newMeta
        ]);

        return $this;
    }

    public function setResolvedNode(array $node)
    {
        $this->resolvedNodes[] = $node;
        return $this;
    }

    /**
     * Composes messages based on the target nodes.
     *
     * @return self
     */
    public function composeMessages()
    {
        foreach ($this->resolvedNodes as $node) {
            $this->composeMessage($node);
        }

        return $this;
    }

    /**
     * Resolves the target node based on the incoming message.
     *
     * @return $this Returns the current instance of the class.
     * @throws \Exception If the action type is not supported.
     */
    public function resolveTargetNodes()
    {
        if ($this->sendWelcomeMessage) {
            return $this->setResolvedNode($this->getStarterNode());
        }

        $flow = $this->flow;
        $edges = $flow->flow_data['edges'];
        $prompt = $this->getMessageText();

        switch ($this->incomingMessage->type) {
            case 'text':

                if (
                    $prompt && in_array($prompt, $this->getStarterNode('keywords') ?? [], true)
                ) {
                    $this->setResolvedNode($this->getStarterNode());
                    break;
                }

                $targetNodeIds = collect($edges)->where('source', $this->activeNode['id'])->pluck('target');
                $targetNodes = collect($flow->meta)->whereIn('id', $targetNodeIds);

                if ($targetNodes->count() === 1) {
                    $this->setResolvedNode($targetNodes->first());
                    break;
                }

                $bestMatchTargetNode = $this->findBestMatchTargetNode($targetNodes, $prompt);
                if ($bestMatchTargetNode)
                    $this->setResolvedNode($bestMatchTargetNode);
                break;

            case 'interactive':
                $interactive = $this->incomingMessage->body;
                $actionType = $interactive['type'];

                $postBackId = $interactive[$actionType]['id'];
                $targetNodeIds = collect($edges)->where('sourceHandle', $postBackId)->pluck('target');
                $targetNodes = collect($flow->meta)->whereIn('id', $targetNodeIds);

                if ($targetNodes->count() > 0) {
                    $this->setResolvedNode($targetNodes->first());
                    break;
                }

                break;

            default:
                $this->composeTextMessage("The type ({$this->incomingMessage->type}) of message is not supported yet. Currently only text and interactive messages are supported.");
                break;
        }

        return $this;
    }

    private function getMessageText(): string
    {
        return str($this->incomingMessage->getBody('body'))->lower()->trim()->toString();
    }

    private function getStarterNode(string $key = null): array
    {
        $node = collect($this->flow->meta)->where('is_starting', true)->first();
        return $key ? data_get($node, $key) : $node;
    }

    /**
     * Sets the first resolved node as the active node.
     *
     * @return $this The instance of the class for method chaining.
     */
    private function setActiveResolvedNode()
    {
        $targetNode = collect($this->resolvedNodes)->first();
        if ($targetNode) {
            $willBlock = in_array('blocked', $targetNode['keywords'] ?? []);
            $this->setActiveNode($targetNode, $willBlock);
        }

        return $this;
    }

    /**
     * Resolve the fallback node when the incoming message is not matched with any of the existing nodes and no resolved node is set yet.
     *
     * @return $this The instance of the class for method chaining.
     */
    private function resolveFallbackNode()
    {
        if (empty($this->resolvedNodes)) {
            $fallbackMessageNode = collect($this->flow->meta)->where('is_fallback', true)->first();
            if ($fallbackMessageNode) {
                $this->setResolvedNode($fallbackMessageNode);
            }
        }

        return $this;
    }

    public function findBestMatchTargetNode(Collection $targetNodes, string $prompt): ?array
    {
        $bestMatchTargetNode = null;
        $maxMatchCount = 0;
        $targetNodes->each(function ($item) use ($prompt, &$maxMatchCount, &$bestMatchTargetNode) {
            $searchKeywords = explode(' ', $prompt);

            $itemKeywords = collect($item['keywords'] ?? [])->map(function ($keyword) {
                return str($keyword)->lower()->trim()->explode(' ')->toArray();
            })->flatten()->toArray();

            $matchCount = count(array_intersect($searchKeywords, $itemKeywords));
            if ($matchCount > $maxMatchCount) {
                $maxMatchCount = $matchCount;
                $bestMatchTargetNode = $item;
            }
        });
        return $bestMatchTargetNode;
    }

    /**
     * Composes a message based on the active node.
     *
     * @param array|null $node The active node.
     *
     * @return $this
     */
    public function composeMessage(array $node): self
    {
        if ($node['type'] === 'text-messages') {
            $this->composeTextMessage($node['body'], $node['id']);
            return $this;
        }

        $message = match ($node['type']) {
            'button-messages' => [
                'type' => 'interactive',
                'body' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $node['body'],
                    ],
                    'action' => [
                        'buttons' => collect($node['items'])->map(function ($item) {
                                return [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => $item['id'],
                                    'title' => $item['title'],
                                ],
                                ];
                            })->toArray(),
                    ],
                ],
            ],
            'list-messages' => [
                'type' => 'interactive',
                'body' => [
                    'type' => 'list',
                    'header' => [
                        'type' => 'text',
                        'text' => $node['header'],
                    ],
                    'body' => [
                        'text' => $node['body'],
                    ],
                    'footer' => [
                        'text' => $node['footer'],
                    ],
                    'action' => [
                        'button' => $node['btnText'],
                        'sections' => collect($node['items'])->flatMap(function ($sections) {
                                return collect($sections)->map(function ($item) {
                                    return [
                                    'title' => $item['title'],
                                    'rows' => collect($item['rows'])->map(function ($row) {
                                        return [
                                        'id' => $row['id'],
                                        'title' => $row['title'],
                                        'description' => $row['body'] ?? '',
                                        ];
                                    })->toArray(),
                                    ];
                                })->values()->toArray();
                            })->values()->toArray(),
                    ],
                ],
            ],
            'image-messages' => [
                'type' => 'image',
                'body' => [
                    'caption' => $node['caption'] ?? '',
                    'link' => $node['image']
                ],
            ],
            'video-messages' => [
                'type' => 'video',
                'body' => [
                    'caption' => $node['body'] ?? '',
                    'link' => $node['video']
                ],
            ],
            'audio-messages' => [
                'type' => 'audio',
                'body' => [
                    'link' => $node['audio']
                ],
            ],
            'document-messages' => [
                'type' => 'document',
                'body' => [
                    'caption' => $node['body'],
                    'link' => $node['document']
                ],
            ],
            'location-messages' => [
                'type' => 'location',
                'body' => [
                    'latitude' => $node['latitude'],
                    'longitude' => $node['longitude'],
                    'name' => $node['name'],
                    'address' => $node['address'],
                ]
            ],
            'contact-messages' => [
                'type' => 'contacts',
                'body' => [
                    [
                        'org' => $node['org'],
                        'name' => $node['name'],
                        'urls' => $node['urls'], // should be an array
                        'phones' => $node['phones'], // should be an array
                        'emails' => $node['emails'], // should be an array
                        'birthday' => $node['birthday'],
                        'addresses' => $node['addresses'], // should be an array
                    ]
                ],
            ],
            'cta-messages' => [
                'type' => 'interactive',
                'body' => [
                    'type' => 'cta_url',
                    'header' => [
                        'type' => 'text',
                        'text' => $node['header'] ?? '',
                    ],
                    'body' => [
                        'text' => $node['body'] ?? '',
                    ],
                    'footer' => [
                        'text' => $node['footer'] ?? '',
                    ],
                    'action' => [
                        'name' => 'cta_url',
                        'parameters' => [
                            'display_text' => $node['btnText'] ?? '',
                            'url' => $node['btnUrl'] ?? '',
                        ]
                    ],
                ],
            ],

            default => [
                'type' => 'text',
                'body' => [
                    'body' => $node['body'] ?? "The type: {$node['type']} message is not supported yet.",
                ]
            ]
        };

        $message['node_id'] = $node['id'];

        $this->composedMessages[] = $message;

        return $this;
    }

    /**
     * Composes a new message with the given text.
     * If the text is empty, it defaults to 'No matching result found. Please try again!'.
     *
     * @param string $text The text of the message.
     * @return $this Returns the current instance of the class.
     */
    public function composeTextMessage(string $text = null, string $nodeId = null)
    {
        $textMessage = [
            'type' => 'text',
            'body' => [
                'body' => $text ?? 'No matching result found. Please try again!'
            ]
        ];

        if ($nodeId) {
            $textMessage['node_id'] = $nodeId;
        }

        $this->composedMessages[] = $textMessage;

        return $this;
    }

    /**
     * Sends a message using the message service.
     *
     * @return self
     */
    public function sendMessage()
    {
        $messagesToSend = collect($this->composedMessages)
            ->map(function ($composedMessage) {
                $nodeId = $composedMessage['node_id'] ?? null;
                unset($composedMessage['node_id']);
                $newMessage = new Message([
                    'module' => $this->conversation->module,
                    'platform_id' => $this->conversation->platform_id,
                    'conversation_id' => $this->conversation->id,
                    'owner_id' => $this->conversation->owner_id,
                    'customer_id' => $this->conversation->customer_id,
                    'uuid' => null,
                    'direction' => 'out',
                    'status' => 'pending',
                    ...$composedMessage
                ]);
                return [
                    'node_id' => $nodeId,
                    'payload' => $newMessage
                ];

            })
            ->toArray();


        foreach ($messagesToSend as $message) {
            $messageService = new MessageService($message['payload']);
            $newMessage = $messageService->send();

            if ($newMessage->uuid) {
                $this->sendMessages[] = $newMessage;

                // Attach the message to the flow if node_id is set
                if ($message['node_id'] ?? false) {
                    $flowId = $this->flow->id;
                    MessageFlow::create([
                        'message_id' => $newMessage->id,
                        'flow_id' => $flowId,
                        'node_id' => $message['node_id']
                    ]);
                }

                // Notify the the live chat that a new message has been sent 
                dispatch(new IncomingMessageJob($newMessage->toArray()));
            }

            sleep(1);
        }
        return $this;
    }
}
