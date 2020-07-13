<?php 
require_once 'glue.php';

$stmt = $dbh->prepare("SELECT id FROM players;");
$stmt->execute();
$assassins = $stmt->fetchAll(PDO::FETCH_ASSOC);

shuffle($assassins);

$targets = $assassins;
$last_target = array_pop($targets);
array_unshift($targets, $last_target);

$contracts = [];

for ($i=0; $i < $assassins; $i++) { 
	$contracts[$i]['assassin'] = $assassins[$i];
	$contracts[$i]['target'] = $targets[$i];
}

foreach ($contracts as $contract) {
	$stmt = $dbh->prepare("INSERT INTO contracts ('assassin', 'target', 'active') VALUES ('".$contract['assassin']."', '".$contract['target']."', '1');");
	$stmt->execute();
}

?>