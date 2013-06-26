<?php

$login_required = true;
require_once('../www-includes/login_check.php');

//require_once('../www-includes/dbconn_mysql.php');

$this_page = 'help';

require_once('head.php');
?>
<body id="help">
<?php require_once('header.php'); ?>
<?php require_once('mini_sidebar.php'); ?>
<div id="main-column">

<h2>Help? You? Okay.</h2>

<p>Here's some stuff to know...</p>

<div class="help-section">
<h3>Upcoming Features</h3>
<p>Here are some features at the top of my priority list:</p>
<ul>
<li>Mark as read/unread per post</li>
<li>Star a post, keep it fucking forever</li>
<li>Keyboard shortcuts</li>
<li>A working subscription management page</li>
<li>Mobile versions for iPhone and iPad</li>
</ul>
<p>For other features, look in the FAQ, or observe the blank "not done yet" sections of the settings page.</p>
</div>

<div class="help-section">
<h3>Keyboard commands:</h3>
<p><b>(NOTE: THESE ARE NOT ACTUALLY IMPLEMENTED YET, LOL)</b></p>
<table>
<tr><th>Key</th><th>What it does</th></tr>
<tr><td>j</td><td>Go to the next post! (down, to be exact)</td></tr>
<tr><td>k</td><td>Go to the previous post! (up, to be exact)</td></tr>
<tr><td>m</td><td>Mark the current post as READ (or UNREAD, if already read)</td></tr>
<tr><td>s</td><td>Star (or unstar) the current item!</td></tr>
<tr><td>v</td><td>Go to the original post (in a new tab or window)</td></tr>
</table>
</div>

<div class="help-section">
<h3>FAQ</h3>
<p><b>Can I import my Google Reader shit to here?</b> Yes, go to settings, upload your <i>subscriptions.xml</i> file.</p>
<p><b>Can I export my shit from here?</b> Yes, but not right now. Haven't created that yet.</p>
<p><b>Will there be an API for all of this?</b> Yes, but not right now. Haven't created that yet.</p>
<p><b>Will this shit be free forever?</b> Maybe. Probably. I dunno. Depends. Fuck off.</p>
<p><b>Can I get invites for my friends and shit?</b> Maybe. I dunno. Send me a whore-ific message <a target="_blank" href="http://twitter.com/cylegage">@cylegage</a>.</p>
<p><b>Google Reader used to do X, or Feedly does X, or Reddit does X, can you make this do it, too?</b> You can fuck right the fuck off.</p>
<p><b>Can I remove all of my data from this?</b> Of course you can. I don't want your shit anyway. But that feature isn't done yet.</p>
<p><b>Are you making money off of this? Are you selling my shit?</b> Fuck no. I'm not making a goddamn cent. For serious, I'm not.</p>
<p><b>How long do you keep posts?</b> The starting plan is to delete posts (read and unread) after 30 days unless they're starred.</p>
</div>

<div class="help-section">
<h3>I need more help!</h3>
<p>For now, send me a goddamn tweet: <a target="_blank" href="http://twitter.com/cylegage">@cylegage</a></p>
</div>


</div>
<?php require_once('footer.php'); ?>
</body>
</html>