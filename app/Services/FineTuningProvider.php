<?php

namespace App\Services;

use App\Exceptions\SessionException;
use App\Interface\FineTuningInterface;
use Nwidart\Modules\Facades\Module;

class FineTuningProvider implements FineTuningInterface
{
    private FineTuningInterface $providerHelper;
    private static $providerList;

    public function __construct($providerName)
    {
        $this->loadProviderMap();
        $this->providerHelper = $this->resolveProviderHelper($providerName);
    }

    private function loadProviderMap()
    {
        $providerList = array_reduce(Module::allEnabled(), function ($carry, $module) {
            if ($module->get('module_type') === 'ai_model') {
                $carry[$module->getLowerName()] = $module->get('tuning_provider');
            }
            return $carry;
        }, []);
        self::$providerList = array_filter($providerList);
    }

    private function resolveProviderHelper($providerName): FineTuningInterface
    {
        if (is_null($providerName)) {
            throw new SessionException("Tuning provider cannot be empty.");
        }
        if (!isset(self::$providerList[$providerName])) {
            throw new SessionException("Unsupported tuning provider: {$providerName}");
        }

        $providerClass = self::$providerList[$providerName];

        if (!class_exists($providerClass)) {
            throw new SessionException("Provider class not found: {$providerClass}");
        }

        if (!$this->isModuleEnabled($providerName)) {
            throw new SessionException("Provider module is not enabled: {$providerName}");
        }

        return new $providerClass();
    }

    private function isModuleEnabled($moduleName)
    {
        $enabledModules = collect(Module::allEnabled())->map(function ($module) {
            return strtolower($module->getName());
        })->flatten();

        return $enabledModules->contains(strtolower($moduleName));
    }

    public function createFineTuningJob($credential, $dataset, $title)
    {
        return $this->providerHelper->createFineTuningJob($credential, $dataset, $title);
    }
    public function getFineTuningStatus($credential, $aiTraining)
    {
        return $this->providerHelper->getFineTuningStatus($credential, $aiTraining);
    }
    public function destroyFineTunedModel($credential, $model_id)
    {
        return $this->providerHelper->destroyFineTunedModel($credential, $model_id);
    }

    /**
     * Retrieves a fine-tuned model completion for the given prompt.
     *
     * @param mixed $credential The credentials used to authenticate the request.
     * @param mixed $model_id The identifier of the model to use for generating the completion.
     * @param string|array $prompt The input prompt or array of prompts to generate the completion from.
     * @return self Returns the instance of the current object.
     */

    public function getFineTunedCompletion($credential, $model_id, string|array $prompt): self
    {
        $this->providerHelper->getFineTunedCompletion($credential, $model_id, $prompt);
        return $this;
    }

    public function generatePrompt(string $role, string $content): array
    {
        return $this->providerHelper->generatePrompt($role, $content);
    }

    public function compilationResponse(?string $key = null): mixed
    {
        return $this->providerHelper->compilationResponse($key);
    }

    public function getFineTuningJobs($credential, $lastJobId = null)
    {
        return $this->providerHelper->getFineTuningJobs($credential);
    }
}
