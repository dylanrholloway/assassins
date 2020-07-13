<?php 
require_once 'glue.php';

if(!game_started($dbh)){
	rickroll();
	die();
}

if($_SERVER['REQUEST_METHOD'] === "GET") {
	if(isset($_GET['h'])) {
		include_once 'enter_killcode.php';
		die();
	} else {
		rickroll();
		die();
	}
} elseif($_SERVER['REQUEST_METHOD'] === "POST") {
	if(confirmed_hit($dbh, $_POST['killcode'], $_POST['h'])) {
		//hit
		$dead_player = get_dead_player($dbh, $_POST['killcode'], $_POST['h']);

		//Kill the player.
		assassinate($dbh, $dead_player['id']);

		//Payout assassin who completed the contract.
		$assassin = complete_contract($dbh, $dead_player['id']);
		//Terminate the dead_players contract. Return contract ID for reassignment
		$terminated_contract = terminate_contract($dbh, $dead_player['id']);

		//SMS target. Tell them they dead
		send_sms($dead_player['mobile_number'], $dead_player['nickname'].', you have been assassinated. Your services are no longer required. Goodbye.', $client);

		if(!endgame($dbh)){
			//Assign dead assassins target to killer
			$target = reassign_target($dbh, $assassin['id'], $terminated_contract['id']);

			//SMS assassin tell them next target
			$message = "Well done Agent ".$assassin['nickname'].". Your next target is ".$target['nickname'].'. Your killcode is '.$target['killcode'].". Good luck.";
			send_sms($assassin['mobile_number'], $message, $client);

			return json_encode(['result'=>"Kill confirmed. Further instructions will be sent to you."]);

		} else {
			//endgame
			//SMS assassin. Tell them they won.
			$message = "Well done Agent ".$assassin['nickname'].". You have eliminated all hostile targets and the mission is over. Debrief now: https://youtu.be/nyaXtKfKsa0";
			send_sms($assassin['mobile_number'], $message, $client);

			//SMS Dylan. Tell him who won.
			send_sms('423106850', $assassin['nickname'].' has won the game. Debrief: https://youtu.be/nyaXtKfKsa0', $client);
			
			//SMS everyone. Tell them game over. 
			$mobiles = get_all_mobiles($dbh);

			foreach ($mobiles as $mobile){
				send_sms($mobile['mobile_number'], "The mission is over. Better luck next time. Debrief now: https://youtu.be/nyaXtKfKsa0", $client);
			}

			//return win message
			return json_encode(['result'=>"Kill confirmed. Further instructions will be sent to you."]);
		}
	} else {
		return json_encode(['result'=>"You missed"]);
		die();
	}
} else {
	rickroll();
}