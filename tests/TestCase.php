<?php

namespace Riclep\Storyblok\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Riclep\Storyblok\Storyblok;

class TestCase extends Orchestra
{

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('storyblok.component_class_namespace', 'Riclep\Storyblok\Tests\Fixtures\\');
		$app['config']->set('storyblok.view_path');

		$app['config']->set('seo.default_title', 'Default title from config');

		$viewPath = str_replace('..', '', __DIR__ . DIRECTORY_SEPARATOR .'..' . 'Fixtures' . DIRECTORY_SEPARATOR. 'views');

		$app['config']->set('view.paths', array_merge(config('view.paths'), [$viewPath]));

		app()->singleton('storyblok', function () {
			return new Storyblok;
		});

		app()->get('storyblok')->content = new \StdClass;
	}

	protected function mockPage($type = 'Default') {
		$storyblokMock = $this->getMockBuilder(Storyblok::class, ['requestStory'])->setMethods(['requestStory'])->getMock();
		$storyblokMock->method('requestStory')->willReturn($this->{'mock' . $type . 'Response'}());

		return $storyblokMock;
	}

	protected function mockDefaultResponse() {
		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Default page",
				"slug": "default-page",
				"full_slug": "pages/default-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "seo": {
					"_uid": "35594ee1-b83b-4bad-80ac-30f3e2dd1de8",
					"title": "SEO title",
					"plugin": "seo_metatags",
					"og_image": "",
					"og_title": "",
					"description": "SEO description",
					"twitter_image": "",
					"twitter_title": "",
					"og_description": "",
					"twitter_description": ""
				  },
				  "component": "page",
				  "image": "//a.storyblok.com/f/44162/1500x500/68b522b06d/1500x500.jpeg",
				  "title": "Block Title",
				  "subtitle": "Block Title",
				  "body": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce interdum scelerisque aliquet. Nam gravida dapibus tincidunt. Cras tempor sollicitudin lectus id accumsan. Morbi ante nulla, elementum quis imperdiet vel, pretium a magna. Cras posuere nunc a risus eleifend hendrerit. In porta nisl non odio suscipit consectetur. Fusce egestas tellus vel neque pulvinar faucibus. Phasellus dignissim nunc id nibh vehicula congue.",
				  "schedule": "2020-10-10 05:05:05",
				  "description": "Description"
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

		return json_decode($json, true)['story'];
	}

	protected function mockDateResponse() {
		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Date page",
				"slug": "date-page",
				"full_slug": "pages/date-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "component": "date",
				  "schedule": "2020-10-10 05:05:05"
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

		return json_decode($json, true)['story'];
	}

	protected function mockSpecificResponse() {
		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Specific page",
				"slug": "specific-page",
				"full_slug": "pages/specific-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "component": "specific",
				  "image": "//a.storyblok.com/f/44162/1500x500/68b522b06d/1500x500.jpeg",
				  "title": "My second title",
				  "body": "\"Lorem ipsum dolor sit amet, consectetur adipiscing elit\". Fusce interdum scelerisque aliquet. Nam gravida dapibus tincidunt. Cras tempor sollicitudin lectus id accumsan. Morbi ante nulla, elementum quis imperdiet vel, pretium a magna. Cras posuere nunc a risus eleifend hendrerit. In porta nisl non odio suscipit consectetur. Fusce egestas tellus vel neque pulvinar faucibus. Phasellus dignissim nunc id nibh vehicula congue.",
				  "schedule": "2020-10-10 05:05:05",
				  "description": "Description"
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

		return json_decode($json, true)['story'];
	}

	protected function mockTrait1Response() {
		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Default page",
				"slug": "default-page",
				"full_slug": "pages/default-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "component": "trait1",
				  "typography": "\"Lorem ipsum dolor sit amet, consectetur adipiscing elit\". 3 x 4.",
				  "schedule": "2020-10-10 05:05:05",
				  "description": "Description"
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

		return json_decode($json, true)['story'];
	}

	protected function mockTrait2Response() {
		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Default page",
				"slug": "default-page",
				"full_slug": "pages/default-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "component": "trait2",
				  "typography": "\"Lorem ipsum dolor sit amet, consectetur adipiscing elit\". 3 x 4.",
				  "schedule": "2020-10-10 05:05:05",
				  "description": "Description"
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

		return json_decode($json, true)['story'];
	}

	protected function mockHasChildResponse() {
		$json = '{"story": {
				"id": 107350,
				"uuid": "ac0d2ed0-e323-43ca-ae59-5cd7d38683cb",
				"name": "Has children",
				"slug": "has-child",
				"full_slug": "pages/default-page",
				"created_at": "2018-04-24T11:57:29.302Z",
				"published_at": "2018-12-07T01:31:36.134Z",
				"first_published_at": "2018-08-07T09:40:13.000Z",
				"content": {
				  "component": "has-child",
				  "description": "Description",
				  "children": [
					"9d40e5c1-cc16-4e10-b03d-ef11e9da085c"
				  ]
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

		return json_decode($json, true)['story'];
	}

	protected function mockComplexResponse() {
		$json = '{
		  "story": {
			"name": "Home",
			"created_at": "2019-08-16T10:37:29.861Z",
			"published_at": "2019-11-13T13:36:46.033Z",
			"alternates": [],
			"id": 1997614,
			"uuid": "73756982-7610-4d87-8c0d-c8194bbc5c4e",
			"content": {
			  "seo": {
				"_uid": "35594ee1-b83b-4bad-80ac-30f3e2dd1de8",
				"title": "Complex response",
				"plugin": "seo_metatags",
				"og_image": "",
				"og_title": "",
				"description": "",
				"twitter_image": "",
				"twitter_title": "",
				"og_description": "",
				"twitter_description": ""
			  },
			  "_uid": "48268ac7-e849-4cf3-b3f3-078415f128ed",
			  "hero": [
				{
				  "_uid": "4cf7fd78-9fc7-42e6-aec3-72efbf70c49c",
				  "gallery": [],
				  "headline": "The headline",
				  "component": "video_hero",
				  "video_url": {
					"id": "73756982-7610-4d87-8c0d-c8194bbc5c4e",
					"url": "",
					"linktype": "story",
					"fieldtype": "multilink",
					"cached_url": "home"
				  }
				}
			  ],
			  "layout_intro": [
				{
				  "_uid": "42a35f5a-ca0c-45b8-a155-2baf7ecbd7fb",
				  "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
				  "links": [
					{
					  "url": {
						"id": "e64d866f-ce6b-40c1-915a-ec70f48f8f44",
						"url": "",
						"linktype": "story",
						"fieldtype": "multilink",
						"cached_url": "services/something"
					  },
					  "_uid": "fe48ef7e-7fdc-43e1-b7bc-2f00b5372cc8",
					  "label": "A link label",
					  "component": "text_link"
					},
					{
					  "url": {
						"id": "2d0c1925-b97a-4666-85f1-ebc3e6192a36",
						"url": "",
						"linktype": "story",
						"fieldtype": "multilink",
						"cached_url": "services/another"
					  },
					  "_uid": "3224934c-db3b-451c-a047-78f108b8ac3f",
					  "label": "Another service",
					  "component": "text_link"
					},
					{
					  "url": {
						"id": "",
						"url": "",
						"linktype": "story",
						"fieldtype": "multilink",
						"cached_url": "services/third"
					  },
					  "_uid": "5ea0add3-804e-47c7-9195-82f24ea427f9",
					  "label": "Third service",
					  "component": "text_link"
					}
				  ],
				  "button": [
					{
					  "url": {
						"id": "2f05310d-c69b-4cf9-b7cc-732649b90fae",
						"url": "",
						"linktype": "story",
						"fieldtype": "multilink",
						"cached_url": "services/"
					  },
					  "_uid": "e9bc5c43-77aa-486d-b932-c6488cc7886e",
					  "label": "Our Services",
					  "component": "link_button"
					}
				  ],
				  "component": "text_with_links"
				},
				{
				  "_uid": "6cb3c87f-9c8b-4153-811b-ee5d3de03e0b",
				  "logo": "",
				  "component": "award",
				  "description": "Test"
				},
				{
				  "_uid": "90120827-79d9-4ccb-96e8-62355cb5f776",
				  "text": "",
				  "links": [],
				  "button": [],
				  "component": "text_with_links"
				}
			  ],
			  "praise": [
				{
				  "_uid": "6c675deb-3321-4615-96a0-4d5ba1e8bc81",
				  "component": "testimonials",
				  "testimonials": [
					{
					  "_uid": "36c86963-5176-4bee-8b71-07fcdd166f7c",
					  "quote": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
					  "speaker": "Person’s Name, Company",
					  "component": "testimonial"
					}
				  ]
				},
				{
				  "_uid": "98667a8f-a611-4bcc-8cc1-58337b5aec0e",
				  "logo": "",
				  "component": "award",
				  "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit"
				}
			  ],
			  "component": "homepage"
			},
			"slug": "home",
			"full_slug": "home",
			"sort_by_date": null,
			"position": -479465,
			"tag_list": [],
			"is_startpage": false,
			"parent_id": 0,
			"meta_data": null,
			"group_id": "9bcbd7c0-de5a-4961-8ced-f6ca9da5e64c",
			"first_published_at": "2019-08-16T07:47:52.000Z",
			"release_id": null,
			"lang": "default",
			"path": null
		  }
		}';

		return json_decode($json, true)['story'];
	}
}

