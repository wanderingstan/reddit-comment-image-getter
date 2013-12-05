<?php
/*
reddit-comment-image-getter


By Stan James http://wanderingstan.com 
*/

class redditPost {

	public $post_url;

	function __construct($post_url) {
		global $DEBUG;

		$this->post_url = $post_url;
;
		if ($DEBUG) {
			echo "Loading POST info from" . $post_url . "\n";
		}
		$post_json = file_get_contents($post_url.".json");
		$post_data = json_decode($post_json);


		// yields array with one entry per top-level comment
		$comments = $post_data[1]->data->children;
		// var_dump($comments);

		foreach ($comments as $comment) {

			$comment_id = $comment->data->id;
			$comment_author = $comment->data->author;
			$comment_body = $comment->data->body;

			echo $comment_id . " : " . $comment_author . " --> " . $comment_body . "\n";

			preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $comment_body, $match);
			// print_r($match);
			if (count($match)>0) {
				extract_and_download_image($match[0][0]);
				//imgur, twitter, instagram
			}
		}

	}
}

function extract_and_download_image($url) {
	echo "Getting image from " . $url . "\n";
}

// $test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s5qai/wotd_shave_125/.json");
$test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s2gkh/wotd_stout_124/.json");

// http://www.reddit.com/r/calligraffiti/comments/1s2gkh/wotd_stout_124/.json
// http://www.reddit.com/r/calligraffiti/comments/1s5qai/wotd_shave_125/.json
?>