<?php

namespace App\Interface;

interface FineTuningInterface
{
    public function createFineTuningJob($credential, $dataset, $title);
    public function getFineTuningStatus($credential, $aiTraining);
    public function getFineTunedCompletion($credential, $model_id, string|array $prompt): self;
    public function destroyFineTunedModel($credential, $model_id);
    public function compilationResponse(?string $key = null): mixed;
    public function generatePrompt(string $role, string $content): array;
    public function getFineTuningJobs($credential, $lastJobId = null);
}
