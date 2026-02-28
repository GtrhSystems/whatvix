<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'short_description' => ['nullable', 'max:255'],
            'description' => 'required',
            'days' => 'required|integer',
            'price' => 'required|numeric',
            'plan_data' => 'required|array',
            'plan_data.devices.value' => 'required|integer',
            'plan_data.devices.overview' => 'nullable|string',
            'plan_data.web_messages.value' => [
                Rule::requiredIf(function () {
                    return in_array('whatsappweb', $this->input('plan_data.modules.value'));
                }),
                'integer'
            ],
            'plan_data.web_messages.overview' => 'nullable|string',
            'plan_data.cloud_messages.value' => [
                Rule::requiredIf(function () {
                    return in_array('whatsapp', $this->input('plan_data.modules.value'));
                }),
                'integer'
            ],
            'plan_data.cloud_messages.overview' => 'nullable|string',
            'plan_data.chat_flow.value' =>  [
                Rule::requiredIf(function () {
                    return in_array('Flow', $this->input('plan_data.modules.value'));
                }),
                'integer'
            ],
            'plan_data.chat_flow.overview' => 'nullable|string',
            'plan_data.custom_template.value' => 'required|integer',
            'plan_data.custom_template.overview' => 'nullable|string',
            'plan_data.credits.value' => 'required|integer',
            'plan_data.credits.overview' => 'nullable|string',
            'plan_data.storage.value' => 'required|integer',
            'plan_data.storage.overview' => 'nullable|string',
            'plan_data.workspaces.value' => 'required|integer',
            'plan_data.workspaces.overview' => 'nullable|string',
            'plan_data.team_members.value' => 'required|integer',
            'plan_data.team_members.overview' => 'nullable|string',
            'plan_data.ai_training.value' => 'required|integer',
            'plan_data.ai_training.overview' => 'nullable|string',
            'plan_data.web_scrape.value' => 'required|integer',
            'plan_data.web_scrape.overview' => 'nullable|string',
            'plan_data.apps.value' => 'required|integer',
            'plan_data.apps.overview' => 'nullable|string',
            'plan_data.contacts.value' => 'required|integer',
            'plan_data.contacts.overview' => 'nullable|string',
            'plan_data.number_scanner.value' => 'required|integer',
            'plan_data.number_scanner.overview' => 'nullable|string',
            'plan_data.campaign.value' => 'required|boolean',
            'plan_data.campaign.overview' => 'nullable|string',
            'plan_data.auto_reply.value' => 'required|boolean',
            'plan_data.auto_reply.overview' => 'nullable|string',
            'plan_data.quick_reply.value' => 'required|boolean',
            'plan_data.quick_reply.overview' => 'nullable|string',
            'plan_data.modules.value' => 'required|array|min:1',
            'plan_data.modules.overview' => 'nullable|string',
            'is_featured' => 'required|boolean',
            'is_recommended' => 'required|boolean',
            'is_trial' => 'required|boolean',
            'status' => 'required|boolean',
            'trial_days' => 'nullable|required_if:is_trial,true|integer',
            'extra_data' => 'nullable|array',
        ];
    }

    public function attributes()
    {
        return [
            'plan_data.devices.value' => 'Devices',
            'plan_data.devices.overview' => 'Devices Overview',
            'plan_data.web_messages.value' => 'Messages',
            'plan_data.web_messages.overview' => 'Messages Overview',
            'plan_data.cloud_messages.value' => 'Messages',
            'plan_data.cloud_messages.overview' => 'Messages Overview',
            'plan_data.chat_flow.value' => 'Chat Flow',
            'plan_data.chat_flow.overview' => 'Chat Flow Overview',
            'plan_data.custom_template.value' => 'Custom Template',
            'plan_data.custom_template.overview' => 'Custom Template Overview',
            'plan_data.credits.value' => 'Credits',
            'plan_data.credits.overview' => 'Credits Overview',
            'plan_data.storage.value' => 'Storage',
            'plan_data.storage.overview' => 'Storage Overview',
            'plan_data.workspaces.value' => 'Workspaces',
            'plan_data.workspaces.overview' => 'Workspaces Overview',
            'plan_data.team_members.value' => 'Team Members',
            'plan_data.team_members.overview' => 'Team Members Overview',
            'plan_data.ai_training.value' => 'AI Training Dataset',
            'plan_data.ai_training.overview' => 'AI Training Dataset Overview',
            'plan_data.web_scrape.value' => 'Web Scraping Query',
            'plan_data.web_scrape.overview' => 'Web Scraping Query Overview',
            'plan_data.apps.value' => 'My App api',
            'plan_data.apps.overview' => 'My App api Overview',
            'plan_data.contacts.value' => 'Contacts',
            'plan_data.contacts.overview' => 'Contacts Overview',
            'plan_data.campaign.value' => 'Campaign',
            'plan_data.campaign.overview' => 'Campaign Overview',
            'plan_data.auto_reply.value' => 'Auto Reply',
            'plan_data.auto_reply.overview' => 'Auto Reply Overview',
            'plan_data.quick_reply.value' => 'Quick Reply',
            'plan_data.quick_reply.overview' => 'Quick Reply Overview',
            'plan_data.modules.value' => 'Modules',
            'plan_data.modules.overview' => 'Modules Overview',
            'plan_data.number_scanner.value' => 'Number Scanner',
            'plan_data.number_scanner.overview' => 'Number Scanner Overview',
            'is_featured' => 'Is Featured',
            'is_recommended' => 'Is Recommended',
            'is_trial' => 'Is Trial',
            'status' => 'Status',
            'trial_days' => 'Trial Days',
            'extra_data.*.key' => 'Extra Perks Key',
            'extra_data.*.value' => 'Extra Perks Value',
        ];
    }
}
