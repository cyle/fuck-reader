$(document).ready(function() {
	
	// SHOW ME A POST, OR CLICK ON IT, OR WHATEVER
	$('div.post').click(postClickHandler);
	
	// INFINITE SCROLL WHOOOAAAAAAA
	if ($('div.feed-list').length) {
		$('div.feed-list').jscroll({
			nextSelector: 'a.nav-next',
			callback: function() {
				$('div.post').click(postClickHandler);
			}
		});
	}
	
	updatePageTitle();
	
	// KEYBOARD SHORTCUTS, BECAUSE YEAH
	window.addEventListener('keyup', function(e) {
		//console.log(e.keyCode);
		/*
		
		j = 74 = go down one post in list
		k = 75 = go up post in list
		s = 83 = star current item
		v = 86 = go to original
		m = 77 = toggle read or unread
		
		*/
	});
	
	$('a.toggle-feed-list').click(function(e) {
		e.preventDefault();
		$('div#sidebar').toggle();
		if ($(this).html() == 'Show Feed List') {
			$(this).html('Hide Feed List');
		} else {
			$(this).html('Show Feed List');
		}
		window.scrollTo(0, 0);
	});
	
});

function updatePageTitle() {
	var doctitle = document.title.replace(/\[\d+\] /i, '');
	if ($('body#feeds').length && $('#unread-feeds-count').length) {
		document.title = '['+$('#unread-feeds-count').html()+'] ' + doctitle;
	}
	if ($('body#feed').length && $('#unread-feed-count').length) {
		document.title = '['+$('#unread-feed-count').html()+'] ' + doctitle;
	}
}

function postClickHandler(event) {
	
	if ($(this).find('div.post-content').css('display') == 'none') {
		// if the post content is hidden, stop the user from following any links
		event.preventDefault();
	}
	
	var this_post_id = $(this).attr('data-post-id');
	
	if ($(event.toElement).hasClass('post-header') && $(this).find('div.post-content').css('display') == 'block') {
		
		// close the post if you are clicking on its header
		$(this).find('div.post-content').hide();
		
	} else if ($(event.toElement).hasClass('mark-this-post')) {
		
		//console.log('marking post ID ' + this_post_id + ' read/unread/uhhh');
		
		if ($(this).hasClass('unread')) {
			$(this).removeClass('unread');
			$(this).addClass('read');
			$.get('/read/post/'+this_post_id+'/');
			// reduce feeds unread count by 1
			$('#unread-feeds-count').html( ($('#unread-feeds-count').html() * 1) - 1 );
			if ($('#unread-feed-count').length) { $('#unread-feed-count').html( ($('#unread-feed-count').html() * 1) - 1 ); }
			updatePageTitle();
		} else if ($(this).hasClass('read')) {
			$(this).removeClass('read');
			$(this).addClass('unread');
			$.get('/unread/post/'+this_post_id+'/');
			// increase feeds unread count by 1
			$('#unread-feeds-count').html( ($('#unread-feeds-count').html() * 1) + 1 );
			if ($('#unread-feed-count').length) { $('#unread-feed-count').html( ($('#unread-feed-count').html() * 1) + 1 ); }
			updatePageTitle();
		}
		
	} else if ($(event.toElement).hasClass('star-this-post')) {
		
		//console.log('starring/unstarring post ID ' + this_post_id);
		
		if ($(this).hasClass('starred')) {
			$(this).removeClass('starred');
			$.get('/unstar/post/'+this_post_id+'/');
		} else {
			$(this).addClass('starred');
			$.get('/star/post/'+this_post_id+'/');
		}
		
	} else {
		
		// hide all other open posts
		$('div.post-content').not($(this).find('div.post-content')).hide();
		
		// otherwise, open the post if it's clicked on, and mark it as read
		
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
		
		if ($(this).hasClass('unread')) {
			$(this).removeClass('unread');
			$(this).addClass('read');
			//$.get('/read/post/'+this_post_id+'/');
			// reduce feeds unread count by 1
			$('#unread-feeds-count').html( ($('#unread-feeds-count').html() * 1) - 1 );
			if ($('#unread-feed-count').length) { $('#unread-feed-count').html( ($('#unread-feed-count').html() * 1) - 1 ); }
			updatePageTitle();
		}
		
	}
}