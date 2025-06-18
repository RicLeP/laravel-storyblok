
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

                axios.post(@js(request()->getRequestUri()), {
                    sbLiveData: event
                }, {
                    cancelToken: source.token
                }).then((response) => {
                    document.querySelector('{{ config('storyblok.live_element') }}').innerHTML = response.data;

                    @if (config('storyblok.live_links'))
                        document.dispatchEvent(new Event('DOMContentLoaded'));
                    @endif

                    const storyblokInstance = new StoryblokBridge({
                        accessToken: '{{ config('storyblok.api_preview_key') }}'
                    });
                });
            });
		@endif


        @if (config('storyblok.live_links'))
            function appendQueryParamsToPath(path) {
                const link = new URL(path, window.location.origin);
                if (link.origin !== window.location.origin) {
                    return path;
                }

                const currentUrl = window.location.href;
                const queryParams = currentUrl.split('?')[1];

                if (queryParams) {
                    path += (path.includes('?') ? '&' : '?') + queryParams;
                }

                return path;
            }

            function updateAllLinks() {
                const urlParams = new URLSearchParams(window.location.search);
                let condition = false;

                for (const [key] of urlParams) {
                    if (key.startsWith('_storyblok')) {
                        condition = true;
                        break;
                    }
                }

                if (condition) {
                    const links = document.querySelectorAll('a[href]');

                    links.forEach(link => {
                        const originalHref = link.getAttribute('href');
                        const updatedHref = appendQueryParamsToPath(originalHref);
                        link.setAttribute('href', updatedHref);
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', updateAllLinks);
        @endif
	</script>
@endif
