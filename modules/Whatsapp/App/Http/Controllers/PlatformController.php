<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use App\Models\Flow;
use Inertia\Inertia;
use App\Models\Platform;
use App\Models\AiTraining;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Services\ChatService;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\PlatformConfigRequest;
use Modules\QAReply\App\Models\AutoResponse;
use Modules\Whatsapp\App\Services\WhatsappClient;

class PlatformController extends Controller
{
    public function index()
    {
        $query = activeWorkspaceOwner()->platforms()->whatsapp();

        PageHeader::set(
            title: 'Platforms',
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'title' => 'Total Platforms',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:checkbox-checked",
                    'title' => 'Active Platforms',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:x-circle",
                    'title' => 'Inactive Platforms',
                    'value' => 0,
                ],
            ]
        )->when(activeWorkspaceOwnerId() == auth()->id(), function ($pageHeader) {
            $pageHeader->addLink(__('Add New'), route('user.whatsapp.platforms.create'), 'bx:plus');
        });

        $platforms = $query->clone()
            ->filterOn(['name', 'status'])
            ->paginate()
            ->through(function ($platform) {

                $method = $platform->getAutoReplyMethod();
                $auto_reply_dataset_name = match ($method) {
                    'trained_ai' => AiTraining::where('id', data_get($platform->meta, 'trained_ai'))->value('title'),
                    'chat_flow' => Flow::where('id', data_get($platform->meta, 'chat_flow'))->value('title'),
                    default => $method,
                };

                if ($platform->getMeta('type') == 'whatsapp_es') {
                    $platform->access_token = null;
                }

                $platform->meta = [
                    ...$platform->meta,
                    'auto_reply_dataset_name' => $auto_reply_dataset_name
                ];

                return $platform;
            });
            if (\Schema::hasTable('flows')) {
                $flows = Flow::query()
                ->where('user_id', activeWorkspaceOwnerId())
                ->get(['id', 'title']);

            } else {
                $flows=[];
            }


        $aiTrainings = AiTraining::query()
            ->active()
            ->where('user_id', activeWorkspaceOwnerId())
            ->get()
            ->map(function ($aiTraining) {
                return [
                    'id' => $aiTraining->id,
                    'title' => $aiTraining->title,
                    'model_name' => $aiTraining->model_name,
                ];
            });

        $autoResponses = [];

        if (Module::has('QAReply')) {
            $autoResponses = AutoResponse::query()
                ->where('owner_id', activeWorkspaceOwnerId())
                ->get(['id', 'title']);
        }



        return Inertia::render('Platforms/Index', [
            'platforms' => $platforms,
            'aiTrainings' => $aiTrainings,
            'flows' => $flows,
            'autoResponses' => $autoResponses
        ]);
    }

    public function create()
    {
        abort_unless(activeWorkspaceOwnerId() == auth()->id(), 403, 'You do not have permission to perform this action in this workspace.');
        validateWorkspacePlan('devices');
        if(file_exists(base_path('modules/WhatsappES'))){
            $hasWhatsappESmodule = Module::find('WhatsappES')->isEnabled();
            if ($hasWhatsappESmodule && Route::has('user.whatsapp-es.platforms.create')) {
                $redirectUrl = route('user.whatsapp-es.platforms.create');
                return Inertia::location($redirectUrl);
            }
        }

        PageHeader::set()->title('Platforms Create')->addBackLink(route('user.whatsapp.platforms.index'));
        return Inertia::render('Platforms/Create');
    }

     public function store(Request $request)
    {
        abort_unless(activeWorkspaceOwnerId() == auth()->id(), 403, __('You do not have permission to perform this action in this workspace.'));
        validateWorkspacePlan('devices');

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone_number_id' => 'required|string|unique:platforms,uuid', // Changed to string for flexibility
            'business_account_id' => 'required|string', // Changed to string for flexibility
            'access_token' => 'required|string',
            'provider' => 'nullable|string|in:meta,ycloud'
        ]);
        
        $provider = $validated['provider'] ?? 'meta';

        try {
            DB::beginTransaction();
            
            if ($provider === 'ycloud') {
                 // Validate YCloud credentials by fetching templates
                 try {
                     \Modules\Whatsapp\App\Services\YCloudClient::make($validated['access_token'], $validated['phone_number_id'])->getTemplates($validated['business_account_id']);
                 } catch (\Exception $e) {
                      throw new \Exception("YCloud Error: " . $e->getMessage());
                 }
            } else {
                $res = WhatsappClient::make($validated['access_token'], $validated['business_account_id'])->getTemplates($validated['business_account_id']);

                if (!$res->successful()) {
                    throw new \Exception($res->collect('error')->first());
                }
            }

            $platform = Platform::create([
                'module' => 'whatsapp',
                'owner_id' => activeWorkspaceOwnerId(),

                'name' => $validated['name'],
                'uuid' => $validated['phone_number_id'],

                'access_token' => $validated['access_token'],
                'access_token_expire_at' => now()->addDay(),

                'refresh_token' => null,
                'refresh_token_expire_at' => null,
                'meta' => Platform::defaultMeta([
                    'phone_number_id' => $validated['phone_number_id'],
                    'business_account_id' => $validated['business_account_id'],
                    'webhook_connected' => false,
                    'signup_method' => 'manual',
                    'provider' => $provider,
                ]),
            ]);


            DB::commit();

            if ($provider === 'meta') {
                $subscribeAppToWaba = $this->subscribeAppToWaba($validated['business_account_id'], $validated['access_token']);
            }

            return response()->json([
                'platform' => $platform,
                'app_url' => config('app.url'),
                'message' => __('Platform has been created successfully'),
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function show($platformUuid, $conversation_id = null)
    {
        $platform = Platform::where('uuid', $platformUuid)->firstOrFail();
        PageHeader::set()->title('Messenger Chats')->buttons([
            [
                'text' => 'Back',
                'url' => route('user.whatsapp.platforms.index'),
            ],
        ]);

        $chatService = new ChatService('whatsapp', $platform);
        $languages = json_decode(file_get_contents(base_path('database/json/languages.json')), true);
        $languages = array_values(array_map(function ($language) {
            return [
                'id' => $language['id'],
                'name' => $language['name'],
            ];
        }, $languages));
        return Inertia::render('Chats/Index', [
            'id' => $conversation_id,
            'platform' => $platform,
            'conversations' => $chatService->conversations(),
            'chat_templates' => $chatService->templates(),
            'quick_replies' => $chatService->quickReplyTemplates(),
            'badges' => $chatService->badges(),
            'languages' => $languages
        ]);
    }

    public function update(PlatformConfigRequest $request, Platform $platform)
    {
        $validated = $request->except(['access_token', 'name']);

        $meta = $platform->meta ?? Platform::defaultMeta();

        $platform->update([
            'access_token' => $request->input('access_token'),
            'name' => $request->input('name'),
            'meta' => [
                ...$meta,
                ...$validated
            ]
        ]);

        return back()->with('success', __('Platform configuration has been updated successfully'));
    }

    public function destroy(Platform $platform)
    {
        if (activeWorkspaceOwnerId() != auth()->id()) {
            return back()->with('danger', __('You do not have permission to delete this platform'));
        }

        $platform->delete();
        return back()->with('success', __('Platform has been removed successfully'));
    }

    protected function syncTemplates()
    {
        /**
         * @var \App\Models\User
         */
        $user = auth()->user();
        $platforms = $user->platforms()->whatsapp()->get();
        foreach ($platforms as $platform) {
            $platform->syncTemplates();
        }
    }

    public function logs(Platform $platform)
    {
        PageHeader::set()->title('Platforms Logs')->addBackLink(route('user.whatsapp.platforms.index'));
        $logs = $platform->logs()->paginate();
        return Inertia::render('Platforms/Logs', [
            'logs' => $logs,
            'platform' => $platform,
        ]);
    }

    function subscribeAppToWaba($wabaId, $accessToken) {
        $url = "https://graph.facebook.com/v22.0/{$wabaId}/subscribed_apps";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        $data = json_decode($response, true);

        return isset($data['success']) && $data['success'] === true;
    }
}
