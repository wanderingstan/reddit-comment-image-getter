<?php
/*
reddit-comment-image-getter


By Stan James http://wanderingstan.com 
*/

include 'jpg_url_from_service_url.php';

/**
 * Function: sanitize
 * Returns a sanitized string, typically for URLs.
 *
 * Parameters:
 *     $string - The string to sanitize.
 *     $force_lowercase - Force the string to lowercase?
 *     $anal - If set to *true*, will remove all non-alphanumeric characters.
 */
function sanitize_file_name($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}

// TODO: replace with: http://stackoverflow.com/questions/2668854/sanitizing-strings-to-make-them-url-and-filename-safe
function clean_filename($filename) {
	$replace="_";
	$pattern="/([[:alnum:]_\.-]*)/";
	return str_replace(str_split(preg_replace($pattern,$replace,$fname)),$replace,$filename);
}

/**
 *
 */
function download_comment_images_from_post($post_url, $destination_dir, $reddit_username, $post_title_re="/.*/") {

	if (file_exists(sanitize_file_name($post_url) . ".serialized")) {
		echo "Found cache for ".$post_url."\n";
		$post_data = unserialize(file_get_contents(sanitize_file_name($post_url) . ".serialized"));
	}
	else {
		// Configure our downloading agent
		$options = array(
		  'http'=>array(
		    'method'=>"GET",
		    'header'=>"Accept-language: en\r\n" .
		              "User-Agent: /u/".$reddit_username."\r\n" // i.e. An iPad 
		  )
		);
		$context = stream_context_create($options);

		$post_json = file_get_contents($post_url.".json",false, $context);
		$post_data = json_decode($post_json);

		file_put_contents(sanitize_file_name($post_url) . ".serialized", serialize($post_data));
	}
	$post_title = $post_data[0]->data->children[0]->data->title;
	$post_created = $post_data[0]->data->children[0]->data->created;
	$post_created_date =  gmdate("Y-m-d\TH-i-s\Z", $post_created);

	// search for word of the day (or whatever we're looking for)
	preg_match($post_title_re, $post_title, $matches, PREG_OFFSET_CAPTURE);
	$wotd = $matches['name'][0];

	echo "Post: '" . $post_title . "'' from " . $post_created_date. ". WotD is '" . $wotd . "'\n";

	// create montage directory if needed
	$montage_directory_name = $destination_dir . "/montage";
	if (!file_exists($montage_directory_name)) {
	    mkdir($montage_directory_name, 0777, true);
	}

	// create montage directory if needed
	$sidebar_directory_name = $destination_dir . "/sidebar";
	if (!file_exists($sidebar_directory_name)) {
	    mkdir($sidebar_directory_name, 0777, true);
	}


	// create directory for posts
	$post_directory_name = $destination_dir . '/' . $post_created_date . "-" . sanitize_file_name($wotd,false);
	if (!file_exists($post_directory_name)) {
	    mkdir($post_directory_name, 0777, true);
	}

	// yields array with one entry per top-level comment
	$comments = $post_data[1]->data->children;

	foreach ($comments as $comment) {

		$comment_id = $comment->data->id;
		$comment_author = $comment->data->author;
		$comment_body = $comment->data->body;
		$comment_timestamp = $comment->data->created;

		echo "    Comment: " . $comment_id . " : " . $comment_author . "\n";

		if ($comment_author == '[deleted]') {
			echo "SKipping deleted.\n";
			continue;
		}
		$image_filename = $post_directory_name . "/" . $comment_author . "____".$comment_id.".jpg";

		preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $comment_body, $match);
		if (count($match)>0) {

			if (count($match[0])>1) {
				error_log("Warning: More than one image found in comment. Using the first." );
			}

			if (!file_exists($image_filename)) {
				$img_html_url = $match['0'][0]; // URL of the "image html page" on hosted service. NOT the actual jpg. 
				$img_url = jpg_url_from_service_url($img_html_url);
				if (!$img_url) {
					error_log  ("Err: Could not extract image from: " . $img_html_url);
				}
				else {
					// Download our image and savev to local file
					file_put_contents($image_filename, file_get_contents($img_url));						
				}
				echo "        Downloaded " . $image_filename . "\n";
				// be polite and sleep between requests
				sleep(2);
			}
			else {
				echo "        Existing file " . $image_filename. "\n";
			}

			// add metadata with commenter username
			$exec_cmd = "mogrify -comment '". $comment_author."' '" . $image_filename . "'";
			shell_exec($exec_cmd);

			// record this in our totals -- so jinky!
			global $USER_DATA, $USER_DATA_FILE;
			if (!array_key_exists($comment_author, $USER_DATA)) {
				$USER_DATA[$comment_author] = array();
			}
			array_push($USER_DATA[$comment_author],array("word"=>$wotd, "author"=>$comment_author, "url"=>$post_url, "title"=>$post_title, "timestamp"=>$comment_timestamp));
			if (!file_put_contents($USER_DATA_FILE, serialize($USER_DATA))) {
				print "Problem writing to " . $USER_DATA_FILE;
			}

			global $WORD_DATA, $WORD_DATA_FILE;
			if (!array_key_exists($wotd, $WORD_DATA)) {
				$WORD_DATA[$wotd] = array();
			}
			array_push($WORD_DATA[$wotd], array("author"=>$comment_author, "url"=>$img_url, "post_url"=>$post_url, "timestamp"=>$comment_timestamp));
			if (!file_put_contents($WORD_DATA_FILE, serialize($WORD_DATA))) {
				print "Problem writing to " . $WORD_DATA_FILE;
			}

		}
	}

	// Example montage command:
	// $ montage calligraffiti/2013-12-02T03-06-23Z-Ignite/*.jpg -geometry 300x300+2+2 -tile 2x -background black calligraffiti/montage/calligraffiti/2013-12-02T03-06-23Z-Ignite.jpg
	$montage_img_filename = $montage_directory_name.'/' .  $post_created_date . "-" . sanitize_file_name($wotd,false) . '.jpg';
	if (!file_exists($montage_img_filename)) {
		$exec_cmd =  'montage -fill white  -label \'%c\' '.$post_directory_name.'/*.jpg -gravity South -geometry 300x300+2+2 -tile 3x -background black \'' . $montage_img_filename . "'\n"; 
		shell_exec($exec_cmd);
		if (!file_exists($montage_img_filename)) {
			echo "********* Problem creating montage " . $montage_img_filename . "\n";
		}
	}

	// // SIDEBAR
	// $sidebar_img_filename = $sidebar_directory_name.'/sidebar-' .  $post_created_date . "-" . sanitize_file_name($wotd) . '.jpg';
	// $exec_cmd =  'montage -fill black  -label \'%c\' '.$post_directory_name.'/*.jpg -gravity South -geometry 150x150+2+2 -tile 2x -background white \'' . $sidebar_img_filename . "'\n"; 

	// print $exec_cmd;
	// shell_exec($exec_cmd);
	// if (!file_exists($sidebar_img_filename)) {
	// 	echo "********* Problem creating montage " . $sidebar_img_filename . "\n";
	// }


}

?>