<?php


namespace Riclep\Storyblok\Traits;


trait SchemaOrg
{
	private $schemaOrg = [];

	protected function initSchemaOrg() {
		//$page = resolve('storyblok')->page;

		// set items on the page
		// $this->schemaOrg();

		$this->schemaOrg();
	}
}