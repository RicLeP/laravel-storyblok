<?php


namespace Testcomponents\Storyblok\Blocks;

use Riclep\Storyblok\Block;

class Children extends Block
{
	public function byUuid($block) {

		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Child content",
				"slug": "child-content",
				"full_slug": "pages/default-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "component": "child-content",
				  "title": "Child Content Title"
				},
				"position": -20,
				"tag_list": [ ],
				"is_startpage": false,
				"parent_id": 107348,
				"group_id": "d5ea8520-1296-40b7-8360-894461fdc5b6",
				"alternates": [ ],
				"release_id": null,
				"lang": "default"
			  }
			}';

		$this->storyblokResponse = json_decode($json, true)['story'];

		return $this;
	}
}