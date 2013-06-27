<?php
if (!isset($this_page)) { $this_page = ''; }
?>
<div id="sidebar">
<ul>
<li><a href="/feeds/">Go back to feeds</a></li>
<li><a href="/subs/"<?php if ($page_title == 'subscriptions') { echo ' class="active"'; } ?>>Manage Subscriptions</a></li>
<li><a href="/settings/"<?php if ($page_title == 'settings') { echo ' class="active"'; } ?>>Settings</a></li>
<li><a href="/help/"<?php if ($page_title == 'help') { echo ' class="active"'; } ?>>Help</a></li>
</ul>
</div>