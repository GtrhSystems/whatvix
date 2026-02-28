import Echo from "laravel-echo";
import Pusher from "pusher-js";

import { usePage } from "@inertiajs/vue3";

export default function (appKey = null) {

    console.log("Initializing websocket...");

    if (!appKey) {
        appKey = usePage().props.pusher_app_key
    }

    if (!appKey) {
        console.error("Cannot initialize websocket. Because 'pusher_app_key' not found")
    }

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: appKey,
        cluster: 'mt1',
        encrypted: true,
    });

    if (window.Echo) {
        console.log("Websocket initialized");
    }

    return window.Echo
}