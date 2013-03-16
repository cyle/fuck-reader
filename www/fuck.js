$(document).ready(function() {
	
	$('div.post').click(function(event) {
		if ($(this).find('div.post-content').css('display') == 'none') {
			// if the post content is hidden, stop the user from following any links
			event.preventDefault();
		}
		// hide all other open posts
		$('div.post-content').not($(this).find('div.post-content')).hide();
		if ($(event.toElement).hasClass('post-title') && $(this).find('div.post-content').css('display') == 'block') {
			// close the post if you are clicking on its header
			$(this).find('div.post-content').hide();
		} else {
			// otherwise, open the post if it's clicked on, and mark it as read
			$(this).find('div.post-content').show();
			if ($(this).hasClass('read') == false) {
				$(this).addClass('read');
				var this_post_int = $(this).attr('data-post-id');
				$.get('/read/post/'+this_post_int+'/');
			}
		}
		
	});
	
});