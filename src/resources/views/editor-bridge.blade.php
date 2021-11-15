
@if (config('storyblok.edit_mode'))
	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	<script src="https://app.storyblok.com/f/storyblok-v2-latest.js"></script>

	<script type="text/javascript">
		const storyblokInstance = new StoryblokBridge({
			accessToken: '{{ config('storyblok.api_preview_key') }}'
		});

		storyblokInstance.on(['change', 'published'], function() {
			window.location.reload()
		});

		@if (config('storyblok.live_preview'))
		storyblokInstance.on('input', (event) => {
			const CancelToken = axios.CancelToken;
			let source = CancelToken.source();

			source && source.cancel('Operation canceled due to new request.');

			// save the new request for cancellation
			source = axios.CancelToken.source();

			axios.post('{{ url()->current() }}', {
				data: event
			}, {
				cancelToken: source.token
			}).then((response) => {
				document.querySelector('{{ config('storyblok.live_element') }}').innerHTML = response.data;

				const storyblokInstance = new StoryblokBridge({
					accessToken: '{{ config('storyblok.api_preview_key') }}'
				});
			});
		});
		@endif
	</script>
@endif