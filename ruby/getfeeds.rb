#
# cylesoft fuckreader feed updater, lol
#

# notes:
# feedzirra github reference: https://github.com/pauldix/feedzirra
# feedzirra ruby doc: http://rubydoc.info/gems/feedzirra/0.1.3/frames
# mysql2 github ref: https://github.com/brianmario/mysql2
#
# this script DOES NOT use feedzirra's fetch_and_parse() method because it breaks if there's any problem fetching a feed
#


# ok, load everything we need

require 'nokogiri'
require 'feedzirra'
require 'redis'
require 'mysql2'
require 'yaml'
require 'digest/sha1'

YAML::ENGINE.yamler = 'psych'

# some variables for data later
feeds_processed = 0
entries_processed = 0
entries_new = 0

# load database configuration file
dbconfig = YAML.load_file('dbconn.yml')

# when did this script start?
start_time = Time.new
puts "Start time: " + start_time.inspect
puts ""

# set up the database connection
dbclient = Mysql2::Client.new(:host => dbconfig["database"]["dbhost"], :username => dbconfig["database"]["dbuser"], :password => dbconfig["database"]["dbpass"], :database => dbconfig["database"]["dbname"])

# set up the redis connection
redis = Redis.new

# this will hold our feed endpoint URLs
feed_urls = []

# this will hold our users' feed lists to use later
users_feeds = Hash.new()

# get users' feeds
getfeeds = dbclient.query("SELECT * FROM users_feeds ORDER BY user_id ASC, feed_id ASC")
getfeeds.each do |row|
	users_feeds[row["user_id"]] = [] unless users_feeds.has_key?(row["user_id"])
	users_feeds[row["user_id"]].push(row["feed_id"])
end

# okay -- fetch every feed URL from the database
dbresults = dbclient.query("SELECT feed_id, feed_url FROM feeds ORDER BY tsu ASC")
dbresults.each do |row|
	feed_urls.push(row)
end

# now, with each feed URL, grab it and parse it
feed_urls.each { |feed_info| 
	
	puts ""
	puts "dealing with " + feed_info["feed_url"]
	
	feed_id = feed_info["feed_id"].to_i
	feed_new_posts_count = 0
	
	# get the raw feed
	feedraw = Feedzirra::Feed.fetch_raw("" + feed_info["feed_url"] + "")
	
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
		puts "error parsing feed!"
		next;
	else
		# successfully parsed... deal with it!
		puts ""
		puts "Feed title: " + parsed.title unless parsed.title.nil?
				
		parsed.entries.each do |entry| 
			
			# okay now we are parsing entries, cool...
			
			entry_content_hash = "no hash to be made!"
			
			if entry.content.nil? and entry.summary.nil?
				# shit -- both are nil? wtf
				#entry_content_hash = "both summary and content are nil"
				unless entry.title.nil?
					entry_content_hash = Digest::SHA1.hexdigest entry.title
				else
					entry_content_hash = Digest::SHA1.hexdigest entry.url
				end
			elsif entry.content.nil? == false and entry.summary.nil?
				# content is available
				entry_content_hash = Digest::SHA1.hexdigest entry.content
			elsif entry.content.nil? and entry.summary.nil? == false
				# summary is available
				entry_content_hash = Digest::SHA1.hexdigest entry.summary
			else
				# both content and summary are available
				entry_content_hash = Digest::SHA1.hexdigest entry.content
			end
			
			puts "Entry title: " + entry.title unless entry.title.nil?
			puts "Entry hash: " + entry_content_hash
			
			# check to see if this entry's hash has already been recorded
			#dbchk_query = "SELECT post_id FROM posts WHERE chksum='"+entry_content_hash+"' AND feed_id=" + feed_info["feed_id"].to_s)
			
			# try this dbchk query instead:
			dbchk_query = "SELECT post_id FROM posts WHERE feed_id="+feed_id.to_s+" AND post_guid='"+(Digest::SHA1.hexdigest entry.url)+"' AND "
			if entry.title.nil?
				dbchk_query = dbchk_query + "post_title IS NULL"
			else
				dbchk_query = dbchk_query + "post_title='" + dbclient.escape(entry.title).slice(0..249) + "'"
			end
			
			dbchkresults = dbclient.query(dbchk_query)
			unless dbchkresults.count > 0
				# okay, save this entry to the database, since it's new
				
				if entry.title.nil?
					entry_title_db = 'null'
				else
					entry_title_db = "'" + dbclient.escape(entry.title) + "'"
				end
				
				entry_guid_db = "'" + (Digest::SHA1.hexdigest entry.url) + "'"
				entry_link_db = "'" + dbclient.escape(entry.url) + "'"
				
				if entry.content.nil? and entry.summary.nil?
					entry_content_db = "'" + dbclient.escape("'No content.'") + "'"
				elsif entry.content.nil? and !entry.summary.nil?
					entry_content_db = "'" + dbclient.escape(entry.summary) + "'"
				else
					entry_content_db = "'" + dbclient.escape(entry.content) + "'"
				end
				
				if entry.author.nil?
					entry_byline_db = 'null'
				else
					entry_byline_db = "'" + dbclient.escape(entry.author) + "'"
				end
				
				entry_pubdate_db = dbclient.escape(entry.published.to_i.to_s)
				entry_ts_db = Time.now.to_i.to_s
				entry_chksum_db = "'" + entry_content_hash + "'"
				
				dbsaveentry = dbclient.query("INSERT INTO posts (feed_id, post_title, post_guid, post_permalink, post_content, post_byline, post_pubdate, ts, chksum) VALUES (#{feed_id}, #{entry_title_db}, #{entry_guid_db}, #{entry_link_db}, #{entry_content_db}, #{entry_byline_db}, #{entry_pubdate_db}, #{entry_ts_db}, #{entry_chksum_db})")
				
				puts "entry added to database"
				
				entries_new += 1
				feed_new_posts_count += 1
				
			else
				puts "entry already in the database, moving on"
			end
			
			entries_processed += 1
			
		end
		
		# try updating feed title and whatnot
		unless parsed.title.nil?
			feed_title_db = "'"+dbclient.escape(parsed.title)+"'"
		else
			feed_title_db = 'null'
		end
		
		unless parsed.url.nil?
			feed_homeurl_db = "'"+dbclient.escape(parsed.url)+"'"
		else
			feed_homeurl_db = 'null'
		end
		
		# update the feed row
		updatefeedrow = dbclient.query("UPDATE feeds SET tsu=UNIX_TIMESTAMP(), feed_title=#{feed_title_db}, feed_homeurl=#{feed_homeurl_db} WHERE feed_id=#{feed_id}")
		
		# if there were any new posts, update users' unread counts
		if feed_new_posts_count > 0
			users_feeds.each do |user_id, users_feed_ids|
				if users_feed_ids.include?(feed_id)
					redis.incrby("counts:#{user_id}:all:unread", feed_new_posts_count)
					redis.incrby("counts:#{user_id}:#{feed_id}:unread", feed_new_posts_count)
				end
			end
		end
		
		# all done with this feed, moving on
		feeds_processed += 1
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
puts "Processed " + feeds_processed.to_s + " feeds"
puts "Processed " + entries_processed.to_s + " entries"
puts "Processed " + entries_new.to_s + " new entries"
