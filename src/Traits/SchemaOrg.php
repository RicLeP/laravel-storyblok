<?php


namespace Riclep\Storyblok\Traits;


use Riclep\Storyblok\Page;

trait SchemaOrg
{
	/**
	 * Automatically called to add a schema to the Page
	 */
	protected function initSchemaOrg() {
		if ($this instanceof Page) {
			$page = $this;
		} else {
			$page = $this->page();
		}

		$page->_meta['schema_org'][] = $this->schemaOrg();
	}

	public function schemaOrgScript() {
		$schemaJson = '';

		foreach ($this->_meta['schema_org'] as $schema) {
			$schemaJson .= $schema->toScript();
		}

		return $schemaJson;
	}
}