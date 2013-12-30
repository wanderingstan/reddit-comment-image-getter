<?php

$user_data = unserialize(file_get_contents("user_data.serialized"));


function x($a, $b)
{
    if (count($a) == count($b))
    {
        return 0;
    }
    else if (count($a) > count($b))
    {
        return -1;
    }
    else {
        return 1;
    }
}

uasort($user_data, "x");

// print_r($user_data);

$out = "";
foreach ($user_data as $username=>$data) {
	$out.= "* **/u/" . $username . "** ";

	$out .= "" . count($data) . " Posts";
	// foreach ($data as $submission) {
	// 	$out .= "[" . $submission['word']."](" . $submission['url'] . ")" . " ";
	// }
	$out.= "\n";
}

print $out;

?>