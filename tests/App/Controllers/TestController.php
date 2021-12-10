<?php

namespace Tsekka\Prerender\Tests\App\Controllers;

class TestController extends \Illuminate\Routing\Controller
{
    public function __invoke(string $slug = '')
    {
        return view('prerender::test', ['slug' => $slug]);
    }
}
