# FUCK READER

A very simple RSS reader. I built it just before Google Reader died, and I've been using this every day on my own server. A couple of my friends use it, too.

Front-end is PHP, feed fetching is Ruby. Feeds, posts, and user post state all stored in MySQL.

## Front-end installation

This was built with lighttpd and php5-cgi (running via fastcgi). Set document root to the `www` folder.

Edit `www-includes/dbconn_mysql.example.php` with the necessary settings and rename it to `dbconn_mysql.php` when you're done.

If you want to use Google Analytics, edit `config/google_analytics.example.php` and rename it to `google_analytics.php` when you're done. If you **do not** want to use Google Analytics, then comment out the last line in `footer.php` which mentions it.

I used MySQL, and included in `config/fuckreader.sql` are the table schemas used by FUCK READER. Just make a database called "fuckreader" and import that using phpMyAdmin or something.

## Feed Fetching Prereqs

Feed fetching uses the ["feedzirra" library](https://github.com/pauldix/feedzirra) from the folks at feedbin.

On Debian-based servers (Debian, Ubuntu), you need these prereqs:

    apt-get install ruby1.9.1 ruby1.9.1-dev build-essential libcurl4-openssl-dev libxml2-dev libxslt-dev

Install rubygems to a specific version. At the time of this writing, rubygems 2.0+ won't install feedzirra.

    git clone https://github.com/rubygems/rubygems.git
    cd rubygems
    git checkout v1.8.25
    ruby setup.rb

Install feedzirra:

    gem install feedzirra

Now you can use the `getfeeds.rb` file. Fun!

## Crontab
    
Put this in your crontab file:

    */5 * * * * cd /path/to/fuckreader/ruby; /usr/bin/ruby getfeeds.rb > ../logs/feeds.log
    0 3 * * * cd /path/to/fuckreader/www-includes; /usr/bin/php cleanup.php > ../logs/cleanup.log
    
You're free to set it to whatever frequency you want. (See note below about the cleanup script, btw.)

## Notes -- please read

The biggest and most important note I can give is that **this is an unfinished project** and it has quirks. For instance, the cleanup script deletes stuff older than 30 days, but then if there are things older than 30 days in the RSS feeds you're scraping, they'll pop back up. That's annoying. Haven't figured that out yet, it probably means I'll need to cache things forever in some fashion. So for now, *I've turned the cleanup script off on my own server*.

There are a lot of planned features. I have no idea what'll be breaking what in the future. However, since I do use this every day, I probably won't make any features that seriously breaks what's already here.

The `getfeeds.rb` script is not as efficient as I'd like, and with very dense RSS feeds (ones that update very often), it can choke a lot of memory. I plan on rewriting it in a better language, like Go.

There's some Redis functionality that is half-in here, but it doesn't actually do anything, so don't worry about the Redis stuff. It was a failed experiment.

There's a lot of MySQL indexing stuff going on which makes this all a lot faster, but in general if you have 50+ feeds, the site will be kinda slow. Be patient with it.