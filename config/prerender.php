<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Prerender
    |--------------------------------------------------------------------------
    |
    | Set this field to false to fully disable the prerender service. You
    | would probably override this in a local configuration, to disable
    | prerender on your local machine.
    |
    */

    'enable' => env('PRERENDER_ENABLE', true),


    /*
    |--------------------------------------------------------------------------
    | Register prerender middleware globally
    |--------------------------------------------------------------------------
    |
    | Enable this if you want to register the middleware globally. Otherwise,
    | you can register the prerender middleware in app/Http/Kernel.php and
    | assign it to specific routes. If this is set to true, then check out
    | the `whitelist` option at the bottom of the config file.
    |
    */

    'register_globally' => env('PRERENDER_REGISTER_GLOBALLY', false),


    /*
    |--------------------------------------------------------------------------
    | Prerender URL
    |--------------------------------------------------------------------------
    |
    | This is the prerender URL to the service that prerenders the pages.
    | Prerender's hosted service on prerender.io is used
    | (https://service.prerender.io). But you can also set it to your
    | own server address or set to null, if you'd like to start the service for
    | Examples: 'https://service.prerender.io', 'http://localhost:3000', null
    |
    */

    'prerender_url' => env('PRERENDER_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | Return soft HTTP status codes
    |--------------------------------------------------------------------------
    |
    | By default Prerender returns soft HTTP codes. If you would like it to
    | return the real ones in case of Redirection (3xx) or status Not Found (404),
    | set this parameter to false.
    | Keep in mind that returning real HTTP codes requires appropriate meta tags
    | to be set. For more details, see github.com/prerender/prerender#httpheaders
    |
    */

    'prerender_soft_http_codes' => env('PRERENDER_SOFT_HTTP_STATUS_CODES', true),

    /*
    |--------------------------------------------------------------------------
    | Debug Prerender
    |--------------------------------------------------------------------------
    |
    |
    */

    'throw_exceptions' => env('PRERENDER_THROW_EXCEPTIONS')
        ?? env('APP_DEBUG')
        ?? false,

    /*
    |--------------------------------------------------------------------------
    | Prerender Token
    |--------------------------------------------------------------------------
    |
    | If you use prerender.io as service, you need to set your prerender.io
    | token here. It will be sent via the X-Prerender-Token header. If
    | you do not provide a token, the header will not be added.
    |
    */

    'prerender_token' => env('PRERENDER_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Cache Time To Live
    |--------------------------------------------------------------------------
    |
    | The time in seconds that the prerendered page should be cached for.
    | If you want to disable caching, set this to NULL.
    |
    | If you're using prerender.io service, then you don't probably need
    | to cache the results because prerender.io is already doing this.
    |
    */

    'cache' => env('PRERENDER_CACHE_TTL', 7 * 86400), // 86400 s = 24h

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The cache driver to use. By default, the cache is stored in the filesystem.
    |
    */

    'cache_driver' => env('PRERENDER_CACHE_DRIVER')
        ?? 'file',

    /*
    |--------------------------------------------------------------------------
    | Cacheable urls class & method
    |--------------------------------------------------------------------------
    |
    | Class and it's method that returns array of cacheable urls. The default
    | provided here returns urls that have already been prerendered before.
    | However, you can provide your own array of urls.
    |
    */

    'cacheable_urls' => [
        Tsekka\Prerender\Actions\GetCacheableUrls::class,
        'handle'
    ], // handle method must return array

    /**
     * Set this to true if the `php artisan prerender:cache` command should
     */

    /*
    |--------------------------------------------------------------------------
    | Cacheable urls class & method
    |--------------------------------------------------------------------------
    |
    | Set this to true if the `php artisan prerender:cache` command should
    | start the prerendering server. Should be set to false if you use
    | third-party prerendering service or you keep the node server
    | constantly running.
    |
    */

    'run_server_by_command' => env('PRERENDER_RUN_SERVER_BY_COMMAND', true),


    /*
    |--------------------------------------------------------------------------
    | Crawler User Agents
    |--------------------------------------------------------------------------
    |
    | Requests from crawlers that do not support _escaped_fragment_ will
    | nevertheless be served with prerendered pages. You can customize
    | the list of crawlers here.
    |
    */

    'crawler_user_agents' => [
        'googlebot',
        'yahoo',
        'bingbot',
        'yandex',
        'baiduspider',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'developers.google.com/+/web/snippet',
        'slackbot',
        'semrushbot',
        'msnbot',
        'ahrefsbot',
        'dotbot',
        'aspiegelbot'
    ],

    /*
    |--------------------------------------------------------------------------
    | Prerender Blacklist
    |--------------------------------------------------------------------------
    |
    | Blacklist paths to exclude. You can use asterix syntax, or regular
    | expressions (without start and end markers). If a blacklist is supplied,
    | all url's will be prerendered except ones containing a blacklist path.
    | By default, a set of asset extentions are included (this is actually only
    | necessary when you dynamically provide assets via routes). Note that this
    | is the full request URI, so including starting slash and query parameter
    | string.
    |
    */

    'blacklist' => [
        '/api/*',
        '*.js',
        '*.css',
        '*.xml',
        '*.less',
        '*.png',
        '*.jpg',
        '*.jpeg',
        '*.svg',
        '*.gif',
        '*.pdf',
        '*.doc',
        '*.txt',
        '*.ico',
        '*.rss',
        '*.zip',
        '*.mp3',
        '*.rar',
        '*.exe',
        '*.wmv',
        '*.doc',
        '*.avi',
        '*.ppt',
        '*.mpg',
        '*.mpeg',
        '*.tif',
        '*.wav',
        '*.mov',
        '*.psd',
        '*.ai',
        '*.xls',
        '*.mp4',
        '*.m4a',
        '*.swf',
        '*.dat',
        '*.dmg',
        '*.iso',
        '*.flv',
        '*.m4v',
        '*.torrent',
        '*.eot',
        '*.ttf',
        '*.otf',
        '*.woff',
        '*.woff2'
    ],

    /*
    |--------------------------------------------------------------------------
    | Prerender Whitelist
    |--------------------------------------------------------------------------
    |
    | Whitelist paths or patterns. You can use asterix syntax, or regular
    | expressions (without start and end markers). If a whitelist is supplied,
    | only url's containing a whitelist path will be prerendered. An empty
    | array means that all URIs will pass this filter. Note that this is the
    | full request URI, so including starting slash and query parameter string.
    | This is useful if you have enabled the `register_globally` option.
    |
    */

    'whitelist' => [],

];
