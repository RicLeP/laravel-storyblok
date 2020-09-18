// for app.js


data() {
	return {
		liveContent: {}
	}
},

mounted() {
	this.liveContent = window.liveContent;

	storyblok.on('input', (payload) => {
		/*console.log(payload);
		console.log(payload.story);
		console.log(payload.story.content);*/
		console.log('hiiiiiiiii');

		axios.post('/live-content', {
			'content': payload.story.content
		}).then((response) => {
			console.log(response.data);



			this.liveContent = response.data
			//	app.liveContent.uuid_3981e4f5_c8aa_4dd3_bb4f_72105d5c1ab9.title = 'moooooo';

		})
	});
}


/////////////


// in main blade

<?php
	$story->block()->flatten();
	?>

window.liveContent = {!!  json_encode($story->liveContent) !!};