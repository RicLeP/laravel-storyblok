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
	 * Initialises the Embed object.
	 */
	protected function init() {
		$this->_embed = Embed::create($this->content);
	}

	/**
	 * Returns the embed code looking for a view in storyblok.embeds or the package.
	 * If neither are found the raw embed code is returned.
	 *
	 * @return string
	 */
	public function html() {
		if (method_exists($this, 'embedView')) {
			$method = 'embedView';
		} else {
			$method = 'baseEmbedView';
		}

		if ($this->{$method}()) {
			return (string) view($this->{$method}(), [
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
	public function embed() {
		return $this->_embed;
	}

	/**
	 * Returns a path to a view to use for embedding this type of media.
	 * If the view can not be found it should return false.
	 *
	 * @return false|string
	 */
	protected function baseEmbedView() {
		if (view()->exists(config('storyblok.view_path') . 'embeds.' . strtolower($this->_embed->providerName))) {
			return config('storyblok.view_path') . 'embeds.' . strtolower($this->_embed->providerName);
		}

		if (view()->exists('laravel-storyblok::embeds.' . strtolower($this->_embed->providerName))) {
			return 'laravel-storyblok::embeds.' . strtolower($this->_embed->providerName);
		}

		return false;
	}


	/**
	 * Returns the embed code
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->html();
	}
}