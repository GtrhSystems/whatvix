<?php

return [
    [
        'type' => 'heading',
        'text' => 'Core',
    ],
    [
        'icon' => 'streamline:dashboard-circle',
        'text' => 'Dashboard',
        'url' => route('user.dashboard'),
    ],
    [
        'icon' => 'bx:grid-alt',
        'text' => 'Workspaces',
        'url' => route('user.workspaces.index'),
    ],
    [
        'icon' => 'bx:user',
        'text' => 'Team Members',
        'url' => route('user.teams.index'),
    ],
    [
        'icon' => 'mdi:robot',
        'text' => 'AI Tools',
        'url' => route('user.ai-tools.index')
    ],
    [
        'icon' => 'fe:codepen',
        'text' => 'AI Generated History',
        'url' => route('user.ai-generated-history.index')
    ],
    [
        'icon' => 'bx:image',
        'text' => 'Assets',
        'url' => route('user.assets.index')
    ],
    [
        "text" => "Flows",
        "icon" => "bx:layer",
        "url" => route("user.whatsapp.flows.index"),
    ]
];
