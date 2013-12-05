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
				extract_and_download_image($match[0][0], $comment_id."____".$comment_author.".jpg");
			}
		}
	}
}

function extract_and_download_image($url,$filename) {
	echo "Getting image from " . $url . "\n";
	$url_data = parse_url($url);
	$domain = $url_data['host'];	

	// echo "Getting $filename from $url  domain is $domain\n";

	if ($domain == "i.imgur.com") {
		// Imgur: Direct link to image. Easy.
		// file_put_contents($filename, file_get_contents($url));
		$img_url = "http://i.imgur.com/" . $url_data['path'];
	}
	elseif ($domain == "imgur.com") {
		// Imgur: extract from page
		// http://imgur.com/udjf8uT --> http://i.imgur.com/udjf8uT.jpg
		$img_url = "http://i.imgur.com/" . $url_data['path'] . ".jpg";
	}
	elseif ($domain == "instagram.com") {
		print "-----------------\n\n\n\n";
		$img_page_html = file_get_contents($url);
		// <meta property="og:image" content="http://distilleryimage3.ak.instagram.com/8e78fe2e531611e3abd4129eb955129a_8.jpg" />
		$pattern = '/\<meta property="og:image" content="([^\"]*\.jpg)/';
		preg_match($pattern, $img_page_html, $matches, PREG_OFFSET_CAPTURE);
		$img_url = $matches[1][0];
		print_r($matches);
		print "-----------------\n\n\n\n";
	}
	elseif ($domain == "twitter.com") {
		$img_page_html = file_get_contents($url);
		// https://pbs.twimg.com/media/BavEMwpCIAA8o4N.jpg
		$pattern = "/https:\/\/pbs\\.twimg\.com\/media\/[^\.]*\.jpg/";
		preg_match($pattern, $img_page_html, $matches, PREG_OFFSET_CAPTURE);
		$img_url = $matches[0][0];
	}
	file_put_contents($filename, file_get_contents($img_url));
}

// $test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s5qai/wotd_shave_125/.json");
$test_post = new redditPost("http://www.reddit.com/r/calligraffiti/comments/1s2gkh/wotd_stout_124/.json");

// http://www.reddit.com/r/calligraffiti/comments/1s2gkh/wotd_stout_124/.json
// http://www.reddit.com/r/calligraffiti/comments/1s5qai/wotd_shave_125/.json

/*

http://www.imagemagick.org/Usage/montage/

create the montage:

$ montage *.jpg -geometry 150x150+2+2 -tile 2x  montage_geom_size.jpg
*/
?>