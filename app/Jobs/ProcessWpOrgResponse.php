<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessWpOrgResponse implements ShouldQueue
{
    use Queueable;

    private $responseContent;
    /**
     * Create a new job instance.
     */
    public function __construct($responseContent)
    {
        $this->responseContent = $responseContent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
            $responseAsJson = json_decode($this->responseContent);

            // has themes prop
        if (isset($responseAsJson->themes)) {
            $mappedThemes = array_map(function ($themeInfo) {
                return [
                    'id' => \Str::uuid()->toString(),
                    'name' => $themeInfo->name,
                    'slug' => $themeInfo->slug,
                    'current_version' => $themeInfo->version,
                    'metadata' => json_encode($themeInfo)
                ];
            }, $responseAsJson->themes);
            \DB::table('themes')->upsert($mappedThemes, ['slug'], ['name','current_version','metadata']);
        }

             // has themes prop
        if (isset($responseAsJson->plugins)) {
            $mappedPlugins = array_map(function ($pluginInfo) {
                    return [
                        'id' => \Str::uuid()->toString(),
                        'name' => $pluginInfo->name,
                        'slug' => $pluginInfo->slug,
                        'current_version' => $pluginInfo->version,
                        'metadata' => json_encode($pluginInfo)
                    ];
            }, $responseAsJson->plugins);
            \DB::table('plugins')->upsert($mappedPlugins, ['slug'], ['name','current_version','metadata']);
        }
    }
}
