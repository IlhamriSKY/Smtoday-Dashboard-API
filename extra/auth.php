<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$request = Illuminate\Http\Request::capture();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->pushMiddleware(\Vanguard\Http\Middleware\EncryptCookies::class);
$kernel->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);
$kernel->handle($request);

/**
 * Redirect to provided url
 * @param $url
 */
function redirectTo($url)
{
    if (! headers_sent()) {
        header('Location: '.$url, true, 302);
    } else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }
    exit;
}
