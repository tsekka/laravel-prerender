# Prerender & cache your SPA pages for crawlers on Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
<!-- [![Build Status][ico-travis]][link-travis] -->
<!-- [![StyleCI][ico-styleci]][link-styleci] -->

This package intends to make it easier to serve prerendered pages to crawlers for better SEO.

You could make use of it if
- you are running Laravel as backend for your single-page webapp or
- parts of your Laravel app are generated using Javascript.

It could be used as a
- middleware for third-party prerender service (like prerender.io) but
- it can also cache prerendered responses & keep cached responses up to date (for running your local prerender server).


## Installation

Via Composer

``` bash
$ composer require tsekka/prerender
```

## Preparing the database
The package loads migrations

```
php artisan migrate
```

## <a id='publish-config'></a>Publishing the config file
Publishing the config file is optional.
```
php artisan vendor:publish --provider="Tsekka\Prerender\PrerenderServiceProvider" --tag="config"
```

## <a id='middleware'></a> Registering prerender middleware 
Enable prerendering for all routes by adding `PRERENDER_REGISTER_GLOBALLY=true` in your .env file;
or add middleware to specific routes:
  ``` php
  // app/Http/Kernel.php
    protected $routeMiddleware = [
      // ...
      'prerender' => 
          \Tsekka\Prerender\Http\Middleware\PrerenderMiddleware::class,
    ];

  // routes/web.php
  Route::get('/{path?}', 'SPAController')->where('path', '.*')
      ->middleware('prerender');
  ```

## Prerendering by third party service

***By using prerender.io or similar service, you don't have to install node server and headless chrome by yourself.***

The easiest way to start prerendering the pages for crawlers is by the pages can be done using third-party service like prerender.io.

1. Register at prerender.io or at another similar service and follow their instructions.
2. Set prerenderer's url `PRERENDER_URL=https://service.prerender.io` (.env)
3. Prerender.io already caches the pages for speed, so you can turn off local cache `PRERENDER_CACHE_TTL=null` (.env)
4. <a href="#middleware">Register the middleware</a> and you're good to go!

## Running your own prerender service
**To run your own prerender service, you must have Node, headless Chrome and their dependencies installed in your webserver.** 

