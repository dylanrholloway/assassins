<?php 
require_once 'glue.php';
$stmt = $dbh->prepare("UPDATE game SET started = 1;");
$stmt->execute();

$stmt = $dbh->prepare("SELECT pa.mobile_number AS mobile_number, pa.nickname AS assassin, pt.nickname AS target, pt.killcode AS killcode
FROM contracts c 
JOIN players pa ON c.assassin = pa.id
JOIN players pt ON c.target = pt.id
WHERE c.active = 1;");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $result){
	$message = "Scan your targets QR code. Enter the kill code. Repeat. Protect your own QR code, but remember the rules. No hiding your QR code. No damaging yours or others QR codes. All violations of the rules are fatal. We are watching.";
	send_sms($result['mobile_number'], $message, $client);
		$message = "Agent ".$result['assassin'].", your target is ".$result['target'].". Your killcode is ".$result['killcode'].". Good luck.";
	send_sms($result['mobile_number'], $message, $client);
}

echo "The game is afoot...";
?>