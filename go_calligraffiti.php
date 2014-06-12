<?
include 'process_all_posts.php';

// Your reddit username
// (Needed to set user-agent string, per Reddit API: https://github.com/reddit/reddit/wiki/API )
$reddit_username = "YOUR_USER_NAME";

// Subreddit to seach in:
$subreddit = "calligraffiti";

// But only process posts with titles that match this pattern:
$post_title_re = '/WotD[^"]*"(?P<name>[^"]*)([ "]*)?([0-9]+\/[0-9]+)?/';

// do it!
process_all_posts();

?>
