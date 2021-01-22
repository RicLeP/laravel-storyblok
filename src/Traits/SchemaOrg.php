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

			if ($page) {
				$this->add($page);
			}
		}

	}

	/**
	 * Returns the JavaScript JSON-LD string
	 *
	 * @return string
	 */
	public function schemaOrgScript() {
		$schemaJson = '';

		foreach ($this->meta()['schema_org'] as $schema) {
			$schemaJson .= $schema->toScript();
		}

		return $schemaJson;
	}

	/**
	 * Adds the schema to the meta of the current page
	 *
	 * @param $page
	 */
	private function add($page) {
		$page->replaceMeta('schema_org', array_merge([$this->schemaOrg()], $this->meta()['schema_org'] ?? []));
	}
}