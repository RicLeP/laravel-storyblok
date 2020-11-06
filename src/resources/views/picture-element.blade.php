<picture>
	@foreach($transformations as $key => $transformation)
		@if ($default !== $key)
			<source srcset="{{ $transformation['src']  }}" type="{{ $transformation['src']->type() }}" media="{{ $transformation['media'] }}">
		@endif
	@endforeach

	<img src="{{ $defaultSrc }}" alt="{{ $alt }}" @if (array_key_exists('class', $attributes)) class="{{ $attributes['class'] }}" @endif>
</picture>