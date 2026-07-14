<?php

declare(strict_types=1);

namespace Riclep\Storyblok;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\Api\Domain\Value\Dto\Direction;
use Storyblok\Api\Domain\Value\Dto\Pagination;
use Storyblok\Api\Domain\Value\Dto\SortBy;
use Storyblok\Api\Domain\Value\Dto\StoryLevel;
use Storyblok\Api\Domain\Value\Dto\Version;
use Storyblok\Api\Domain\Value\Field\FieldCollection;
use Storyblok\Api\Domain\Value\Filter\FilterCollection;
use Storyblok\Api\Domain\Value\IdCollection;
use Storyblok\Api\Domain\Value\QueryParameter\FirstPublishedAtGt;
use Storyblok\Api\Domain\Value\QueryParameter\FirstPublishedAtLt;
use Storyblok\Api\Domain\Value\QueryParameter\PublishedAtGt;
use Storyblok\Api\Domain\Value\QueryParameter\PublishedAtLt;
use Storyblok\Api\Domain\Value\QueryParameter\UpdatedAtGt;
use Storyblok\Api\Domain\Value\QueryParameter\UpdatedAtLt;
use Storyblok\Api\Domain\Value\Resolver\RelationCollection;
use Storyblok\Api\Domain\Value\Resolver\ResolveLinks;
use Storyblok\Api\Domain\Value\Slug\Slug;
use Storyblok\Api\Domain\Value\Slug\SlugCollection;
use Storyblok\Api\Domain\Value\Tag\TagCollection;
use Storyblok\Api\Request\StoriesRequest;
use Storyblok\Api\Response\StoriesResponse;
use Storyblok\Api\StoriesApi;

abstract class Folder
{
    use HasChildClasses;

    public int $totalStories = 0;
    public ?Collection $stories = null;

    protected bool $startPage = false;
    protected string $language = 'default';
    protected int $page = 1;
    protected int $perPage = 10;
    protected ?SortBy $sortBy = null;
    protected ?Slug $startsWith = null;
    protected ?string $contentType = null;
    protected ?Version $version = null;
    protected ?string $searchTerm = null;
    protected Pagination $pagination;
    protected FilterCollection $filters;
    protected FieldCollection $excludeFields;
    protected TagCollection $withTags;
    protected IdCollection $excludeIds;
    protected RelationCollection $withRelations;
    protected ResolveLinks $resolveLinks;
    protected SlugCollection $excludeSlugs;
    protected ?PublishedAtGt $publishedAtGt = null;
    protected ?PublishedAtLt $publishedAtLt = null;
    protected ?FirstPublishedAtGt $firstPublishedAtGt = null;
    protected ?FirstPublishedAtLt $firstPublishedAtLt = null;
    protected ?UpdatedAtGt $updatedAtGt = null;
    protected ?UpdatedAtLt $updatedAtLt = null;
    protected SlugCollection $bySlugs;
    protected ?StoryLevel $level = null;
    protected ?bool $isStartpage = null;

    protected string $cacheKey = 'folder-';

    public function __construct()
    {
        $this->filters = new FilterCollection();
        $this->excludeFields = new FieldCollection();
        $this->withTags = new TagCollection();
        $this->excludeIds = new IdCollection();
        $this->withRelations = new RelationCollection();
        $this->resolveLinks = new ResolveLinks();
        $this->excludeSlugs = new SlugCollection();
        $this->bySlugs = new SlugCollection();

        $this->pagination = new Pagination(page: $this->page, perPage: $this->perPage);

        $this->setDefaults();
    }

    protected function setDefaults(): void
    {
        // Intentionally empty.
        // Override in child classes to set default slug/content type/sort/etc.
    }

    public function language(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function pagination(Pagination $pagination): static
    {
        $this->pagination = $pagination;
        $this->page = $pagination->page;
        $this->perPage = $pagination->perPage;

        return $this;
    }

    public function page(int $page): static
    {
        $this->page = $page;
        $this->pagination = new Pagination(page: $page, perPage: $this->perPage);

        return $this;
    }

    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;
        $this->pagination = new Pagination(page: $this->page, perPage: $perPage);

        return $this;
    }

    public function sortBy(?SortBy $sortBy): static
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function sort(string $sortBy, Direction $sortOrder): static
    {
        $this->sortBy = new SortBy($sortBy, $sortOrder);

        return $this;
    }

    public function asc(string $sortBy = 'published_at'): static
    {
        return $this->sort($sortBy, Direction::Asc);
    }

    public function desc(string $sortBy = 'published_at'): static
    {
        return $this->sort($sortBy, Direction::Desc);
    }

    public function startsWith(?Slug $startsWith): static
    {
        $this->startsWith = $startsWith;

        return $this;
    }

    public function slug(string $slug): static
    {
        return $this->startsWith(new Slug($slug));
    }

