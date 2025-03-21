<?php

namespace Riclep\Storyblok\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\Solution;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;

class DenylistedUrlException extends Exception implements ProvidesSolution
{
    /**
     * The denylisted URL that triggered the exception
     *
     * @var string
     */
    protected $url;

    /**
     * Create a new DenylistedUrlException instance.
     *
     * @param string $url
     * @param string $message
     */
    public function __construct(string $url, string $message = "")
    {
        $this->url = $url;
        $message = $message ?: "The URL '{$url}' is denylisted and cannot be accessed";

        parent::__construct($message, 404);
    }

    /**
     * Get the solution for this exception.
     *
     * @return \Spatie\Ignition\Contracts\Solution
     */
    public function getSolution(): Solution
    {
        return BaseSolution::create('URL is denylisted')
            ->setSolutionDescription('This URL has been denylisted by the application. If you believe this is an error, check the denylist configuration.')
            ->setDocumentationLinks([
                'Laravel Storyblok docs' => 'https://ls.sirric.co.uk/docs/',
            ]);
    }

    /**
     * Get the denylisted URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
