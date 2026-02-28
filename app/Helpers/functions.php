<?php

use App\Models\User;
use App\Models\Option;
use App\Helpers\PlanPerks;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Cache;

if (!function_exists('amount_format')) {
	/**
	 *  format amount
	 * @param string $amount
	 * @param string $icon_type
	 * @return string
	 */
	function amount_format($amount = 0, $icon_type = 'name')
	{
		$currency = (object) get_option('base_currency');
		if ($icon_type == 'name') {
			$currency = $currency->position == 'right' ? $currency->name . ' ' . number_format($amount, 2) : number_format($amount, 2) . ' ' . $currency->name;
		} elseif ($icon_type == 'both') {
			$currency = $currency->icon . number_format($amount, 2) . ' ' . $currency->name;
		} else {
			$currency = $currency->position == 'right' ? number_format($amount, 2) . $currency->icon : $currency->icon . number_format($amount, 2);
		}

		return $currency;
	}
}

if (!function_exists('get_option')) {
	/**
	 * Get Settings From Database
	 * @param $key
	 * @param $locale
	 * @return mixed
	 */
	function get_option($key, bool $withLocale = false): mixed
	{
		$restKeys = null;
		if (Str::contains($key, '.')) {
			$restKeys = Str::after($key, '.');
			$key = Str::before($key, '.');
		}

		$value = Cache::remember(
			$withLocale ? $key . '_' . current_locale() : $key,
			env('CACHE_LIFETIME', 1800),
			fn() => Option::query()
				->where('key', $key)
				->when(
					$withLocale,
					fn($query) => $query->where('lang', current_locale())
				)
				->value('value')
		);

		return data_get($value, $restKeys, '');
	}
}

if (!function_exists('get_option_with_locale')) {
	function get_option_with_locale(string $key, ?string $language = null, bool $includeDefault = false): mixed
	{
		$language ??= current_locale();
		$cacheKey = "{$key}_{$language}";

		return cache_remember($cacheKey, function () use ($key, $language, $includeDefault) {
			$option = Option::query()
				->where('key', $key)
				->where('lang', $language)
				->first();

			if (!$option && $includeDefault) {
				$option = Option::query()->where('key', $key)->first();
				if ($option) {
					$optionClone = $option->replicate();
					$optionClone->lang = current_locale();
					$optionClone->save();
				}
			}

			return $option?->value ?? [];
		});
	}
}

if (!function_exists('cache_remember')) {
	/**
	 * This function will remember the cache
	 * @param string $key
	 * @param callable $callback
	 * @param integer $ttl
	 * @return mixed
	 */
	function cache_remember(string $key, callable $callback, int $ttl = 1800): mixed
	{
		return cache()->remember($key, env('CACHE_LIFETIME', $ttl), $callback);
	}
}

if (!function_exists('current_locale')) {
	/**
	 * Get Current Locale
	 * Return current locale|lang
	 * @return string|null
	 */
	function current_locale()
	{
		return session('locale', app()->getLocale());
	}
}

if (!function_exists('getTranslationFile')) {
	function getTranslationFile()
	{
		$file = base_path('/lang/' . session('locale', 'en') . '.json');
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return [];
	}
}

if (!function_exists('getCookieConsent')) {
	function getCookieConsent()
	{
		if (!request()->isSecure())
			return true;
		$cookieKey = Str::slug(env('APP_NAME', 'laravel'), '_') . '_cookie_consent';
		$cookieHeader = request()->header('Cookie');
		parse_str(str_replace('; ', '&', $cookieHeader), $cookies);
		$cookieValue = isset($cookies[$cookieKey]) ? json_decode($cookies[$cookieKey]) : false;
		return $cookieValue->$cookieKey ?? false;
	}
}

if (!function_exists('activeWorkspaceOwnerId')) {
	/**
	 * Get the owner id of the currently active workspace.
	 *
	 * @return int|null
	 */
	function activeWorkspaceOwnerId()
	{
		/**
		 * @var User
		 */
		$authUser = auth()->user();
		return $authUser?->getActiveWorkspaceOwnerId() ?? $authUser->id;
	}
}

if (!function_exists('activeWorkspaceOwner')) {
	function activeWorkspaceOwner()
	{
		return User::find(activeWorkspaceOwnerId());
	}
}

function getRequestModuleName(): Stringable
{
	$activeModule = null;
	$secondRouteSegment = request()->segment(2) ?? false;
	$module = Module::find(
		str($secondRouteSegment)
			->remove('-')
			->toString()
	);

	if ($module) {
		$activeModule = str($module->getName())->studly()->toString();
	}

	return str($activeModule);
}

function getActiveModules()
{
	return collect(Module::allEnabled())
		->filter(fn($module) => $module->get('accessible', false))
		->map(fn($module) => str($module->getName())->kebab()->toString())
		->values()
		->all();
}

function validateUserPlan(string $planKey, bool $toArray = false, ?int $userId = null): array|bool
{
	return PlanPerks::checkPlan($planKey, $toArray, $userId);
}

function validateWorkspacePlan(string $planKey, bool $toArray = false): array|bool
{
	return PlanPerks::checkPlan($planKey, $toArray, activeWorkspaceOwnerId());
}
