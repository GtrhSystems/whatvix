<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait HasMembership
{
    protected static function bootHasMembership()
    {
        static::creating(function (Model $model) {
            throw_if(Auth::guest(), 'Auth user not found in HasMembership trait.');
            $key = self::resolvePlanKeyBy($model);
            throw_unless($key, 'Plan Key could not resolved for model: ' . $model::class);
            validateWorkspacePlan($key);
        });
    }

    private static function resolvePlanKeyBy(Model $model)
    {
        return match (class_basename($model)) {
            'Platform' => 'devices',
            'Message' => fn() => match ($model->module) {
                'whatsapp' => 'cloud_messages',
                'whatsapp_web' => 'web_messages'
            },
            'Flow' => 'chat_flow',
            'Asset' => 'storage',
            'Workspace' => 'workspaces',
            'User' => 'team_members',
            'AiTraining' => 'ai_training',
            'WebScraping' => 'web_scrape',
            'CloudApp', 'WhatsappWebApp' => 'apps',
            'Customer' => 'contacts',
            'Template' => 'custom_template',
         
            'Campaign' => 'campaign',
            'AutoReply' => 'auto_reply',
            'QuickReply' => 'quick_reply',
            default => false
        };
    }
}
