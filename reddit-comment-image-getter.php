<?php
/*
reddit-comment-image-getter


By Stan James http://wanderingstan.com 
*/

include 'jpg_url_from_service_url.php';


function download_comment_images_from_post($post_url) {

	$post_json = file_get_contents($post_url.".json");
	$post_data = json_decode($post_json);

	$post_title = $post_data[0]->data->children[0]->data->title;
	$post_created = $post_data[0]->data->children[0]->data->created;
	$post_created_date =  gmdate("Y-m-d\TH-i-s\Z", $post_created);

	echo $post_title . "\n";
	// try to get word of the day
	$pattern = '/WotD "([^"]*)"/';
	$post_title_re = '/WotD *"([^"]*)([ "]*)?([0-9]+\/[0-9]+)?/';

	preg_match($post_title_re, $post_title, $matches, PREG_OFFSET_CAPTURE);
	$wotd = $matches[1][0];
	$month_day = $matches[3][0];

	// create montage directory
	$montage_directory_name = "montage";
	if (!file_exists($montage_directory_name)) {
	    mkdir($montage_directory_name, 0777, true);
	}

	// create director for posts
	$post_directory_name = $post_created_date . "-" . $wotd;
	if (!file_exists($post_directory_name)) {
	    mkdir($post_directory_name, 0777, true);
	}

	// yields array with one entry per top-level comment
	$comments = $post_data[1]->data->children;

	foreach ($comments as $comment) {

		$comment_id = $comment->data->id;
		$comment_author = $comment->data->author;
		$comment_body = $comment->data->body;

		echo $comment_id . " : " . $comment_author . " --> " . $comment_body . "\n";

		$image_filename = $post_directory_name . "/" . $comment_author . "____".$comment_id.".jpg";

		preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $comment_body, $match);
		if (count($match)>0) {
			if (count($match[0])>1) {
				error_log("Err: More than one image found in comment: " . $comment_body);
			}
			echo $image_filename . "\n";
			$img_html_url = $match[0][0]; // URL of the "image html page" on hosted service. NOT the actual jpg. 
			$img_url = jpg_url_from_service_url($img_html_url);
			if (!$img_url) {
				error_log  ("Err: Could not extract image from: " . $img_html_url);
			}
			else {
				// Download our image and savev to local file
				file_put_contents($image_filename, file_get_contents($img_url));						
			}

			sleep(2);
		}
	}

	shell_exec ( 'montage '.$post_directory_name.'/*.jpg -geometry 150x150+2+2 -tile 2x '.$montage_directory_name.'/'.$post_directory_name.'.jpg' );

}

/*
Test it:

// $test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s5qai/wotd_shave_125/.json");
$test_post = download_comment_images_from_post("http://www.reddit.com/r/calligraffiti/comments/1s2gkh/wotd_stout_124/.json");

*/

/*
http://www.imagemagick.org/Usage/montage/
create the montage:
$ montage *.jpg -geometry 150x150+2+2 -tile 2x  montage_geom_size.jpg
*/
?>