# FUCK READER

A very simple RSS reader.

Front-end is PHP, feed fetching is Ruby. Feeds, posts, and user post state all stored in MySQL.

## Front-end setup

This was built with lighttpd and php5-cgi (running via fastcgi). Set document root to the "www" folder.

Edit **config/login_config.example.php** and rename it to login_config.php when you're done.

Edit **www-includes/dbconn_mysql.example.php** and rename it to dbconn_mysql.php when you're done.

If you want to use Google Analytics, edit **config/google_analytics.example.php** and rename it to google_analytics.php when you're done.

SQL set up not included yet...

## Feed Fetching Prereqs

Feed fetching uses the "feedzirra" library from the folks at feedbin.

On Debian-based servers (Debian, Ubuntu), you need these prereqs:

    apt-get install ruby1.9.1 ruby1.9.1-dev build-essential libcurl4-openssl-dev libxml2-dev libxslt-dev

Install rubygems to a specific version. At the time of this writing, rubygems 2.0+ won't install feedzirra.

    git clone https://github.com/rubygems/rubygems.git
    cd rubygems
    git checkout v1.8.25
    ruby setup.rb

Install feedzirra:

    gem install feedzirra

Now you can use the getfeeds.rb file. Fun!