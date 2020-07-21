@if (config('storyblok.edit_mode'))
	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	<script type="text/javascript" src="//app.storyblok.com/f/storyblok-latest.js"></script>
	<script type="text/javascript">
		storyblok.init({
			accessToken: '{{ config('storyblok.api_preview_key') }}'
		});

		storyblok.pingEditor(function() {
			if (storyblok.inEditor) {
				storyblok.enterEditmode
			}
		});

		storyblok.on('change', function() {
			window.location.reload(true);
		});

		storyblok.on('published', function() {
			axios.post('{{ route('clear-storyblok-cache') }}').then((response) => {
				console.log(response);
			});
		});
	</script>
@endif