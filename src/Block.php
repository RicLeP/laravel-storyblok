<?php


namespace Riclep\Storyblok;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Riclep\Storyblok\Exceptions\UnableToRenderException;
use Riclep\Storyblok\Fields\Asset;
use Riclep\Storyblok\Fields\Image;
use Riclep\Storyblok\Fields\MultiAsset;
use Riclep\Storyblok\Fields\RichText;
use Riclep\Storyblok\Fields\Table;
use Riclep\Storyblok\Traits\HasChildClasses;
use Riclep\Storyblok\Traits\HasMeta;
use Storyblok\ApiException;


class Block implements \IteratorAggregate, \JsonSerializable
{
	use HasChildClasses;
	use HasMeta;

	/**
	 * @var bool resolve UUID relations automatically
	 */
	public bool $_autoResolveRelations = false;

	/**
	 * @var array list of field names containing relations to resolve
	 */
	public array $_resolveRelations = [];

	/**
	 * @var bool Remove unresolved relations such as those that 404
	 */
	public bool $_filterRelations = true;

	/**
	 * @var array the path of nested components
	 */
	public array $_componentPath = [];

	/**
	 * @var array the path of nested components
	 */
	protected array $_casts = [];

	/**
	 * @var Collection all the fields for the Block
	 */
	protected Collection $_fields;

	/**
	 * @var Page|Block reference to the parent Block or Page
	 */
	protected mixed $_parent;

	/**
	 * @var array default values for fields
	 */
	protected array $_defaults = [];

