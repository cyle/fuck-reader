require 'nokogiri'
require 'feedzirra'
require 'mysql2'
require 'yaml'

YAML::ENGINE.yamler = 'psych'

dbconfig = YAML.load_file('dbconn.yml')

# github reference: https://github.com/pauldix/feedzirra
# ruby doc: http://rubydoc.info/gems/feedzirra/0.1.3/frames

start_time = Time.new
puts "Start time: " + start_time.inspect
puts ""

feed_urls = []

dbclient = Mysql2::Client.new(:host => dbconfig["database"]["dbhost"], :username => dbconfig["database"]["dbuser"], :password => dbconfig["database"]["dbpass"], :database => dbconfig["database"]["dbname"])

dbresults = dbclient.query("SELECT feed_id, feed_url FROM feeds ORDER BY tsu ASC")
dbresults.each do |row|
	feed_urls.push(row["feed_url"])
end

feed_urls.each { |url| 
  
  puts ""
  puts "dealing with " + url
  
  feedraw = Feedzirra::Feed.fetch_raw("" + url + "")
  
  unless feedraw.is_a?(String)
    puts "error getting feed, returned: " + feedraw.to_s
    next
  end
    
  begin
    parsed = Feedzirra::Feed.parse(feedraw)
  rescue Feedzirra::NoParserAvailable => err
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

puts ""
end_time = Time.new
puts "End time: " + end_time.inspect
seconds_exec = end_time - start_time
puts "That took... " + seconds_exec.to_s + " seconds"