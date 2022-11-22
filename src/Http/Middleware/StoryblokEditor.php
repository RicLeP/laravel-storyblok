<?php

namespace Riclep\Storyblok\Http\Middleware;

use Closure;
use Illuminate\View\View;

class StoryblokEditor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
		$response = $next($request);

		if ($request->ajax() && ($content = $response->getOriginalContent()) instanceof View) {
			return $response->setContent($content->renderSections()['content']);
		}

		return $response;
    }
}
