<?php

/**
 * Given a URL on an image hosting service (Imgur, twitter, Instagram),
 * this function will attempt to return the URL of the acutal image jpg. 
 * E.g. 
 *     http://instagram.com/p/g6wdIeP_L2
 * returns
 *     http://distilleryimage6.ak.instagram.com/694252de518311e396e312b3099bf222_8.jpg
 */
function jpg_url_from_service_url($url) {
	$url_data = parse_url($url);
	$domain = $url_data['host'];	

	if ($domain == "i.imgur.com") {
		// Imgur: Already a direct link to image. Easy.
		$img_url = "http://i.imgur.com/" . $url_data['path'];
	}
	elseif ($domain == "imgur.com") {
		// Imgur: reform URL to get jpg
		// e.g. http://imgur.com/udjf8uT --> http://i.imgur.com/udjf8uT.jpg
		$img_url = "http://i.imgur.com/" . $url_data['path'] . ".jpg";
	}
	elseif ($domain == "instagram.com") {
		// Instagram: find URL in meta tag
		// e.g. <meta property="og:image" content="http://distilleryimage3.ak.instagram.com/8e78fe2e531611e3abd4129eb955129a_8.jpg" />
		$img_page_html = file_get_contents($url);
		$pattern = '/\<meta property="og:image" content="([^\"]*\.jpg)/';
		preg_match($pattern, $img_page_html, $matches, PREG_OFFSET_CAPTURE);
		$img_url = $matches[1][0];
	}
	elseif ($domain == "twitter.com") {
		// Twitter: Find URL at special doman
		// e.g. https://pbs.twimg.com/media/BavEMwpCIAA8o4N.jpg
		$img_page_html = file_get_contents($url);
		$pattern = "/https:\/\/pbs\\.twimg\.com\/media\/[^\.]*\.jpg/";
		preg_match($pattern, $img_page_html, $matches, PREG_OFFSET_CAPTURE);
		$img_url = $matches[0][0];
	}
	else {
		error_log("jpg_url_from_service_url: Unrecognized photo sharing service: " . $url );
		return;				
	}

	if (!$img_url) {
		// We don't know what to do with this URL
		error_log("jpg_url_from_service_url: Could not extract an image url from page at " . $url );		
	}

	return $img_url;
}

?>
