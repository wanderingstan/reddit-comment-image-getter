<?php

include 'get_matching_posts.php';
include 'download_comment_images_from_post.php';

$USER_DATA = array();
$USER_DATA_FILE = "user_data.serialized";
$WORD_DATA = array();
$WORD_DATA_FILE = "word_data.serialized";
if (file_exists($USER_DATA_FILE) || file_exists($WORD_DATA_FILE)) {
	print "Data files exist and I won't overwrite.";
	exit();
}

function process_all_posts() {
	global $subreddit;
	global $post_title_re;
	global $reddit_username;

	// create dir for our subreddit 
	if (!file_exists($subreddit)) {
	    mkdir($subreddit, 0777, true);
	}

	$posts_cache_filename = $subreddit . '/' . "matching_reddit_posts.php.data";
	$post_count_max = 25;

	if (file_exists($posts_cache_filename)) {
		echo "=== LOADED ALL WOTD POSTS ===\n";
		$posts = unserialize(file_get_contents($posts_cache_filename));
	}
	else {
		echo "=== FINDING ALL WOTD POSTS ===\n";
		$posts = get_matching_posts($subreddit, $post_title_re, $post_count_max, $reddit_username);
		file_put_contents($posts_cache_filename, serialize($posts));
	}

	echo "=== EXTRACTING IMAGES FROM WOTD POSTS ===\n";
	foreach ($posts as $post) {
		download_comment_images_from_post($post->url, $subreddit, $reddit_username, $post_title_re);
	}
}
?>
