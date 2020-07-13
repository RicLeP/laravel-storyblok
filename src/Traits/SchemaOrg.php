<?php


namespace Riclep\Storyblok\Traits;


use Riclep\Storyblok\Page;

trait SchemaOrg
{
	/**
	 * Automatically called to add a schema to the Page
	 */
	protected function initSchemaOrg() {
		if (method_exists($this, 'schemaOrg')) {
			if ($this instanceof Page) {
				$page = $this;
			} else {
				$page = $this->page();
			}

			$this->add($page);
		}

	}

	public function schemaOrgScript() {
		$schemaJson = '';

		foreach ($this->meta()['schema_org'] as $schema) {
			$schemaJson .= $schema->toScript();
		}

		return $schemaJson;
	}

	private function add($page) {
		$page->replaceMeta('schema_org', array_merge([$this->schemaOrg()], $this->meta()['schema_org'] ?? []));
	}
}