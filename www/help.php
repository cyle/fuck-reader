<?php

$login_required = true;
require_once('../www-includes/login_check.php');

//require_once('../www-includes/dbconn_mysql.php');

$page_title = 'help';

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
<li>Interface tweaks -- your suggestions are welcome, I guess</li>
<li>Mobile versions for iPhone and iPad</li>
<li>Keyboard shortcuts</li>
<li>Lots of backend minutia</li>
<li>Sort by publish date oldest-first (latest-first is the current default)</li>
<li>Filling in the "not done yet" stuff in the settings page</li>
</ul>
<p>These features may come someday:</p>
<ul>
<li>An API</li>
<li>"View by day" mode, for easier catching-up</li>
</ul>
</div>

<div class="help-section">
<h3>Functionality</h3>
<p>To mark a post as read (or revert it to unread) without having to actually open it, click the &#10004; symbol next to the post title.</p>
<p>To star/unstar a post, click the &#10029; symbol next to the post title.</p>
</div>

<div class="help-section">
<h3>Keyboard commands:</h3>
<p><b>(NOTE: THESE ARE NOT ACTUALLY IMPLEMENTED YET, LOL)</b></p>
<table class="fucked">
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
<div id="faq-answers">
<p><b>Can I import my Google Reader shit to here?</b><br />Yes, go to settings, upload your <i>subscriptions.xml</i> file.</p>
<p><b>Can I export my shit from here?</b><br />Yes, but not right now. Haven't created that yet.</p>
<p><b>Will there be an API for all of this?</b><br />Yes, but not right now. Haven't created that yet.</p>
<p><b>Will this shit be free forever?</b><br />Maybe. Probably. I dunno. Depends. Fuck off.</p>
<p><b>Can I get invites for my friends and shit?</b><br />Maybe. I dunno. Send me a whore-ific message <a target="_blank" href="http://twitter.com/cylegage">@cylegage</a>.</p>
<p><b>Google Reader used to do X, or Feedly does X, or Reddit does X, can you make this do it, too?</b><br />You can fuck right the fuck off.</p>
<p><b>Can I remove all of my data from this?</b><br />Of course you can. I don't want your shit anyway. But that feature isn't done yet.</p>
<p><b>Are you making money off of this? Are you selling my shit?</b><br />Fuck no. I'm not making a goddamn cent. For serious, I'm not.</p>
<p><b>How long do you keep posts?</b><br />The starting plan is to delete posts (read and unread) after 30 days unless they're starred.</p>
<p><b>I'm seeing some posts more than once, why is that?</b><br />The feed retrieval mechanism caches posts based on a checksum of the post content. So if the post content changes, it thinks it's a new post. Maybe that's not the best way to do it, but I'm figuring it out.</p>
<p><b>Why are some posts showing up as read before I've read them?</b><br />Post titles will display as "read" if you have already visited the URL of the original article, but will still technically be "unread" on here.</p>
</div>
</div>

<div class="help-section">
<h3>I need more help!</h3>
<p>For now, send me a goddamn tweet: <a target="_blank" href="http://twitter.com/cylegage">@cylegage</a></p>
</div>

<div class="help-section">
<h3>View some stats...</h3>
<p><a href="/stats/">oh shit...</a></p>
</div>

</div>
<?php require_once('footer.php'); ?>
</body>
</html>