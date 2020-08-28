<?php

namespace Riclep\Storyblok\Http\Controllers;

use Riclep\Storyblok\NullPage;
use Illuminate\Http\Request;
use Riclep\Storyblok\Block;

class LiveContentController
{
    public function index(Request $request) {
		$content = new Block($request->all()['content'], new NullPage());

		$content->flatten();

		return json_encode($content->page()->liveContent);
	}
}
