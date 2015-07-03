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

function fetchQuestion($qid, $db) {
	$filter = "!)rcjzniPuafk4WNG65yr";
	$data = apiCall("questions/$qid?order=desc&sort=activity", 'codereview', $filter);
	$json = json_decode($data, true);
	$question = $json['items'][0];
	$dbfields = array("is_answered", "view_count", "favorite_count", "answer_count", "score", "accepted_answer_id");
	
	$sql = 'INSERT INTO cr_badge (question_id, is_answered, favorite_count, answer_count, view_count, score, fetch_time, accepted_answer_id) ' .
		'VALUES (:qid, :is_answered, :favorite_count, :answer_count, :view_count, :score, :time, :accepted_answer_id) ON DUPLICATE KEY UPDATE ' .
		'is_answered = :is_answered, favorite_count = :favorite_count, answer_count = :answer_count, view_count = :view_count, score = :score, fetch_time = :time, accepted_answer_id = :accepted_answer_id ;';
	$stmt = $db->prepare($sql);
	$sql_params = array();
	foreach ($dbfields as $field_name) {
		if (isset($question[$field_name])) {
			$sql_params[':' . $field_name] = $question[$field_name];
		} else {
			$sql_params[':' . $field_name] = 0;
		}
	}
	$sql_params[':qid'] = $qid;
	$sql_params[':time'] = time();
	$result = $stmt->execute($sql_params);
	if ($result) {
		useData($question);
	} else {
		die($stmt->errorInfo());
	}
	return $json;
}

function useData($data) {
	header('Content-type: image/svg+xml; charset=utf-8');
	$is_answered = $data['text'];
	$text = 'reviewed';
	if (isset($data['accepted_answer_id']) && $data['accepted_answer_id'] != 0) {
		$color = '97ca00';
		$mode = 'views';
	} elseif ($data['answer_count'] >= 1) {
		$color = 'ff8000';
		$right = $data['score'] . ' score';
		$mode = 'answers';
	} else {
		$color = 'e05d44';
		$text = 'reviewing';
		$mode = 'score';
	}
	if (isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}
	$data['answers'] = $data['answer_count'];
	$data['views'] = $data['view_count'];
	$right = $data[$mode] . ' ' . $mode;
	
	$svg = <<<END
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
<path fill="#$color" d="M62 0h75v20H62z"/>
<path fill="url(#b)" d="M0 0h137v20H0z"/>
</g>
<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">
<text x="31" y="15" fill="#010101" fill-opacity=".3">$text</text>
<text x="31" y="14">$text</text>
<text x="98.5" y="15" fill="#010101" fill-opacity=".3">$right</text>
<text x="98.5" y="14">$right</text>
</g>
</svg>
END;
	echo $svg;
}

function dbOrAPI($qid, $db) {
	
	$sql = 'SELECT is_answered, favorite_count, answer_count, view_count, score, fetch_time, accepted_answer_id FROM cr_badge WHERE question_id = :qid;';

	$stmt = $db->prepare($sql);
	$result = $stmt->execute(array(':qid' => $qid));
	if ($result) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$time = $row['fetch_time'];
		if ($time < time() - 3600) { // if time was updated more than one hour ago
			// fetch data again
			fetchQuestion($qid, $db);
		} else {
			useData($row);
		}
	} else {
		print_r($stmt->errorInfo());
	}
}

if (isset($_GET['qid'])) {
	$qid = $_GET['qid'];
} else {
	die("No qid set");
}

try {
	$db = new PDO($dbhostname, $dbuser, $dbpass);
} catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
	return false;
}

dbOrAPI($qid, $db);