	/**
	 * Takes the Block’s content and a reference to the parent
	 * @param $content
	 * @param $parent
	 */
	public function __construct($content, $parent = null)
	{
		$this->_parent = $parent;
		$this->preprocess($content);

		if ($parent) {
			$this->_componentPath = array_merge($parent->_componentPath, [Str::lower($this->meta()['component'])]);
		}

		$this->processFields();

		if (method_exists($this, 'fieldsReady')) {
			$this->fieldsReady();
		}

		// run automatic traits - methods matching initTraitClassName()
		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'init' . class_basename($trait))) {
				$this->{$method}();
			}
		}
	}

	/**
	 * Returns the every field of content
	 *
	 * @return Collection
	 */
	public function content(): Collection
	{
		$fields = $this->_fields;

		foreach ($fields as $key => $field) {
			if ($field === null) {
				$fields[$key] = $this->_defaults[$key] ?? null;
			}
		}

		return $fields;
	}

	/**
	 * Checks if this Block’s fields contain the specified key
	 *
	 * @param $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return $this->_fields->has($key);
	}

	/**
	 * Checks if a ‘Blocks’ fieldtype contains a specific block component
	 * Pass the $field that contains the blocks and the component type to search for
	 *
	 * @param $field
	 * @param $component
	 * @return boolean
	 */
	public function hasChildBlock($field, $component): bool
	{
		return $this->content()[$field]->contains(function($item) use ($component) {
			return $item->meta('component') === $component;
		});
	}

	/**
	 * Returns the parent Block
	 *
	 * @return Block
	 */
	public function parent(): Block|Page|null
	{
		return $this->_parent;
	}

	/**
	 * Returns the page this Block belongs to
	 *
	 * @return Block
	 */
	public function page(): Block|Page|null
	{
		if ($this->parent() instanceof Page) {
			return $this->parent();
		}

		return $this->parent()->page();
	}

	/**
	 * Returns the first matching view, passing it the Block and optional data
	 *
	 * @param array $with
	 * @return View
	 * @throws UnableToRenderException
	 */
	public function render(array $with = []): View
	{
		return $this->renderUsing($this->views(), $with);
	}

	/**
	 * Pass an array of views rendering the first match, passing it the Block and optional data
	 *
	 * @param array|string $views
	 * @param array $with
	 * @return View
	 * @throws UnableToRenderException
	 */
	public function renderUsing(array|string $views, array $with = []): View
	{
		try {
			return view()->first(Arr::wrap($views), array_merge(['block' => $this], $with));
		} catch (\Exception $exception) {
			throw new UnableToRenderException('None of the views in the given array exist.', $this);
		}
	}

	/**
	 * Returns an array of views for the Block based on page’s content type and
	 * block’s $componentPath. First are page specific views starting with the
	 * page’s content type followed by those using the block’s component path
	 *
	 * Example:
	 *
	 * $componentPath = ['page', 'product', 'heroes', 'hero'];
	 *
	 * [
	 *	"storyblok.pages.product.blocks.heroes.hero"
	 *	"storyblok.pages.product.blocks.hero"
	 *	"storyblok.blocks.heroes.hero"
	 *	"storyblok.blocks.product.hero"
	 *	"storyblok.blocks.hero"
	 * ]
	 *
	 * It is recommended to start with the most generic view and create more
	 * specific ones as and when required
	 *
	 * @return array
	 */
	public function views(): array
	{
		$componentPath = $this->_componentPath;
		array_pop($componentPath);

		$views = array_filter(array_map(function($path) {
			if ($path !== 'page') {
				return config('storyblok.view_path') . 'blocks.' . $path . '.' . $this->component();
			}

			return null;
		}, $componentPath));

		$views = array_reverse($views);

		$views[] = config('storyblok.view_path') . 'blocks.' . $this->component();

		$themeViews = $views;

		$themeViews = array_filter(array_map(function($view) {
			$theme = $this->page()?->block()->component();

			if (!strpos($view, $theme)) {
				return str_replace('blocks.', 'pages.' . $theme . '.blocks.', $view);
			}

			return null;
		}, $themeViews));

		return array_merge($themeViews, $views);
	}

	/**
	 * Returns a component X generations previous
	 *
	 * @param $generation int
	 * @return mixed
	 */
	public function ancestorComponentName(int $generation): mixed
	{
		return $this->_componentPath[count($this->_componentPath) - ($generation + 1)];
	}

	/**
	 * Checks if the current component is a child of another
	 *
	 * @param $parent string
	 * @return bool
	 */
	public function isChildOf(string $parent): bool
	{
		return $this->_componentPath[count($this->_componentPath) - 2] === $parent;
	}

	/**
	 * Checks if the component is an ancestor of another
	 *
	 * @param $parent string
	 * @return bool
	 */
	public function isAncestorOf(string $parent): bool
	{
		return in_array($parent, $this->parent()->_componentPath, true);
	}

	/**
	 * Returns the current Block’s component name from Storyblok
	 *
	 * @return string
	 */
	public function component(): string
	{
		return $this->_meta['component'];
	}


	/**
	 * Returns the HTML comment required for making this Block clickable in
	 * Storyblok’s visual editor. Don’t forget to set comments to true in
	 * your Vue.js app configuration.
	 *
     * @param $attribute bool return a data-* attribute or comment for editor link
	 * @return string
	 */
	public function editorLink($attribute = false): string
	{
		if (array_key_exists('_editable', $this->_meta) && config('storyblok.edit_mode')) {
            if ($attribute) {
                return 'data-blok-c=\'' . str_replace(['<!--#storyblok#', '-->'], '', $this->_meta['_editable']) . '\'';
            }

			return $this->_meta['_editable'];
		}

		return '';
	}


	/**
	 * Magic accessor to pull content from the _fields collection. Works just like
	 * Laravel’s model accessors. Matches public methods with the follow naming
	 * convention getSomeFieldAttribute() - called via $block->some_field
	 *
	 * @param $key
	 * @return null|string
	 */
	public function __get($key) {
		$accessor = 'get' . Str::studly($key) . 'Attribute';

		if (method_exists($this, $accessor)) {
			return $this->$accessor();
		}

		if (array_key_exists($key, $this->_defaults) && $this->has($key) && !$this->_fields[$key]) {
			return $this->_defaults[$key];
		}

		if ($this->has($key)) {
			return $this->_fields[$key];
		}

		return null;
	}

	/**
	 * Casts the Block as a string - json serializes the $_fields Collection
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->jsonSerialize();
	}

	/**
	 * Loops over every field to get the ball rolling
	 */
	private function processFields(): void
	{
		$this->_fields->transform(fn($field, $key) => $this->getFieldType($field, $key));
	}

	/**
	 * Converts fields into Field Classes based on various properties of their content
	 *
	 * @param $field
	 * @param $key
	 * @return array|Collection|mixed|Asset|Image|MultiAsset|RichText|Table
	 * @throws \Storyblok\ApiException
	 */
	private function getFieldType($field, $key): mixed
	{
		return (new FieldFactory())->build($this, $field, $key);
	}

	/**
	 * Storyblok returns fields and other meta content at the same level so
	 * let’s do a little tidying up first
	 *
	 * @param $content
	 */
	private function preprocess($content): void
	{
		// run pre-process traits - methods matching preprocessTraitClassName()
		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'preprocess' . class_basename($trait))) {
				$content = $this->{$method}($content);
			}
		}

		$fields = ['_editable', '_uid', 'component'];

		$this->_fields = collect(array_diff_key($content, array_flip($fields)));

		// remove non-content keys
		$this->_meta = array_intersect_key($content, array_flip($fields));
	}

	/**
	 * Casting Block to JSON
	 *
	 * @return Collection|mixed
	 */
	public function jsonSerialize(): mixed
	{
		return $this->content();
	}

	/**
	 * Let’s up loop over the fields in Blade without needing to
	 * delve deep into the content collection
	 *
	 * @return \Traversable
	 */
	public function getIterator(): \Traversable {
		return $this->_fields;
	}

	/**
	 * @param RequestStory $requestStory
	 * @param $relation
	 * @param $className
	 * @return mixed|null
	 */
	public function getRelation(RequestStory $requestStory, $relation, $className = null): mixed
	{
		try {
			$response = $requestStory->get($relation);

			if (!$className) {
				$class = $this->getChildClassName('Block', $response['content']['component']);
			} else {
				$class = $className;
			}

			$relationClass = new $class($response['content'], $this);

			$relationClass->addMeta([
				'name' => $response['name'],
				'published_at' => $response['published_at'],
				'full_slug' => $response['full_slug'],
				'page_uuid' => $relation,
			]);

			return $relationClass;
		} catch (ApiException $e) {
			return null;
		}
	}

	/**
	 * Returns an inverse relationship to the current Block. For example if Service has a Multi-Option field
	 * relationship to People, on People you can request all the Services it has been related to
	 *
	 * @param string $foreignRelationshipField
	 * @param string $foreignRelationshipType
	 * @param array|null $components
	 * @param array|null $options
     * @param array|null $resolveRelations
	 * @return array
	 */
    public function inverseRelation(string $foreignRelationshipField, string $foreignRelationshipType = 'multi', ?array $components = null, ?array $options = null, ?array $resolveRelations = null): array
    {
        $storyblokClient = resolve('Storyblok\Client');

        $type = 'any_in_array';

        if ($foreignRelationshipType === 'single') {
            $type = 'in';
        }

        $query = [
            'filter_query' => [
                $foreignRelationshipField => [$type => $this->meta('page_uuid') ?? $this->page()->uuid()]
            ],
        ];

        if ($components) {
            $query = array_merge_recursive($query, [
                'filter_query' => [
                    'component' => ['in' => $components],
                ]
            ]);
        }

        if ($options) {
            $query = array_merge_recursive($query, $options);
        }

        if ($resolveRelations) {
            $storyblokClient->resolveRelations(implode(',', $resolveRelations));
        }

        if (request()->has('_storyblok') || !config('storyblok.cache')) {
            $storyblokClient->getStories($query);

            $response = [
                'headers' => $storyblokClient->getHeaders(),
                'stories' => $storyblokClient->getBody()['stories'],
            ];
        } else {
            $apiHash = md5(config('storyblok.api_public_key') ?? config('storyblok.api_preview_key')); // unique id for multitenancy applications

            $uniqueTag = md5(serialize($query));

            $response = Cache::store(config('storyblok.sb_cache_driver'))->remember($foreignRelationshipField . '-' . $foreignRelationshipType . '-' . $apiHash . '-' . $uniqueTag, config('storyblok.cache_duration') * 60, function () use ($storyblokClient, $query) {
                $storyblokClient->getStories($query);

                return [
                    'headers' => $storyblokClient->getHeaders(),
                    'stories' => $storyblokClient->getBody()['stories'],
                ];
            });
        }

        return [
            'headers' => $response['headers'],
            'stories' => collect($response['stories'])->transform(function ($story) {
                $blockClass = $this->getChildClassName('Page', $story['content']['component']);

                return new $blockClass($story);
            }),
        ];
    }

	/**
	 * Returns the casts on this Block
	 *
	 * @return array
	 */
	public function getCasts(): array
	{
		return $this->_casts;
	}
}
