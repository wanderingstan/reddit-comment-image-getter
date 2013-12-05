<?php

function download_image_from_service($url,$filename) {
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
		$img_page_html = file_get_contents($url);
		// <meta property="og:image" content="http://distilleryimage3.ak.instagram.com/8e78fe2e531611e3abd4129eb955129a_8.jpg" />
		$pattern = '/\<meta property="og:image" content="([^\"]*\.jpg)/';
		preg_match($pattern, $img_page_html, $matches, PREG_OFFSET_CAPTURE);
		$img_url = $matches[1][0];
		print_r($matches);
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

?>
