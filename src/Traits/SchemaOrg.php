<?php


namespace Riclep\Storyblok\Traits;


use Riclep\Storyblok\Page;

trait SchemaOrg
{
	/**
	 * Automatically called to add a schema to the Page
	 */
	protected function initSchemaOrg(): void
	{
		if (method_exists($this, 'schemaOrg')) {
			if ($this instanceof Page) {
				$page = $this;
			} else {
				$page = $this->page();
			}

			if ($page && count($this->_componentPath) <= config('storyblok.schema_org_depth')) {
				$this->addschemaOrg($page);
			}
		}

	}

	/**
	 * Returns the JavaScript JSON-LD string
	 *
	 * @return string
	 */
	public function schemaOrgScript(): string
	{
		$schemaJson = '';

        if (array_key_exists('schema_org', $this->meta())) {
            foreach ($this->meta()['schema_org'] as $schema) {
                $schemaJson .= $schema->toScript();
            }
        }

        return $schemaJson;

	}

	/**
	 * Adds the schema to the meta of the current page
	 *
	 * @param $page
	 */
	protected function addschemaOrg($page): void
	{
		$currentSchemaOrg = $page->meta('schema_org');

        if ($schema = $this->schemaOrg()) {
            $currentSchemaOrg[] = $schema;
        }

		$page->replaceMeta('schema_org', $currentSchemaOrg ?? []);
	}
}
