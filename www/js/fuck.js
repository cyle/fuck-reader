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
			var this_post_id = $(this).attr('data-post-id');
			$.ajax({
				url: '/get/post/'+this_post_id+'/',
				dataType: 'json',
				success: function(data) {
					if (data.error != undefined) {
						alert('There was an error of some kind...');
						console.log(data);
					} else {
						$('div.post-content#post-content-'+this_post_id).html(data.content);
						$('div.post-content#post-content-'+this_post_id).show();
					}
				},
				error: function(jqxhr, status, err) {
					console.log('get post error: ' + status + ' ' + err);
				}
			});
			if ($(this).hasClass('read') == false) {
				$(this).addClass('read');
				//$.get('/read/post/'+this_post_id+'/');
			}
		}
		
	});
	
	if ($('div.feed-list').length) {
		$('div.feed-list').jscroll();
	}
	
});