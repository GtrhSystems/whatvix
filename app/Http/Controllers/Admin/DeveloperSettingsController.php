<?php

namespace App\Http\Controllers\Admin;

use DateTimeZone;
use Inertia\Inertia;
use App\Traits\Dotenv;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeveloperSettingsController extends Controller
{
    use Dotenv, Uploader;

    public function __construct()
    {
        $this->middleware('permission:developer-settings');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     */
    public function show($id)
    {

        PageHeader::set(Str::headline($id));

        if ($id == 'app-settings') {
            $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
            $languages = get_option('languages');
            $appName = env('APP_NAME');
            $appDebug = env('APP_DEBUG');
            $timeZone = env('TIME_ZONE', 'UTC');
            $default_lang = env('DEFAULT_LANG', 'en');
            $QUEUE_CONNECTION = env('QUEUE_CONNECTION', 'sync');

            return Inertia::render('Admin/Developer/App', compact('id', 'tzlist', 'languages', 'appName', 'appDebug', 'timeZone', 'default_lang', 'QUEUE_CONNECTION'));
        } elseif ($id == 'mail-settings') {
            $mailDriver = env('MAIL_DRIVER_TYPE') == 'MAIL_DRIVER' ? env('MAIL_DRIVER') : env('MAIL_MAILER');
            $QUEUE_MAIL = env('QUEUE_MAIL');
            $MAIL_DRIVER_TYPE = env('MAIL_DRIVER_TYPE');
            $MAIL_HOST = env('MAIL_HOST');
            $MAIL_PORT = env('MAIL_PORT');
            $MAIL_USERNAME = env('MAIL_USERNAME');
            $MAIL_PASSWORD = env('MAIL_PASSWORD');
            $MAIL_ENCRYPTION = env('MAIL_ENCRYPTION');
            $MAIL_FROM_ADDRESS = env('MAIL_FROM_ADDRESS');
            $MAIL_FROM_NAME = env('MAIL_FROM_NAME');
            $MAIL_TO = env('MAIL_TO');

            return Inertia::render(
                'Admin/Developer/Smtp',
                compact(
                    'id',
                    'mailDriver',
                    'QUEUE_MAIL',
                    'MAIL_DRIVER_TYPE',
                    'MAIL_HOST',
                    'MAIL_PORT',
                    'MAIL_USERNAME',
                    'MAIL_PASSWORD',
                    'MAIL_ENCRYPTION',
                    'MAIL_FROM_ADDRESS',
                    'MAIL_FROM_NAME',
                    'MAIL_TO',
                )
            );
        } elseif ($id == 'newsletter-settings') {

            $NEWSLETTER_API_KEY = env('NEWSLETTER_API_KEY');
            $NEWSLETTER_LIST_ID = env('NEWSLETTER_LIST_ID');

            return Inertia::render(
                'Admin/Developer/Newsletter',
                compact(
                    'id',
                    'NEWSLETTER_API_KEY',
                    'NEWSLETTER_LIST_ID',
                )
            );
        } elseif ($id == 'storage-settings') {
            $FILESYSTEM_DISK = env('FILESYSTEM_DISK');
            $WAS_ACCESS_KEY_ID = env('WAS_ACCESS_KEY_ID');
            $SECRET_ACCESS_KEY = env('SECRET_ACCESS_KEY');
            $WAS_DEFAULT_REGION = env('WAS_DEFAULT_REGION');
            $WAS_BUCKET = env('WAS_BUCKET');
            $WAS_ENDPOINT = env('WAS_ENDPOINT');

            return Inertia::render(
                'Admin/Developer/Storage',
                compact(
                    'id',
                    'FILESYSTEM_DISK',
                    'WAS_ACCESS_KEY_ID',
                    'SECRET_ACCESS_KEY',
                    'WAS_DEFAULT_REGION',
                    'WAS_BUCKET',
                    'WAS_ENDPOINT',
                )
            );
        } elseif ($id == 'twilio-settings') {
            $TWILIO_ACCOUNT_SID = env('TWILIO_ACCOUNT_SID');
            $TWILIO_AUTH_TOKEN = env('TWILIO_AUTH_TOKEN');
            $TWILIO_NUMBER = env('TWILIO_NUMBER');
            return Inertia::render(
                'Admin/Developer/Twilio',
                compact(
                    'id',
                    'TWILIO_ACCOUNT_SID',
                    'TWILIO_AUTH_TOKEN',
                    'TWILIO_NUMBER',
                )
            );
        } elseif ($id == 'features-settings') {
            $EMAIL_VERIFICATION = env('EMAIL_VERIFICATION');
            $PHONE_VERIFICATION = env('PHONE_VERIFICATION');
            return Inertia::render(
                'Admin/Developer/Features',
                compact(
                    'id',
                    'EMAIL_VERIFICATION',
                    'PHONE_VERIFICATION',
                )
            );
        } elseif ($id == 'social-login-settings') {

            $GOOGLE_CLIENT_ID = config('services.google.client_id');
            $GOOGLE_CLIENT_SECRET = config('services.google.client_secret');
            $GOOGLE_REDIRECT_URL = route('oauth.callback', 'google');

            $FACEBOOK_CLIENT_ID = config('services.facebook.client_id');
            $FACEBOOK_CLIENT_SECRET = config('services.facebook.client_secret');
            $FACEBOOK_REDIRECT_URL = route('oauth.callback', 'facebook');

            return Inertia::render(
                'Admin/Developer/SocialLogin',
                compact(
                    'id',
                    'GOOGLE_CLIENT_ID',
                    'GOOGLE_CLIENT_SECRET',
                    'GOOGLE_REDIRECT_URL',
                    'FACEBOOK_CLIENT_ID',
                    'FACEBOOK_CLIENT_SECRET',
                    'FACEBOOK_REDIRECT_URL',
                )
            );
        } elseif ($id == 'cookie-settings') {

            $COOKIE_CONSENT = env('COOKIE_CONSENT');
            $cookieData = json_decode(file_get_contents(database_path('json/cookie_data.json')), true);
            return Inertia::render(
                'Admin/Developer/Cookie',
                compact(
                    'id',
                    'COOKIE_CONSENT',
                    'cookieData',
                )
            );
        } elseif ($id == 'wa-server-settings') {

            $waServerEnv = \Dotenv\Dotenv::createImmutable(base_path('whatsapp-server'))->load();

            $PORT = data_get($waServerEnv, 'PORT');
            $NODE_ENV = data_get($waServerEnv, 'NODE_ENV');

            $ENABLE_WEBHOOK = data_get($waServerEnv, 'ENABLE_WEBHOOK');
            $ENABLE_WEBSOCKET = data_get($waServerEnv, 'ENABLE_WEBSOCKET');
            $BOT_NAME = data_get($waServerEnv, 'BOT_NAME');
            $DATABASE_URL = data_get($waServerEnv, 'DATABASE_URL');
            $LOG_LEVEL = data_get($waServerEnv, 'LOG_LEVEL');
            $RECONNECT_INTERVAL = data_get($waServerEnv, 'RECONNECT_INTERVAL');
            $MAX_RECONNECT_RETRIES = data_get($waServerEnv, 'MAX_RECONNECT_RETRIES');
            $SSE_MAX_QR_GENERATION = data_get($waServerEnv, 'SSE_MAX_QR_GENERATION');
            $SESSION_CONFIG_ID = data_get($waServerEnv, 'SESSION_CONFIG_ID');
            $API_KEY = data_get($waServerEnv, 'API_KEY');

            return Inertia::render(
                'Admin/Developer/WaServer',
                compact(
                    'id',
                    'PORT',
                    'NODE_ENV',

                    'ENABLE_WEBHOOK',
                    'ENABLE_WEBSOCKET',
                    'BOT_NAME',
                    'DATABASE_URL',
                    'LOG_LEVEL',
                    'RECONNECT_INTERVAL',
                    'MAX_RECONNECT_RETRIES',
                    'SSE_MAX_QR_GENERATION',
                    'SESSION_CONFIG_ID',
                    'API_KEY'
                )
            );
        } elseif ($id == 'ai-api-settings') {

            $aiProviders = collect(config('prism.providers', []))
                ->filter(function ($provider) {
                    return array_key_exists('api_key', $provider);
                })
                ->mapWithKeys(function ($provider, $key) {
                    return [
                        strtoupper($key) . '_API_KEY' => $provider['api_key'],
                    ];
                })->toArray();

            return Inertia::render(
                'Admin/Developer/AiApi',
                [
                    'id' => $id,
                    'aiProviders' => $aiProviders,
                ]
            );
        } elseif ($id == 'place-api-settings') {
            $GOOGLE_PLACE_API_KEY = env('GOOGLE_PLACE_API_KEY');

            return Inertia::render(
                'Admin/Developer/PlaceApi',
                compact(
                    'id',
                    'GOOGLE_PLACE_API_KEY'
                )
            );
        } elseif ($id == 'broadcast-settings') {
            $data = [
                'id' => $id,
                'BROADCAST_DRIVER' => env('BROADCAST_DRIVER'),
                'PUSHER_APP_ID' => env('PUSHER_APP_ID'),
                'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
                'PUSHER_APP_SECRET' => env('PUSHER_APP_SECRET'),
                'PUSHER_SCHEME' => env('PUSHER_SCHEME'),
                'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
            ];
            return Inertia::render('Admin/Developer/BroadcastSetting', $data);
        }
        abort(404);
    }


    public function update(Request $request, $id)
    {
        if ($id == 'app-settings') {
            $this->editEnv('APP_NAME', Str::slug($request->APP_NAME));
            $this->editEnv('APP_DEBUG', filter_var($request->APP_DEBUG, FILTER_VALIDATE_BOOLEAN), true);
            $this->editEnv('TIME_ZONE', $request->TIME_ZONE);
            $this->editEnv('DEFAULT_LANG', $request->DEFAULT_LANG ?? 'en');
            $this->editEnv('QUEUE_CONNECTION', $request->QUEUE_CONNECTION ?? 'en');
        } elseif ($id == 'storage-settings') {
            $this->editEnv('FILESYSTEM_DISK', $request->FILESYSTEM_DISK);
            $this->editEnv('WAS_ACCESS_KEY_ID', $request->WAS_ACCESS_KEY_ID);
            $this->editEnv('SECRET_ACCESS_KEY', $request->SECRET_ACCESS_KEY);
            $this->editEnv('WAS_DEFAULT_REGION', $request->WAS_DEFAULT_REGION);
            $this->editEnv('WAS_BUCKET', $request->WAS_BUCKET);
            $this->editEnv('WAS_ENDPOINT', $request->WAS_ENDPOINT);
        } elseif ($id == 'newsletter-settings') {

            $this->editEnv('NEWSLETTER_API_KEY', $request->NEWSLETTER_API_KEY);
            $this->editEnv('NEWSLETTER_LIST_ID', $request->NEWSLETTER_LIST_ID);
        } elseif ($id == 'mail-settings') {

            $this->editEnv('QUEUE_MAIL', filter_var($request->QUEUE_MAIL, FILTER_VALIDATE_BOOLEAN), true);
            $this->editEnv('MAIL_DRIVER_TYPE', $request->MAIL_DRIVER_TYPE);
            $this->editEnv($request->MAIL_DRIVER_TYPE, $request->MAIL_DRIVER);
            $this->editEnv('MAIL_HOST', $request->MAIL_HOST);
            $this->editEnv('MAIL_PORT', $request->MAIL_PORT);
            $this->editEnv('MAIL_USERNAME', $request->MAIL_USERNAME);
            $this->editEnv('MAIL_PASSWORD', $request->MAIL_PASSWORD);
            $this->editEnv('MAIL_ENCRYPTION', $request->MAIL_ENCRYPTION);
            $this->editEnv('MAIL_FROM_ADDRESS', $request->MAIL_FROM_ADDRESS);
            $this->editEnv('MAIL_FROM_NAME', $request->MAIL_FROM_NAME);
            $this->editEnv('MAIL_TO', $request->MAIL_TO);
        } elseif ($id == 'stripe-settings') {
            $this->editEnv('STRIPE_API_KEY', $request->STRIPE_API_KEY);
            $this->editEnv('STRIPE_PUBLIC_API_KEY', $request->STRIPE_PUBLIC_API_KEY);
            $this->editEnv('STRIPE_CURRENCY', $request->STRIPE_CURRENCY);
        } elseif ($id == 'twilio-settings') {
            $this->editEnv('PHONE_VERIFICATION', $request->PHONE_VERIFICATION, true);
            $this->editEnv('TWILIO_ACCOUNT_SID', $request->TWILIO_ACCOUNT_SID);
            $this->editEnv('TWILIO_AUTH_TOKEN', $request->TWILIO_AUTH_TOKEN);
            $this->editEnv('TWILIO_NUMBER', $request->TWILIO_NUMBER);
        } elseif ($id == 'features-settings') {
            $this->editEnv('EMAIL_VERIFICATION', $request->EMAIL_VERIFICATION, true);
            $this->editEnv('PHONE_VERIFICATION', $request->PHONE_VERIFICATION, true);
        } elseif ($id == 'social-login-settings') {
            $this->editEnv('GOOGLE_CLIENT_ID', $request->GOOGLE_CLIENT_ID);
            $this->editEnv('GOOGLE_CLIENT_SECRET', $request->GOOGLE_CLIENT_SECRET);
            $this->editEnv('FACEBOOK_CLIENT_ID', $request->FACEBOOK_CLIENT_ID);
            $this->editEnv('FACEBOOK_CLIENT_SECRET', $request->FACEBOOK_CLIENT_SECRET);
        } elseif ($id == 'cookie-settings') {
            $this->editEnv('COOKIE_CONSENT', $request->COOKIE_CONSENT, true);
            file_put_contents(database_path('json/cookie_data.json'), json_encode($request->cookieData));
        } elseif ($id == 'wa-server-settings') {
            $this->editWaServerEnv('PORT', $request->get('PORT'));
            $this->editWaServerEnv('NODE_ENV', $request->get('NODE_ENV'));
            $this->editWaServerEnv('BASE_URL', url('/'));
            $this->editWaServerEnv('ENABLE_WEBHOOK', $request->get('ENABLE_WEBHOOK'), true);
            $this->editWaServerEnv('ENABLE_WEBSOCKET', $request->get('ENABLE_WEBSOCKET'), true);
            $this->editWaServerEnv('BOT_NAME', $request->get('BOT_NAME'));
            $this->editWaServerEnv('DATABASE_URL', 'mysql://' . env('DB_USERNAME') . ':' . env('DB_PASSWORD') . '@' . env('DB_HOST') . ':' . env('DB_PORT') . '/' . env('DB_DATABASE'));
            $this->editWaServerEnv('LOG_LEVEL', 'info');
            $this->editWaServerEnv('RECONNECT_INTERVAL', $request->get('RECONNECT_INTERVAL'));
            $this->editWaServerEnv('SITE_KEY', env('SITE_KEY'));
            
            $this->editWaServerEnv('MAX_RECONNECT_RETRIES', $request->get('MAX_RECONNECT_RETRIES'));
            $this->editWaServerEnv('SSE_MAX_QR_GENERATION', $request->get('SSE_MAX_QR_GENERATION'));
            $this->editWaServerEnv('SESSION_CONFIG_ID', $request->get('SESSION_CONFIG_ID'));
            $this->editWaServerEnv('API_KEY', $request->get('API_KEY'));
            $this->editEnv('WHATSAPP_WEB_API_BASE_URL', 'http://localhost:' . $request->get('PORT'));

        } elseif ($id == 'ai-api-settings') {
            foreach ($request->all() as $key => $value) {
                $this->editEnv($key, $value);
            }
        } elseif ($id == 'place-api-settings') {
            $this->editEnv('GOOGLE_PLACE_API_KEY', $request->GOOGLE_PLACE_API_KEY);
        } elseif ($id == 'broadcast-settings') {
            $this->updateEnv([
                'BROADCAST_DRIVER' => $request->get('BROADCAST_DRIVER'),
                'PUSHER_APP_ID' => $request->get('PUSHER_APP_ID'),
                'PUSHER_APP_KEY' => $request->get('PUSHER_APP_KEY'),
                'PUSHER_APP_SECRET' => $request->get('PUSHER_APP_SECRET'),
                'PUSHER_APP_CLUSTER' => $request->get('PUSHER_APP_CLUSTER'),
                'PUSHER_SCHEME' => $request->get('PUSHER_SCHEME')
            ]);
        }

        return back()->with('success', __('Settings updated successfully.'));
    }
}
