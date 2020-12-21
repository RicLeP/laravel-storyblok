
@if (config('storyblok.edit_mode'))
	{{ $story->block()->flatten() }}

	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	<script type="text/javascript" src="//app.storyblok.com/f/storyblok-latest.js"></script>

	<script type="text/javascript">
		document.onreadystatechange = function () {
			if (document.readyState === 'complete') {
				Vue.set(app.__vue__, 'laravelStoryblokLive', {!! json_encode($story->liveContent) !!});
			}
		}

		storyblok.init({
			accessToken: '{{ config('storyblok.api_preview_key') }}'
		});

		storyblok.pingEditor(function() {
			if (storyblok.inEditor) {
				storyblok.enterEditmode
			}
		});

		let CancelToken = axios.CancelToken;
		let inputCall = CancelToken.source();

		storyblok.on('input', (payload) => {
			axios.post('/api/laravel-storyblok/live-content', {
				'content': payload.story.content,
				cancelToken: inputCall.token
			}).then((response) => {
				Vue.set(app.__vue__, 'laravelStoryblokLive', response.data);
			}).catch(function(thrown) {
				if (axios.isCancel(thrown)) {
					console.log('First request canceled', thrown.message);
				} else {
					// handle error
				}
			});
		});

		storyblok.on('change', function() {
			window.location.reload(true);
		});
	</script>
@endif