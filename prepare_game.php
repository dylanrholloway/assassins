<?php 
require_once 'glue.php';

$stmt = $dbh->prepare("SELECT mobile_number FROM players;");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $result){
	$message = "Your next briefing is ready. Watch now. https://youtu.be/q_uEM4g4YDo";
	send_sms($result['mobile_number'], $message, $client);
}

echo "The game is afoot...";
?>