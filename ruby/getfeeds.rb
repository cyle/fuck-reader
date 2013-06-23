require 'nokogiri'
require 'feedzirra'

# github reference: https://github.com/pauldix/feedzirra
# ruby doc: http://rubydoc.info/gems/feedzirra/0.1.3/frames

start_time = Time.new
puts "Start time: " + start_time.inspect
puts ""

feed_urls = []

f = File.open("subscriptions.xml")
subs = Nokogiri::XML(f)
f.close

subs.xpath("//outline").each { |node| feed_urls.push(node.attribute("xmlUrl")) }

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