<?php

namespace App\Imports;

use App\Models\AiTraining;
use App\Traits\Uploader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DatasetImportCsv implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    use Uploader;
    public $aiTraining;

    public function __construct(protected $provider, protected $request) {}

    public function collection(Collection $rows)
    {
        $dataset = $rows->map(fn($item) => [
            'question' => $item['question'],
            'answer' => $item['answer'],
        ]);

        $this->request->merge([
            'datasetJson' => $dataset,
        ]);

        $jsonDataPath = $this->saveJsonFile($this->request, 'datasetJson');

        $this->aiTraining = AiTraining::create([
            'user_id' => Auth::id(),
            'provider' => $this->provider,
            'title' => $this->request->title,
            'status' => 'pending',
            'dataset' => $jsonDataPath,
        ]);
    }

    public function rules(): array
    {
        return [
            'question' => 'required',
            'answer' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'question.required' => 'The question field is required.',
            'answer.required' => 'The answer field is required.',
        ];
    }
}
