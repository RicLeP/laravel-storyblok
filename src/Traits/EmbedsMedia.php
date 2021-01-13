<?php


namespace Riclep\Storyblok\Traits;

use Embed\Embed;

trait EmbedsMedia
{
	/**
	 * The Embed\Embed object.
	 *
	 * @var Embed\Embed
	 */
	private $_embed;

	/**
	 * The field to use as the source of the embedded item
	 *
	 * @var string
	 */
	private $_embedField;

	/**
	 * Initialises the Embed object.
	 */
	protected function initEmbedsMedia() {
		$this->_embed = Embed::create($this->content()[$this->_embedField]);
	}

	/**
	 * Returns the embed code looking for a view in storyblok.embeds or the package.
	 * If neither are found the raw embed code is returned.
	 *
	 * @return string
	 */
	public function embed() {
		if ($this->hasView()) {
			return view()->first([
				config('storyblok.view_path') . 'embeds.' . strtolower($this->_embed->providerName),
				'laravel-storyblok::embeds.' . strtolower($this->_embed->providerName),
			], [
				'embed' => $this->_embed,
			]);
		}

		return $this->rawEmbed();
	}

	/**
	 * Returns the raw embed code.
	 *
	 * @return string
	 */
	public function rawEmbed() {
		return $this->_embed->code;
	}

	/**
	 * Returns the Embed\Embed object.
	 *
	 * @return Embed\Embed
	 */
	public function embedder() {
		return $this->_embed;
	}

	/**
	 * Checks if a view has been created for this service.
	 *
	 * @return bool
	 */
	private function hasView() {
		return view()->exists(config('storyblok.view_path') . 'embeds.' . strtolower($this->_embed->providerName)) || view()->exists('laravel-storyblok::embeds.' . strtolower($this->_embed->providerName));
	}
}