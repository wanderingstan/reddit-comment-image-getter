<?php

include 'get_matching_posts.php';
include 'reddit-comment-image-getter.php';

// Your reddit username
// Needed to set user-agent string, per Reddit API:
// https://github.com/reddit/reddit/wiki/API
$reddit_username = "wanderingstan";
// Look in this subreddit:
$subreddit = "calligraffiti";
// At this many posts
$post_count_max = 25;
// But only process posts with titles that match this pattern:
$post_title_re = '/WotD *"([^"]*)([ "]*)?([0-9]+\/[0-9]+)?/';


echo "=== FINDING ALL WOTD POSTS ===\n";
$posts = get_matching_posts($subreddit, $post_title_re, $post_count_max, $reddit_username);

echo "=== EXTRACTING IMAGES FROM WOTD POSTS ===\n";
foreach ($posts as $post) {
	echo "--->" . $post->title . "\n";
	download_comment_images_from_post($post->url);
}

?>
