<?php

namespace App\Helpers;

final class ModuleMenu
{

    public static array $menus = [];


    public static function add(array $menuItems = [], string $moduleName = 'core'): array
    {
        self::$menus = array_merge(self::$menus, self::mapMenuItems($menuItems, $moduleName));
        return self::$menus;
    }

    public static function get(array $moduleNames = []): array
    {
        foreach ($moduleNames as $moduleName) {
            self::getModuleMenus(strtolower($moduleName));
        }
        return self::$menus;
    }

    public static function getModuleMenus(string $moduleName)
    {
        if ($moduleName === 'core') {
            $coreMenus = require __DIR__ . '/menu.php';
            self::add($coreMenus, $moduleName);
            return;
        }

        if (!file_exists(module_path($moduleName, 'menu.php'))) {
            throw new \Exception("The module menu file does not exist: $moduleName");
        }

        $menuFilePath = module_path($moduleName, 'menu.php');
        $menuItems = require $menuFilePath;

        if (!is_array($menuItems)) {
            throw new \Exception("The module menu file is not an array: $menuFilePath");
        }

        self::add($menuItems, $moduleName);
    }

    private static function mapMenuItems(array $menuItems = [], string $moduleName = 'core'): array
    {
        return collect($menuItems)->map(function ($menuItem) use ($moduleName) {
            if (!key_exists('module', $menuItem)) {
                $menuItem['module'] = $moduleName;
            }
            return $menuItem;
        })->toArray();
    }
}
