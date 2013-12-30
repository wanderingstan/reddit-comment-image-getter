<?php

$user_data = unserialize(file_get_contents("word_data.serialized"));

// print_r($user_data);

foreach ($user_data as $word=>$data) {
	print "[" . $word . "]" . "(" . $data[0]['post_url'] . ") - ";

	// print "  ";
	// foreach ($data as $submission) {
	// 	print $submission['author']." ";
	// }
	// print "\n";
}

?>