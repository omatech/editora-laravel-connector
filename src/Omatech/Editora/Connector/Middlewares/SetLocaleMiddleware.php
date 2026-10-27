<?php

namespace Omatech\Editora\Connector\Middlewares;

use Closure;
use App;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // The locale reaches the extractor SQL: only configured languages pass
        $language = $request->route('language');
        abort_unless(in_array($language, config('editora.availableLanguages'), true), 404);
        App::setLocale($language);
        return $next($request);
    }
}
