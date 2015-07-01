<?php

require 'conf.php';

function buildURL($apiCall, $site, $filter, $apiKey) {
	if (strpos($apiCall, '?') === false) {
		$apiCall = $apiCall + "?dummy";
	}
	return "https://api.stackexchange.com/2.2/" . $apiCall
				. "&site=" . $site
				. "&filter=" . $filter . "&key=" . $apiKey;
}

function apiCall($apiCall, $site, $filter) {
	global $apiKey;
	$url = buildURL($apiCall, $site, $filter, $apiKey);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	
	if ($result === false) {
		$error = curl_error($ch);
		curl_close($ch);
		throw new Exception("Error calling Stack Exchange API: $error");
	}
	curl_close($ch);
	return $result;
}

function fetchQuestion($qid) {
	$filter = "!)rcjzniPuafk4WNG65yr";
	$data = apiCall("questions/$qid?order=desc&sort=activity", 'codereview', $filter);
	$json = json_decode($data, true);
	var_dump($json);
	return $json;
}

fetchQuestion(79408);

//header('Content-type: image/svg+xml; charset=utf-8');

/*try {
    $db = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}*/

if (isset($_GET['qid'])) {
	$qid = $_GET['qid'];
	
/*	$sql = 'INSERT INTO cr_badge (question_id, is_answered, favorite_count, answer_count, view_count, score, fetch_time) ' .
		'VALUES (:qid, :is_answered, :favorite_count, :answer_count, :view_count, :score, :time);';
	$stmt = $db->prepare($sql);
	$result = $stmt->execute(array(':qid' => $qid, ':is_answered' => $is_answered));
	if ($result) {
		echo "OK";
	} else {
		echo "failed";
		print_r($stmt->errorInfo());
	}*/
} else {
	echo "invalid";
}



function reviewed_answers_green() {
	$answers = $_GET['answers'];
	$text = $_GET['text'];
    return <<<END
<svg xmlns="http://www.w3.org/2000/svg" width="137" height="20">
<linearGradient id="b" x2="0" y2="100%">
<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
<stop offset="1" stop-opacity=".1"/>
</linearGradient>
<mask id="a">
<rect width="137" height="20" rx="3" fill="#fff"/>
</mask>
<g mask="url(#a)">
<path fill="#555" d="M0 0h62v20H0z"/>
<path fill="#e05d44" d="M62 0h75v20H62z"/>
<path fill="url(#b)" d="M0 0h137v20H0z"/>
</g>
<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">
<text x="31" y="15" fill="#010101" fill-opacity=".3">$text</text>
<text x="31" y="14">$text</text>
<text x="98.5" y="15" fill="#010101" fill-opacity=".3">$answers</text>
<text x="98.5" y="14">$answers</text>
</g>
</svg>
END;
}

//echo reviewed_answers_green();
