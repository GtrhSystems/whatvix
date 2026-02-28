<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Inertia\Inertia;
use Nwidart\Modules\Facades\Module;
class CronjobController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:cron-job');
    }

    public function __invoke()
    {
        PageHeader::set(__('Cron Job'));
        $jobs = [
            [
                'title' => __('Execute Schedule Messages'),
                'url' => 'curl -s ' . url('/cron/execute-schedule'),
                'time' => __('Once Everyday')
            ],
            [
                'title' => __('Delete activity log'),
                'url' => 'curl -s ' . url('/cron/delete-activity-log'),
                'time' => __('Once Everyday')
            ],
            [
                'title' => __('Notify to customer before expire the subscription'),
                'url' => 'curl -s ' . url('/cron/notify-to-user/7'),
                'time' => __('Once Everyday')
            ]
        ];

        $crons = collect(array_values(Module::toCollection()->toArray()))->map(function($items){
            return $items['crons'] ?? '';
        })->filter(fn($item) => !empty($item));

       
        foreach ($crons as $key => $module) {
            foreach ($module as $key => $cron) {
                $data['title'] = $cron['title'] ?? '';
                $data['time'] =  $cron['time'] ?? '';
                $data['url'] ='curl -s ' . url($cron['url'] ?? '');
                array_push($jobs, $data);
            }
        }
      


        return Inertia::render('Admin/Cron/Index', compact('jobs'));
    }
}
