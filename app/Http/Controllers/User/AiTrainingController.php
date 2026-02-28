<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Traits\Uploader;
use App\Models\AiTraining;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Imports\DatasetImport;
use App\Imports\DatasetImportCsv;
use Nwidart\Modules\Facades\Module;
use App\Exceptions\SessionException;
use App\Http\Controllers\Controller;
use App\Models\AiTrainingCredential;
use App\Services\FineTuningProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class AiTrainingController extends Controller
{
    use Uploader;

    public function testPrompt($id)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $aiTraining = AiTraining::where('user_id', Auth::id())
            ->findOrFail($id);
        $fineTuningProvider = new FineTuningProvider($aiTraining->provider);
        $credentials = $user->aiCredentials()->where('provider', $aiTraining->provider)->first();
        $prompt = $fineTuningProvider->generatePrompt('user', 'Hello!');
        $fineTuningProvider
            ->getFineTunedCompletion($credentials, $aiTraining->model_id, [$prompt]);
        if ($fineTuningProvider->compilationResponse()) {
            return back()->with('success', $fineTuningProvider->compilationResponse());
        }
        return back()->with('danger', 'Failed to generate prompt.');
    }
    public function index()
    {
        PageHeader::set()
            ->title(__('Ai Trainings'))
            ->addModal('Import Dataset', 'importDatasetModal', 'bx:upload')
            ->addLink('Add Data', route('user.ai-training.create'), 'bx:plus');

        $aiTrainingCredentials = AiTrainingCredential::where('user_id', Auth::id())->get();

        $aiModules = collect(Module::allEnabled())
            ->filter(fn($module) => $module->get('module_type') === 'ai_model')
            ->mapWithKeys(function ($module) {
                $name = $module->getLowerName();
                return [
                    $name => [
                        'config' => [
                            'schema' => config("{$name}.schema", []),
                            'info' => config("{$name}.info", []),
                        ],
                        'dropdown_items' => $module->get('dropdown_items', [])
                    ]
                ];
            });

        $providerConfig = $aiModules->map(fn($module) => $module['config'])->toArray();


        $providerConfigData = collect($providerConfig)->map(function ($config) {
            return $config['schema'];
        });

        $providerConfigSchema = [];

        foreach ($providerConfigData as $provider => $defaultConfig) {
            $credential = $aiTrainingCredentials->firstWhere('provider', $provider);

            if ($credential) {
                $metaData = $credential->meta;
                $providerConfigSchema[$provider] = array_merge($defaultConfig, $metaData);
            } else {
                $providerConfigSchema[$provider] = $defaultConfig;
            }
        }
        $demoDatasets = [
            'json' => asset('assets/dataset.json'),
            'csv' => asset('assets/dataset.csv'),
        ];

        return Inertia::render('User/AiTraining/Index', [
            'providerConfigSchema' => $providerConfigSchema,
            'providerConfig' => $providerConfig,
            'aiTrainingCredentials' => $aiTrainingCredentials,
            'aiModules' => $aiModules,
            'demoDatasets' => $demoDatasets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        validateUserPlan('ai_training');
        PageHeader::set(title: __('Ai Trainings Create'));

        $providers = collect(Module::all())
            ->filter(fn($module) => $module->get('module_type') === 'ai_model')
            ->keys()
            ->map(fn($module) => strtolower($module));
        return Inertia::render('User/AiTraining/Create', [
            'providers' => $providers
        ]);
    }
    private function createFineTuningJob($aiTraining, $provider, $title)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $credentials = $user->aiCredentials()->where('provider', $provider)->first();
        if (!$credentials) {
            return back()->with('danger', 'Ai credentials not found.');
        }

        $dataset = File::get(public_path(parse_url($aiTraining->dataset, PHP_URL_PATH)));
        $dataset = json_decode($dataset, true);

        $fineTuningProvider = new FineTuningProvider($provider);
        if ($credentials) {
            $response = $fineTuningProvider->createFineTuningJob($credentials, $dataset, $title);
            $aiTraining->update([
                'status' => $response['status'] ?? 'pending',
                'meta' => $response,
                'model_name' => $response['model_name'],
                'model_id' => $response['model_id'],
            ]);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        validateUserPlan('ai_training');
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'dataset' => 'required|array',
            'dataset.*.question' => 'required|string',
            'dataset.*.answer' => 'required|string',
        ], [
            'dataset.*.question.required' => 'The question field is required.',
            'dataset.*.answer.required' => 'The answer field is required.',
        ]);

        $dataset = $this->saveJsonFile($request, 'dataset');
        $aiTraining = AiTraining::create([
            'title' => $validated['title'],
            'provider' => $validated['provider'],
            'user_id' => Auth::id(),
            'status' => 'pending',
            'dataset' => $dataset
        ]);

        $this->createFineTuningJob($aiTraining, $validated['provider'], $validated['title']);
        return redirect()->route('user.ai-training.show', $aiTraining->provider)->with('success', 'Ai training created.');
    }


    public function storeCredentials(Request $request)
    {
        $data = $request->all();
        foreach ($data as $key => $value) {
            AiTrainingCredential::updateOrCreate(
                ['user_id' => Auth::id(), 'provider' => $key],
                ['meta' => $value]
            );
        }

        return back()->with('success', 'Ai credentials saved.');
    }
    /**
     * Display the specified resource.
     */
    public function show($provider)
    {
        PageHeader::set(title: __('Ai Trainings Data'))
            ->addLink('Add New', route('user.ai-training.create'), 'bx:plus')
            ->addLink('Sync Fine-tuning', route('user.ai-training.sync', $provider), 'bx:refresh', animate: true);
        $aiTrainings = AiTraining::where('user_id', Auth::id())
            ->where('provider', $provider)->paginate();

        return Inertia::render('User/AiTraining/Show', [
            'aiTrainings' => $aiTrainings,
            'provider' => $provider,
            'isDev' => env('APP_ENV') === 'local'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function checkStatus($id)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $aiTraining = AiTraining::where('user_id', Auth::id())
            ->findOrFail($id);
        $fineTuningProvider = new FineTuningProvider($aiTraining->provider);
        $credentials = $user->aiCredentials()->where('provider', $aiTraining->provider)->first();
        $fineTuningStatus = $fineTuningProvider
            ->getFineTuningStatus($credentials, $aiTraining);

        return back()->with('success', 'Ai training status updated to ' . $fineTuningStatus['status']);
    }
    public function update(Request $request, $id)
    {
        $aiTraining = AiTraining::where('user_id', Auth::id())
            ->where('id', $id)->firstOrFail();
        if ($aiTraining->status !== 'pending') {
            throw new SessionException('Ai training dataset cannot be edited.');
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'dataset' => 'required|array',
            'dataset.*.question' => 'required|string',
            'dataset.*.answer' => 'required|string',
        ], [
            'dataset.*.question.required' => 'The question field is required.',
            'dataset.*.answer.required' => 'The answer field is required.',
        ]);

        $this->removeFile($aiTraining->dataset);
        $dataset = $this->saveJsonFile($request, 'dataset');
        $aiTraining->update([
            'title' => $request->title,
            'dataset' => $dataset
        ]);
        return back()->with('success', 'Ai training updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $aiTraining = AiTraining::where('user_id', Auth::id())
            ->where('id', $id)->firstOrFail();

        $fineTuningProvider = new FineTuningProvider($aiTraining->provider);
        $credentials = $user->aiCredentials()->where('provider', $aiTraining->provider)->first();
        $fineTuningProvider->destroyFineTunedModel($credentials, $aiTraining->model_id);
        $aiTraining->delete();
        return back()->with('danger', 'Ai training deleted.');
    }
    public function destroyCredentials($provider)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $user->aiCredentials()->where('provider', $provider)->delete();
        return Inertia::location(route('user.ai-training.index'));
    }
    public function destroyRecord($id)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $aiTraining = AiTraining::where('user_id', $user->id)
            ->where('id', $id)->firstOrFail();
        $aiTraining->delete();
        return back()->with('danger', 'Ai training record deleted.');
    }

    public function importDataset(Request $request)
    {
        validateUserPlan('ai_training');

        $request->validate([
            'dataset' => 'required|file|mimes:json,csv,xlsx',
            'provider' => 'required|string',
            'title' => 'required|string',
            'file_type' => 'required|string|in:json,csv',
        ]);
        if ($request->hasFile('dataset')) {
            $fileExtension = $request->dataset->extension();
            if ($fileExtension == 'json' && !in_array($request->file_type, ['json'])) {
                return back()->with('danger', 'Invalid file type. Select json file.');
            }
            if (($fileExtension == 'csv' || $fileExtension == 'xlsx') && !in_array($request->file_type, ['csv', 'xlsx'])) {
                return back()->with('danger', 'Invalid file type. Select csv file.');
            }
        }
        if ($request->hasFile('dataset') && $request->file_type == 'json') {
            $importer = new DatasetImport($request->provider);
            $result = $importer->import($request);

            $this->createFineTuningJob($result['aiTraining'], $request->provider, $request->title);
            if ($result['success']) {
                return back()->with('success', 'Dataset imported successfully');
            } else {
                return back()->with('danger', $result['errors']);
            }
        }
        if ($request->hasFile('dataset') && $request->file_type == 'csv') {
            $uploadedData = $this->saveFile($request, 'dataset');
            $parsedPath = '/' . parse_url($uploadedData, PHP_URL_PATH);
            try {
                $import = new DatasetImportCsv($request->provider, $request);
                Excel::import($import, $parsedPath);
                $this->createFineTuningJob($import->aiTraining, $request->provider, $request->title);
                $this->removeFile($uploadedData);
                return redirect()->back()->with('success', 'Dataset imported successfully.');
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();

                return redirect()->back()->withErrors($failures)->withInput();
            }
        }
    }
    public function syncFineTuning($provider)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $fineTuningProvider = new FineTuningProvider($provider);
        $credentials = $user->aiCredentials()->where('provider', $provider)->first();
        $fineTuningProvider->getFineTuningJobs($credentials);
        return back()->with('success', 'Fine-tuned models synced successfully.');
    }
}
