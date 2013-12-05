<?php
/*
reddit-comment-image-getter


By Stan James http://wanderingstan.com 
*/

include 'download_image_from_service.php';

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

		$post_title = $post_data[0]->data->children[0]->data->title;

		$post_created = $post_data[0]->data->children[0]->data->created;

		$post_created_date =  gmdate("Y-m-d\TH-i-s\Z", $post_created);

		echo $post_title . "\n";
		// try to get word of the day
		$pattern = '/WotD "([^"]*)"/';
		preg_match($pattern, $post_title, $matches, PREG_OFFSET_CAPTURE);
		$wotd = $matches[1][0];

		$post_directory_name = $post_created_date . "-" . $wotd;
		if (!file_exists($post_directory_name)) {
		    mkdir($post_directory_name, 0777, true);
		}

		// yields array with one entry per top-level comment
		$comments = $post_data[1]->data->children;
		// var_dump($comments);

		foreach ($comments as $comment) {

			$comment_id = $comment->data->id;
			$comment_author = $comment->data->author;
			$comment_body = $comment->data->body;

			echo $comment_id . " : " . $comment_author . " --> " . $comment_body . "\n";

			$image_filename = $post_directory_name . "/" . $comment_author . "____".$comment_id.".jpg";

			preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $comment_body, $match);
			// print_r($match);
			if (count($match)>0) {
				echo $image_filename . "\n";
				download_image_from_service($match[0][0], $image_filename);
			}
		}

		shell_exec ( 'montage '.$post_directory_name.'/*.jpg -geometry 150x150+2+2 -tile 2x '.$post_directory_name.'/montage.jpg' );

	}
}

// $test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s5qai/wotd_shave_125/.json");
$test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s2gkh/wotd_stout_124/.json");


/*

http://www.imagemagick.org/Usage/montage/

create the montage:

$ montage *.jpg -geometry 150x150+2+2 -tile 2x  montage_geom_size.jpg
*/
?>