<picture>
	@foreach($transformations as $key => $transformation)
		@if ($default !== $key)
			<source srcset="{{ $transformation['src']  }}" type="{{ $transformation['src']->mime() }}" media="{{ $transformation['media'] }}">
		@endif
	@endforeach

	<img src="{{ $imgSrc }}" alt="{{ $alt }}" @foreach ($attributes as $attribute => $value) {{$attribute}}="{{$value}}" @endforeach>
</picture>