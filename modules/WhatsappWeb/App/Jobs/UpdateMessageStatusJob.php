<?php

namespace Modules\WhatsappWeb\App\Jobs;

use App\Models\CampaignLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateMessageStatusJob implements ShouldQueue
{
    use Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $payload)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $event = data_get($this->payload, 'event');

        $log = CampaignLog::query()
            ->whatsappWeb()
            ->where('meta->response->key->id', $this->getMessageId())->firstOrFail();

        $updateColumns = match ($event) {
            'messages.update' => $this->getUpdateColumnsForMessagesUpdate($log),
            default => [],
        };

        if ($updateColumns) {
            $log->update($updateColumns);
        }
    }

    private function getUpdateColumnsForMessagesUpdate(CampaignLog $log): array
    {
        $messageId = data_get($this->payload, 'data.messages.key.id');
        $status = data_get($this->payload, 'data.messages.status');

        if (!$messageId || !$status) {
            return [];
        }

        $updateColumn = match ($status) {
            2 => 'send_at',
            3 => 'delivered_at',
            4 => 'read_at',
            default => null
        };

        if (!$updateColumn) {
            return [];
        }

        $updateColumns = [$updateColumn => now()];

        if ($log->send_at === null && in_array($updateColumn, ['delivered_at', 'read_at'])) {
            $updateColumns['send_at'] = now();
        }

        return $updateColumns;
    }

    private function getMessageId(): string
    {
        return data_get($this->payload, 'data.messages.key.id');
    }
}
