#
# cylesoft fuckreader feed updater, lol
#

# notes:
# feedzirra github reference: https://github.com/pauldix/feedzirra
# feedzirra ruby doc: http://rubydoc.info/gems/feedzirra/0.1.3/frames
#
# this script DOES NOT use feedzirra's fetch_and_parse() method because it breaks if there's any problem fetching a feed
#


# ok, load everything we need

require 'nokogiri'
require 'feedzirra'
require 'mysql2'
require 'yaml'

YAML::ENGINE.yamler = 'psych'

# load database configuration file
dbconfig = YAML.load_file('dbconn.yml')

# when did this script start?
start_time = Time.new
puts "Start time: " + start_time.inspect
puts ""

# this will hold our feed endpoint URLs
feed_urls = []

# okay -- fetch every feed URL from the database
dbclient = Mysql2::Client.new(:host => dbconfig["database"]["dbhost"], :username => dbconfig["database"]["dbuser"], :password => dbconfig["database"]["dbpass"], :database => dbconfig["database"]["dbname"])
dbresults = dbclient.query("SELECT feed_id, feed_url FROM feeds ORDER BY tsu ASC")
dbresults.each do |row|
	feed_urls.push(row["feed_url"])
end

# now, with each feed URL, grab it and parse it
feed_urls.each { |url| 
  
  puts ""
  puts "dealing with " + url
  
  # get the raw feed
  feedraw = Feedzirra::Feed.fetch_raw("" + url + "")
  
  unless feedraw.is_a?(String)
  	# maybe it gave us a 404 or something like that, whatever. move on.
  	# should probably take note of this somewhere in the database
    puts "error getting feed, returned: " + feedraw.to_s
    next
  end
    
  begin
  	# try parsing the feed!
    parsed = Feedzirra::Feed.parse(feedraw)
  rescue Feedzirra::NoParserAvailable => err
  	# aw crap
    puts "error parsing!"
  else
    # successfully parsed... deal with it!
    puts ""
    puts "Feed title: " + parsed.title unless parsed.title.nil?
    parsed.entries.each { |entry| 
      puts "Entry title: "+ entry.title unless entry.title.nil?
    }
  end
  
}

# okay, all done

puts ""

# mark when the script completed
end_time = Time.new
puts "End time: " + end_time.inspect

# mark how long that took
seconds_exec = end_time - start_time
puts "That took... " + seconds_exec.to_s + " seconds"