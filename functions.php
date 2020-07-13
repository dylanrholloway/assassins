<?php 
function rickroll() {
	header('location: https://www.youtube.com/watch?v=dQw4w9WgXcQ');
	die();
}

//Generate 6 character killcode. Not sure how it works. Suspected black magic. Beware. Returns 6 character killcode.
function generate_killcode() {
	$keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < 6; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

//Generates an md5 hash based on the output of the uniqid function. Returns hash.
function generate_hash(){
	return md5(uniqid(rand(), TRUE));
}

//Gets ids of all players in database. Returns multidimensional array if successful.
function get_all_player_ids($dbh){
	$stmt = $dbh->prepare("SELECT id FROM players;");
	if($stmt->execute()){
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	} else {
		return FALSE;
	}
}

//Marks player as alive = 0. Returns TRUE if 1 row updated. Returns FALSE on all other conditions.
function assassinate($dbh, $player_id) {
	$stmt = $dbh->prepare("UPDATE players SET alive = 0 WHERE id = :player_id;");
	$stmt->bindParam(":player_id", $player_id);
	if($stmt->execute()) {
		if($stmt->rowCount() === 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

//Checks if hash and kill code supplied match. Returns * of matching player if true. Else returns FALSE.
function confirmed_hit($dbh, $killcode, $hash){
	$stmt=$dbh->prepare("SELECT * FROM players WHERE hash = :hash AND killcode = :killcode;");
	$stmt->bindParam(":killcode", $killcode);
	$stmt->bindParam(":hash", $hash);
	$stmt->execute();
	$player = $stmt->fetch(PDO::FETCH_ASSOC);
	if(count($player['id']) === 1){
		return TRUE;
	} else {
		return FALSE;
	}
}

function get_dead_player($dbh, $killcode, $hash){
	$stmt = $dbh->prepare("SELECT * FROM players WHERE hash = :hash AND killcode = :killcode;");
	$stmt->bindParam(":killcode", $killcode);
	$stmt->bindParam(":hash", $hash);
	$stmt->execute();
	$player = $stmt->fetch(PDO::FETCH_ASSOC);
	return $player;
}

//Finds assassin who completed the kill. Marks target as active = 0. Returns assassin_id, nickname and mobile_number.
function complete_contract($dbh, $target){
	$stmt = $dbh->prepare("SELECT p.id AS id, p.mobile_number AS mobile_number, p.nickname AS nickname FROM contracts c JOIN players p ON c.assassin = p.id WHERE c.target = :target AND c.active = 1;");
	$stmt->bindParam(":target", $target);
	$stmt->execute();
	$assassin = $stmt->fetch(PDO::FETCH_ASSOC);

	$stmt = $dbh->prepare("UPDATE contracts c SET c.active = 0 WHERE c.target = :target;");
	$stmt->bindParam(":target", $target);
	$stmt->execute();

	return $assassin;
}

//Marks contract as active = 0. Returns contract id.
function terminate_contract($dbh, $dead_player){
	$stmt = $dbh->prepare("SELECT * FROM contracts WHERE assassin = :dead_player AND active = 1;");
	$stmt->bindParam(":dead_player", $dead_player);
	$stmt->execute();
	$contract_id = $stmt->fetch(PDO::FETCH_ASSOC);

	$stmt = $dbh->prepare("UPDATE contracts SET active = 0 WHERE assassin = :dead_player AND active = 1;");
	$stmt->bindParam(":dead_player", $dead_player);
	$stmt->execute();

	return $contract_id;
}

//Gets target from unfulfilled contract and assigns it to assassin. Returns targets id, nickname and killcode.
function reassign_target($dbh, $assassin, $terminated_contract) {
	//Get target from terminated contract that needs reassignment
	$stmt = $dbh->prepare("SELECT p.id AS id, p.nickname AS nickname FROM contracts c JOIN players p ON c.target = p.id WHERE c.id = :id;");
	$stmt->bindParam(":id", $terminated_contract);
	$stmt->execute();
	$target = $stmt->fetch(PDO::FETCH_ASSOC);

	//Update contracts
	$stmt = $dbh->prepare("INSERT INTO contracts (assassin, target, active) VALUES (:assassin, :target, 1);");
	$stmt->bindParam(":assassin", $assassin);
	$stmt->bindParam(":target", $target['id']);
	$stmt->execute();

	//Change killcode of target
	$killcode = generate_killcode();

	$stmt = $dbh->prepare("UPDATE players SET killcode = :killcode WHERE id = :id AND alive = 1;");
	$stmt->bindParam(":id", $target['id']);
	$stmt->bindParam(":killcode", $killcode);
	$stmt->execute();

	$target['killcode'] = $killcode;

	return $target;
}

//Sends sms. recipient must start with area code eg. 4 for mobiles. 2 for NSW. etc.
function send_sms($recipient, $message, $client) {
	$twilio_number = "+12058904676";
	$recipient = '+61'.$recipient;
	$client->messages->create(
	    // Where to send a text message (your cell phone?)
	    $recipient,
	    array(
	        'from' => $twilio_number,
	        'body' => $message
	    )
	);
}

function endgame($dbh) {
	$stmt = $dbh->prepare("SELECT COUNT(alive) AS count FROM players WHERE alive = 1;");
	$stmt->execute();
	$count = $stmt->fetch(PDO::FETCH_ASSOC);
	if($count['count'] == 1) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function get_all_mobiles($dbh){
	$stmt = $dbh->prepare("SELECT mobile_number FROM players WHERE alive = 0;");
	$stmt->execute();
	$numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $numbers;
}

function game_started($dbh){
	$stmt = $dbh->prepare("SELECT started FROM game;");
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if($result['started'] == 1) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function smite_player($dbh, $nickname){
	$stmt = $dbh->prepare("SELECT * FROM players WHERE nickname = :nickname;");
	$stmt->bindParam(":nickname", $nickname);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	return $result;
}


?>