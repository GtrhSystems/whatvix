<?php

namespace App\Models;

use App\Models\Campaign;
use App\Models\Template;
use App\Models\Conversation;
use Nwidart\Modules\Facades\Module;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Modules\Whatsapp\App\Models\PlatformQrCode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Traits\{HasActivityLog, HasModule, HasOwner, HasFilter};

class Platform extends Model
{
    use HasFactory, HasModule, HasOwner, HasActivityLog, HasFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'owner_id',
        'platform_id',

        'uuid',
        'name',
        'picture',

        'access_token',
        'access_token_expire_at',

        'refresh_token',
        'refresh_token_expire_at',

        'status',

        'meta',
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    protected $appends = [
        'webhook_url'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function webhookUrl(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            match ($this->module) {
                
                'whatsapp' => secure_url("api/whatsapp/device/" . str_replace('+', '', $this->uuid) . "/webhook"),
                
                default => null,
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function conversations(): hasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function customers(): hasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(PlatformQrCode::class);
    }

    public function graphApiWithBusinessAccount(string $endpoint): string
    {
        $api_url = 'https://graph.facebook.com/v18.0';
        $businessAccountId = $this->getMeta('business_account_id');
        $url = "$api_url/$businessAccountId$endpoint";

        return $url;
    }

    public function graphApiWithPhoneNumberId(string $endpoint): string
    {
        $api_url = 'https://graph.facebook.com/v18.0';
        $phoneNumberId = $this->uuid;
        $url = "{$api_url}/{$phoneNumberId}{$endpoint}";

        return $url;
    }

    public function getTemplates(): mixed
    {
        $provider = $this->getMeta('provider', 'meta');
        
        if ($provider === 'ycloud') {
             $wabaId = $this->getMeta('business_account_id');
             return \Modules\Whatsapp\App\Services\YCloudClient::make($this->access_token, $this->uuid)->getTemplates($wabaId);
        }

        $url = $this->graphApiWithBusinessAccount('/message_templates');

        return Http::withToken($this->access_token)->get($url);
    }

    public function syncTemplates(): bool
    {
        $res = $this->getTemplates();
        
        $templatesData = collect([]);

        if ($res instanceof Response) {
            throw_if($res->failed(), new \Exception($res->json('error.message'), $res->status()));
            $templatesData = $res->collect('data');
        } elseif (is_array($res)) {
            // YCloud returns array of objects
            $templatesData = collect($res)->map(function($tpl) {
                // Convert YCloud object to array
                // Assuming it implements JsonSerializable or has public properties compatible
                return json_decode(json_encode($tpl), true);
            });
        }

        $templatesData->map(function ($template) {
                $templateModel = $this->templates()
                    ->updateOrCreate(
                        [
                            'module' => $this->module,
                            'owner_id' => auth()->id(),
                            'name' => $template['name'],
                        ],
                        [
                            'type' => 'template',
                            'meta' => $template,
                            'status' => $template['status'],
                        ]
                    );
                // schedule sender where status is scheduled to pending
                $templateModel->schedule_senders()
                    ->where('status', Campaign::$STATUS_SCHEDULED)
                    ->update([
                        'status' => Campaign::$STATUS_PENDING,
                    ]);
            });

        return true;
    }

    public function createQrCodes(string $text, string $format = 'SVG')
    {
        $url = $this->graphApiWithPhoneNumberId('/message_qrdls');
        $res = Http::withToken($this->access_token)->post($url, [
            'prefilled_message' => $text,
            'generate_qr_image' => $format,
        ])->throw();

        return $this->qrCodes()->create($res->json());
    }

    public function removeQRCodes(string $code)
    {
        $url = $this->graphApiWithPhoneNumberId("/message_qrdls/$code");
        Http::withToken($this->access_token)->delete($url)->throw();

        return $this->qrCodes()->where('code', $code)->delete();
    }

    public function getQrCodes(): Response
    {
        $url = $this->graphApiWithPhoneNumberId('/message_qrdls');

        return Http::withToken($this->access_token)->get($url, ['fields' => 'code,prefilled_message,deep_link_url,qr_image_url.format(PNG)']);
    }

    public function syncQrCodes(): bool
    {
        $res = $this->getQrCodes();
        if ($res->successful()) {
            $this->qrCodes()->delete();
            $res->collect('data')->each(function ($item) {
                $this->qrCodes()->create($item);
            });

            return true;
        }

        return false;
    }

    public function logs(): hasMany
    {
        return $this->hasMany(PlatformLog::class);
    }

    public function messages(): HasManyThrough
    {
        return $this->hasManyThrough(Message::class, Conversation::class);
    }

    public static function defaultMeta(array $mergeAttrs = []): array
    {
        $defaultMeta = [
            'send_auto_reply' => false,
            'auto_reply_method' => 'default', // default, chat_flow, trained_ai

            'trained_ai' => '', // if auto_reply_method is trained_ai
            'chat_flow' => '', // if auto_reply_method is chat_flow

            'send_welcome_message' => false,
            'welcome_message_template' => "Hello {name}, how can I help you?",

            'webhook_callback_url' => "",
        ];

        return array_merge($defaultMeta, $mergeAttrs);
    }

    public function getMeta($key, $default = null)
    {
        return data_get($this->meta, $key, $default);
    }

    public function isAutoReplyEnabled(): bool
    {
        return $this->getMeta('send_auto_reply', false) && $this->getMeta('auto_reply_method');
    }

    public function getAutoReplyMethod(): ?string
    {
        return $this->getMeta('auto_reply_method', 'default');
    }

    public function isWelcomeMessageEnabled(): bool
    {
        return $this->getMeta('send_welcome_message', false);
    }

    public function getWelcomeMessageTemplate(): ?string
    {
        return $this->getMeta('welcome_message_template', "");
    }

    public function getActiveChatFlow(): ?Flow
    {
        return Flow::find($this->getMeta('chat_flow', ''));
    }
}