    public function contentType(?string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function startPage(?bool $startPage = true): static
    {
        $this->startPage = $startPage ?? false;
        $this->isStartpage = $this->startPage;

        return $this;
    }

    public function isStartpage(?bool $isStartpage): static
    {
        $this->isStartpage = $isStartpage;
        $this->startPage = $isStartpage ?? false;

        return $this;
    }

    public function filters(FilterCollection $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function excludeFields(FieldCollection $excludeFields): static
    {
        $this->excludeFields = $excludeFields;

        return $this;
    }

    public function withTags(TagCollection $withTags): static
    {
        $this->withTags = $withTags;

        return $this;
    }

    public function excludeIds(IdCollection $excludeIds): static
    {
        $this->excludeIds = $excludeIds;

        return $this;
    }

    public function withRelations(RelationCollection $withRelations): static
    {
        $this->withRelations = $withRelations;

        return $this;
    }

    public function version(?Version $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function searchTerm(?string $searchTerm): static
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    public function resolveLinks(ResolveLinks $resolveLinks): static
    {
        $this->resolveLinks = $resolveLinks;

        return $this;
    }

    public function excludeSlugs(SlugCollection $excludeSlugs): static
    {
        $this->excludeSlugs = $excludeSlugs;

        return $this;
    }

    public function publishedAtGt(?PublishedAtGt $value): static
    {
        $this->publishedAtGt = $value;

        return $this;
    }

    public function publishedAtLt(?PublishedAtLt $value): static
    {
        $this->publishedAtLt = $value;

        return $this;
    }

    public function firstPublishedAtGt(?FirstPublishedAtGt $value): static
    {
        $this->firstPublishedAtGt = $value;

        return $this;
    }

    public function firstPublishedAtLt(?FirstPublishedAtLt $value): static
    {
        $this->firstPublishedAtLt = $value;

        return $this;
    }

    public function updatedAtGt(?UpdatedAtGt $value): static
    {
        $this->updatedAtGt = $value;

        return $this;
    }

    public function updatedAtLt(?UpdatedAtLt $value): static
    {
        $this->updatedAtLt = $value;

        return $this;
    }

    public function bySlugs(SlugCollection $bySlugs): static
    {
        $this->bySlugs = $bySlugs;

        return $this;
    }

    public function level(?StoryLevel $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function makeRequest(): StoriesRequest
    {
        return new StoriesRequest(
            language: $this->language,
            pagination: $this->pagination,
            sortBy: $this->sortBy,
            filters: $this->filters,
            excludeFields: $this->excludeFields,
            withTags: $this->withTags,
            excludeIds: $this->excludeIds,
            withRelations: $this->withRelations,
            version: $this->version,
            searchTerm: $this->searchTerm,
            resolveLinks: $this->resolveLinks,
            excludeSlugs: $this->excludeSlugs,
            startsWith: $this->startsWith,
            publishedAtGt: $this->publishedAtGt,
            publishedAtLt: $this->publishedAtLt,
            firstPublishedAtGt: $this->firstPublishedAtGt,
            firstPublishedAtLt: $this->firstPublishedAtLt,
            updatedAtGt: $this->updatedAtGt,
            updatedAtLt: $this->updatedAtLt,
            bySlugs: $this->bySlugs,
            level: $this->level,
            isStartpage: $this->isStartpage,
            contentType: $this->contentType,
        );
    }

    protected function get()
    {
        if (request()->has('_storyblok') || !config('storyblok.cache')) {
            $response = $this->doRequest();

            $this->totalStories = $response->total->value;

            return collect($response->stories);
        }

        $apiHash = md5(config('storyblok.api_public_key') ?? config('storyblok.api_preview_key'));
        $requestHash = hash('xxh128', serialize($this->makeRequest()));

        $response = Cache::store(config('storyblok.sb_cache_driver'))->remember(
            $this->cacheKey . ($this->startsWith?->value ?? '') . '-' . $apiHash . '-' . $requestHash,
            config('storyblok.cache_duration') * 60,
            function () {
                $response = $this->doRequest();

                return [
                    'total' => $response->total->value,
                    'stories' => $response->stories,
                ];
            }
        );

        $this->totalStories = $response['total'];

        return collect($response['stories']);
    }

    protected function doRequest(): StoriesResponse
    {
        $storyblokClient = resolve('Storyblok\Api\StoryblokClient');
        $storiesApi = new StoriesApi($storyblokClient, config('storyblok.draft') ? 'draft' : 'published');

        return $storiesApi->all($this->makeRequest());
    }

    public function paginate($page = null, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

        if ($this->stories === null || $this->page !== $page) {
            $this->page($page)->read();
        }

        return new LengthAwarePaginator(
            $this->stories,
            $this->totalStories,
            $this->perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    public function read(): static
    {
        $stories = $this->get()->transform(function ($story) {
            $blockClass = $this->getChildClassName('Page', $story['content']['component']);

            return new $blockClass($story);
        });

        $this->stories = $stories;

        return $this;
    }

    public function count(): int
    {
        return $this->stories?->count() ?? 0;
    }

    public function toArray(): array
    {
        return $this->stories?->toArray() ?? [];
    }
}
