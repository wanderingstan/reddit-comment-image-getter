<?
include 'process_all_posts.php';

// Your reddit username
// (Needed to set user-agent string, per Reddit API: https://github.com/reddit/reddit/wiki/API )
$reddit_username = "YOUR_REDDIT_USERNAME";

// Subreddit to seach in:
$subreddit = "calligraphy";

// But only process posts with titles that match this pattern:
$post_title_re = '/Word of the day +-[^-]*- ?(?P<name>.*)$/i';

// do it!
process_all_posts();

?>
