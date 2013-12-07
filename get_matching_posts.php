<?php

/**
 * Given a subreddit, extract posts that match a pattern
 * See: http://stackoverflow.com/questions/13328798/reddit-api-returning-useless-json
 */
function get_matching_posts($subreddit, $post_title_re, $post_count_max=100, $reddit_username="Anonymous reddit user") {

	// array that we will fill up
	$matching_posts_array = array();

	// Configure our downloading agent
	$options = array(
	  'http'=>array(
	    'method'=>"GET",
	    'header'=>"Accept-language: en\r\n" .
	              "User-Agent: /u/".$reddit_username."\r\n" // i.e. An iPad 
	  )
	);
	$context = stream_context_create($options);

	$after_post_id="";
	do {
		$subreddit_url = "http://reddit.com/r/" . $subreddit . "/";
		$json_query_url = $subreddit_url.".json?count=" . $post_count_max . "&after=" . $after_post_id;
		$subreddit_posts_data = json_decode(file_get_contents($json_query_url,false, $context));

		echo "\n\n\n\n\n-------------------------\n";
		echo "loading from json_query_url:" . $json_query_url . "\n";

		// iterate over each post
		foreach ($subreddit_posts_data->data->children as $post) {
			preg_match($post_title_re, $post->data->title, $matches, PREG_OFFSET_CAPTURE);
			if (count($matches)) {

				$wotd = $matches[1][0];
				$month_day = $matches[3][0];
				print $post->data->title . " -->" . $wotd . "\n";

				// Append our match information to the post
				$post->data->title_matches = $matches;
				array_push($matching_posts_array,$post->data);
			}
		}

		// get ID of the last returned post so we know where to begin next request
		$after_post_id = $subreddit_posts_data->data->after;

		sleep(2);
	} while (count($subreddit_posts_data->data->children) >= $post_count_max);

	return $matching_posts_array;
}

/* Sample usage 

// Your reddit username
// Needed to set user-agent string, per Reddit API:
// https://github.com/reddit/reddit/wiki/API
$reddit_username = "wanderingstan";
// Look in this subreddit:
$subreddit = "calligraffiti";
// At this many posts
$post_count_max = 100;
// But only process posts with titles that match this pattern:
$post_title_re = '/WotD *"([^"]*)([ "]*)?([0-9]+\/[0-9]+)?/';

print_r(get_matching_posts($subreddit, $post_title_re, $post_count_max, $reddit_username));

*/

?>