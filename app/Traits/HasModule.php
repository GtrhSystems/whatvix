<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasModule
{

    public function scopeModule(Builder $query, string $moduleName): Builder
    {
        return $query->where('module', $moduleName);
    }

    public function scopeWhatsapp(Builder $query): Builder
    {
        return $query->module('whatsapp');
    }

    public function scopeTelegram(Builder $query): Builder
    {
        return $query->module('telegram');
    }

    public function scopeMessenger(Builder $query): Builder
    {
        return $query->module('messenger');
    }

    public function scopeWhatsappweb(Builder $query): Builder
    {
        return $query->module('whatsapp-web');
    }
}
