<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Nwidart\Modules\Facades\Module;

final class MenuHelper
{
    public static array $menus = [];

    public static function add(array $menu)
    {
        if ($menu && !empty($menu)) {
            self::$menus[] = $menu;
        }
    }

    public static function sort()
    {
        self::$menus = collect(Arr::sort(self::$menus, 'order'))->values()->all();
    }

    public static function all(): array
    {
        self::sort();
        return collect(self::$menus)->flatMap(function ($menu) {
            return $menu['links'] ?? [];
        })
            ->filter()
            ->toArray();
    }

    public static function registerModule(string $moduleName)
    {
        $menu = Module::find($moduleName)?->get('menu') ?? [];

        if ($menu && isset($menu['links'])) {

            $menu['links'] = collect($menu['links'])
                ->prepend([
                    'type' => 'heading',
                    'text' => $menu['heading'] ?? '',
                ])
                ->map(function ($link) use ($moduleName) {
                    $link['module'] = strtolower($moduleName);
                    return $link;
                })->toArray();

        }
        self::add($menu);
    }
}