Prerender.io has open-sourced [node server](https://github.com/prerender/prerender) that you can use to prerender the pages at your server.

Here's how you can make use of it:
1. Install and run prerenderer's node server. 
   * We've icluded working copy in this package's directory for you co clone (`cp -r ./vendor/tsekka/prerender/prerenderer ./prerenderer`) and set up by following a [our quick tutorial for Debian-based Linux distributions](/prerenderer/readme.md).
   * You could also set it up by following [full instructions](https://github.com/prerender/prerender).
2. Set prerenderer's url to url of your prerenderer's service. Eg. if you're running it locally, then add `PRERENDER_URL=http://localhost:3000` to your .env file.
3. Decide if you will keep the prerender server constantly running or if you would rather start the server for the duration of <a href='#caching-schedule'>schedule command</a>.
    - If you will keep the server constantly running, then make sure that the node server will re-start even after webserver is rebooted.
    - If you would rather start the server only for the duration of prerender command, then set `PRERENDER_RUN_SERVER_BY_COMMAND=true` in your .env file.
4. <a href="#middleware">Register the middleware</a>
5. Prerendering the page on-demand can be slow and therefore, by default the pages will be <a href='#caching'>cached</a>.
6. It's recommended that you  <a href='#caching-schedule'>set up the schedule to re-cache</a> the prerendered pages.

### <a id='caching'></a> Caching prerendered responses
Prerendering the page can take up to few seconds or even more. 

Therefore the pages will be cached by default for 1 week. 

You can change cache time-to-live and cache driver by setting .env variables `PRERENDER_CACHE_TTL` and `PRERENDER_CACHE_DRIVER` or by <a href='#publish-config'>publishing</a> and modifying the config file.

*If you're using third-party service like Prerender.io, then the responses are probably already cached, so you can turn off local cache (add `PRERENDER_CACHE_TTL=null` to your .env).*


#### Running cache command
You can run `php artisan prerender:cache` command to cache all pages that are defined in array of <a id='cacheable-urls'>cacheable urls</a>.

By default, the cache command only caches urls that have not been cached yet or whose cache ttl have already expired. You can run cache command with --force option (`php artisan prerender:cache --force`) to re-cache all urls.

#### <a id='caching-schedule'></a> Setting up schedule to cache the pages
Prerendering the page on-demand can be slow and it's therefore recommended to keep fresh copy of pages constantly in cache. You can do this by adding cache command to console kernel:
``` php
    // app/Console/Kernel.php
    protected function schedule(Schedule $schedule)
    {
        // Daily re-cache all urls that's cache-time-to-live is expired
         $schedule->command('prerender:cache')->dailyAt("02:00");
    }
```

#### <a id='cacheable-urls'></a> Providing list of urls to cache
Each time crawler visits the url that matches all requirements for it to be prerendered, the prerendered response will be cached and the url of request will be recorded in database.

So by default, before you actually start using the package, the list will be empty and the urls will be prerendered at the time of request (and therefore the request time could be quite slow, as prerendering takes time). 

If you would like to cache the pages only <a href='#on-demand'>on demand</a> or you would like to keep response time low even on first crawler visit, then you should provide a class & method name that returns array of of urls by publishing config file and modifying it's `cacheable_urls` value.
        
## <a id='run-server-by-command'></a> Starting prerender service on-demand

## <a id='pruning'></a> Pruning old entries
The package logs all crawler visits into database. 

Use `php artisan prerender:prune` command to clear old entries.

You can also schedule the artisan command:
``` php
    // app/Console/Kernel.php
    protected function schedule(Schedule $schedule)
    {
        // Daily prune all crawler visit entries older than 1 month
        $schedule->command('prerender:prune "1 month"')->daily();
    }
```

## Whitelisting urls

Whitelist paths or patterns. You can use asterix syntax.
If a whitelist is supplied, only url's containing a whitelist path will be prerendered.
An empty array means that all URIs will pass this filter.
Note that this is the full request URI, so including starting slash and query parameter string.

```php
// prerender.php:
'whitelist' => [
    '/frontend/*' // only prerender pages starting with '/frontend/'
],
```

## Blacklisting urls

Blacklist paths to exclude. You can use asterix syntax.
If a blacklist is supplied, all url's will be prerendered except ones containing a blacklist path.
By default, a set of asset extentions are included (this is actually only necessary when you dynamically provide assets via routes).
Note that this is the full request URI, so including starting slash and query parameter string.

```php
// config/prerender.php 
'blacklist' => [
    '/api/*' // do not prerender pages starting with '/api/'
    // ...
],
```

<!-- ## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently. -->

<!-- ## Testing

``` bash
$ composer test
``` -->

## Other resources

If you don't know why or when you should prerender your SPA apps, then there are some resources for you to check out:
- <a target="blank" href="https://www.netlify.com/blog/2016/11/22/prerendering-explained/">blog post at netlify.com</a>, 
- <a target="blank" href="https://prerender.io/">prerender.io</a> 
- <a target="blank" href="https://stackoverflow.com/questions/58107986/csr-vs-ssr-vs-pre-render-which-one-should-i-choose">stackoverflow question</a>.

## Contributing

This package is under development. Contributions are appreciated and will be credited.
<!-- Please see [contributing.md](contributing.md) for details and a todolist. -->

## Security

If you discover any security related issues, please email kristjan@pintek.ee instead of using the issue tracker.

## Credits

- Forked from Jeroen Noten's [Laravel-Prerender][link-jeroennoten]


## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/tsekka/prerender.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/tsekka/prerender.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/tsekka/prerender/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/tsekka/prerender
[link-downloads]: https://packagist.org/packages/tsekka/prerender
[link-travis]: https://travis-ci.org/tsekka/prerender
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/tsekka
[link-github]: https://github.com/tsekka/prerender
[link-jeroennoten]: https://github.com/jeroennoten/Laravel-Prerender
[link-contributors]: ../../contributors
