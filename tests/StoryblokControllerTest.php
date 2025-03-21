<?php

namespace Riclep\Storyblok\Tests;

use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Exceptions\DenylistedUrlException;
use Riclep\Storyblok\Http\Controllers\StoryblokController;
use Riclep\Storyblok\StoryblokFacade;

class StoryblokControllerTest extends TestCase
{
    protected StoryblokController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a fresh controller instance for each test
        $this->controller = new StoryblokController();

        // Create a proper mock Page object
        $mockPage = \Mockery::mock('Riclep\Storyblok\Page');

        // Add expectation for render method on the Page object
        $mockPage->shouldReceive('render')->andReturn(view('welcome'));

        // Setup the facade to return proper objects
        StoryblokFacade::shouldReceive('read')->andReturn($mockPage);

        // Set the extended denylist for testing
        config(['storyblok.denylist' => [
            '/^\.well-known\/.*$/',
            'another-bad-slug',
            '/^admin\/.*$/', // Blocks any URL starting with "admin/"
            '/^user\/\d+\/edit$/', // Blocks URLs like "user/123/edit"
            '/\.(php|sql|exe)$/', // Blocks URLs ending with .php, .sql, or .exe
        ]]);
    }

    /** @test */
    public function it_allows_non_denylisted_slugs()
    {
        // Regular URLs should be allowed
        $this->assertInstanceOf(\Illuminate\View\View::class, $this->controller->show('normal-page'));
        $this->assertInstanceOf(\Illuminate\View\View::class, $this->controller->show('blog/article'));
    }

    /** @test */
    public function it_blocks_well_known_urls()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('another-bad-slug');
    }

    /** @test */
    public function it_blocks_other_well_known_urls()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('.well-known/something-else');
    }

    /** @test */
    public function it_blocks_exact_match_denylisted_slugs()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('.well-known/traffic-advice');
    }

    /** @test */
    public function it_blocks_regex_pattern_for_admin_urls()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('admin/dashboard');
    }

    /** @test */
    public function it_blocks_nested_admin_urls()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('admin/users/permissions');
    }

    /** @test */
    public function it_blocks_user_edit_urls()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('user/123/edit');
    }

    /** @test */
    public function it_allows_other_user_urls()
    {
        // The regex should only match the exact pattern, not similar ones
        $this->assertInstanceOf(\Illuminate\View\View::class, $this->controller->show('user/123'));
        $this->assertInstanceOf(\Illuminate\View\View::class, $this->controller->show('user/123/view'));
    }

    /** @test */
    public function it_blocks_forbidden_file_extensions()
    {
        $this->expectException(DenylistedUrlException::class);
        $this->controller->show('config.php');
    }

    /** @test */
    public function it_allows_urls_with_similar_endings()
    {
        // Should allow URLs that don't exactly match the forbidden extensions
        $this->assertInstanceOf(\Illuminate\View\View::class, $this->controller->show('my-php-article'));
        $this->assertInstanceOf(\Illuminate\View\View::class, $this->controller->show('sql-tutorial'));
    }

    /** @test */
    public function it_flushes_cache_when_destroying()
    {
        // Mock the Cache facade
        Cache::shouldReceive('getStore')->andReturn(new class {
            public function __instanceof($class) {
                return $class === \Illuminate\Cache\TaggableStore::class;
            }
        });

        Cache::shouldReceive('tags')->with('storyblok')->andReturnSelf();
        Cache::shouldReceive('flush')->once();

        $this->controller->destroy();
    }
}
