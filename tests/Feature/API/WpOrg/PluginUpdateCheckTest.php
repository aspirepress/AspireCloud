<?php

use Illuminate\Support\Facades\Http;

it('hits the wp.org api', function () {

    $pluginsData = [
        'plugins' => [
            'akismet/akismet.php' => [
                'Name' => 'Akismet Anti-spam: Spam Protection',
                'PluginURI' => 'https://akismet.com/',
                'Version' => '5.0.3',
                'Description' => 'Used by millions, Akismet is quite possibly the best way in the world to **protect your blog from spam</strong>. Akismet Anti-spam keeps your site protected even while you sleep. To get started: activate the Akismet plugin and then go to your Akismet Settings page to set up your API key.',
                'Author' => 'Automattic - Anti-spam Team',
                'AuthorURI' => 'https://automattic.com/wordpress-plugins/',
                'TextDomain' => 'akismet',
                'DomainPath' => '',
                'Network' => false,
                'RequiresWP' => '5.8',
                'RequiresPHP' => '5.0.20',
                'UpdateURI' => '',
                'RequiresPlugins' => '',
                'Title' => 'Akismet Anti-spam: Spam Protection',
                'AuthorName' => 'Automattic - Anti-spam Team',
            ],
            'hello.php' => [
                'Name' => 'Hello Dolly',
                'PluginURI' => 'http://wordpress.org/plugins/hello-dolly/',
                'Version' => '1.7.2',
                'Description' => 'This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from Hello, Dolly</cite> in the upper right of your admin screen on every page.',
                'Author' => 'Matt Mullenweg',
                'AuthorURI' => 'http://ma.tt/',
                'TextDomain' => '',
                'DomainPath' => '',
                'Network' => false,
                'RequiresWP' => '',
                'RequiresPHP' => '',
                'UpdateURI' => '',
                'RequiresPlugins' => '',
                'Title' => 'Hello Dolly',
                'AuthorName' => 'Matt Mullenweg',
            ],
        ],
        'active' => ['akismet/akismet.php', 'hello.php'],
    ];

    $response = Http::withHeaders([
        'User-Agent' => 'WordPress/6.6.2; http://wp.test/',
    ])
    ->timeout(3)
    ->asForm()
    ->post('https://api.wordpress.org/plugins/update-check/1.1/', [
        'plugins' => json_encode($pluginsData),
        'translations' => json_encode([]),
        'locale' => json_encode([]),
        'all' => 'true',
    ]);

    //    dd($response->json());
});
