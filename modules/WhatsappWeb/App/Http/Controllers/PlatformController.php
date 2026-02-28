<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Platform;
use App\Helpers\PageHeader;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ChatService;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PlatformConfigRequest;
use Modules\QAReply\App\Models\AutoResponse;
use Illuminate\Validation\ValidationException;

class PlatformController extends Controller
{
    public function index()
    {

        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();

        $buttons = [];

        $isOwner = activeWorkspaceOwnerId() === Auth::id();

        if ($isOwner) {
            $buttons[] = [
                'url' => route('user.whatsapp-web.platforms.create'),
                'text' => 'Add New'
            ];
        }

        $query = $user->platforms()->whatsappWeb();
        PageHeader::set(
            title: 'Platforms',
            buttons: $buttons,
            overviews: [
                [
                    'icon' => "bx:grid-alt",
                    'title' => 'Total Platforms',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:circle",
                    'title' => 'In Pending',
                    'value' => $query->clone()->where('status', 'pending')->count(),
                ],
                [
                    'icon' => "bx:check-circle",
                    'title' => 'Connected Platforms',
                    'value' => $query->clone()->whereIn('status', ['active', 'connected'])->count(),
                ],
                [
                    'icon' => "bx:x-circle",
                    'title' => 'Inactive Platforms',
                    'value' => $query->clone()->where(function ($q) {
                        $q->where('status', 'inactive')->orWhereNull('status');
                    })->count()
                ],
            ]
        );


        $platforms = $query->clone()->filterOn(['name', 'status'])->paginate();
        $aiTrainings = $user->aiTrainings()
            ->active()
            ->get()->map(function ($aiTraining) {
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
            'autoResponses' => $autoResponses,
        ]);
    }

    public function create()
    {
        abort_unless(activeWorkspaceOwnerId() === Auth::id(), 403);

        PageHeader::set(
            title: 'Create Platform',
        )->addBackLink(route('user.whatsapp-web.platforms.index'));
        return Inertia::render('Platforms/Create');
    }
    public function show(Platform $platform)
    {
        PageHeader::set()->title('Platforms Logs')
            ->buttons([
                [
                    'url' => route('user.whatsapp-web.platforms.index'),
                    'text' => 'Back'
                ]
            ]);
        $logs = $platform
            ->whatsappWeb()
            ->where('owner_id', activeWorkspaceOwnerId())
            ->logs()->paginate();
        return Inertia::render('Platforms/Logs', [
            'logs' => $logs,
            'platform' => $platform,
        ]);
    }
    public function store(Request $request)
    {
        abort_unless(activeWorkspaceOwnerId() === Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone_number' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    // Remove all non-digit characters except +
                    $cleaned = preg_replace('/[^\+\d]/', '', $value);

                    // Check if starts with + and has correct length
                    if (!preg_match('/^\+\d{1,4}\d{6,14}$/', $cleaned)) {
                        $fail('The ' . $attribute . ' must be a valid international phone number starting with country code (e.g., +1234567890)');
                    }
                },
            ],
        ]);

        $phoneNumber = str_replace('+', '', $validated['phone_number']);

        $exists = Platform::query()->where('owner_id', activeWorkspaceOwnerId())
            ->where('meta->phone_number', $phoneNumber)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'phone_number' => 'Phone number already exists',
            ]);
        }


        try {
            DB::beginTransaction();

            $platform = Platform::create([
                'module' => 'whatsapp-web',
                'owner_id' => activeWorkspaceOwnerId(),

                'name' => $validated['name'],
                'uuid' => Str::uuid(),
                'access_token_expire_at' => now()->addDay(),
                'status' => 'unverified',
                'meta' => [
                    'phone_number' => $phoneNumber,
                    'send_auto_reply' => false,
                    'auto_reply_method' => 'default',
                    'send_welcome_message' => false,
                    'verified' => false,
                ],
            ]);
            DB::commit();

            return to_route('user.whatsapp-web.platforms.connection', $platform->uuid);
        } catch (\Exception $ex) {
            DB::rollBack();

            throw $ex;
        }
    }

    public function update(PlatformConfigRequest $request, Platform $platform)
    {
        $meta = $platform->meta ?? Platform::defaultMeta();
        $meta = [
            ...$meta,
            ...$request->validated()
        ];
        $platform->update(['meta' => $meta]);
        return back()->with('success', __('Platform configuration has been updated successfully'));
    }

    public function destroy($id)
    {
        $platform = Platform::where('owner_id', Auth::id())
            ->where('module', 'whatsapp-web')
            ->find($id);

        if (!$platform) {
            return back()->with('danger', 'You do not have permission to delete this Platform');
        }

        $platform->delete();
        return back()->with('success', 'Platform has been removed successfully');
    }

    public function connection($uuid)
    {
        PageHeader::set(
            title: 'Platforms QrCode',
        );
        $platform = Platform::query()->whatsappWeb()
            ->where('owner_id', activeWorkspaceOwnerId())
            ->where('uuid', $uuid)->first();

        return Inertia::render('Platforms/Connection', [
            'platform' => $platform,
        ]);
    }


  

    public function showConversation($platformUuid)
    {
        $platform = activeWorkspaceOwner()->platforms()->whatsappWeb()->where('uuid', $platformUuid)->firstOrFail();

        PageHeader::set()->title('Whatsapp Chats')
            ->buttons([
                [
                    'text' => 'Back',
                    'url' => route('user.whatsapp-web.platforms.index'),
                ],
            ]);
        $languages = json_decode(file_get_contents(base_path('database/json/languages.json')), true);
        $languages = array_values(array_map(function ($language) {
            return [
                'id' => $language['id'],
                'name' => $language['name'],
            ];
        }, $languages));

        $moduleFeatures = Module::find('WhatsappWeb')->get('features');
        $chatService = new ChatService('whatsapp-web');
        return Inertia::render('Chats/Index', [
            'platforms' => [$platform],
            'languages' => $languages,
            'api_base_url' => url('api/whatsapp-web/v1'),
            'chat_templates' => $chatService->templates(),
            'quick_replies' => $chatService->quickReplyTemplates(),
            'badges' => $chatService->badges(),
            'module_features' => $moduleFeatures
        ]);
    }
}
