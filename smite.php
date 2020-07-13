<?php 
require_once 'glue.php';
if(!isset($_GET['nickname']) || !isset($_GET['pw']) || $_GET['pw'] != "bruce"){
	rickroll();
	die();
} else {

		$dead_player = smite_player($dbh, $_GET['nickname']);

		//Kill the player.
		assassinate($dbh, $dead_player['id']);

		//Payout assassin who completed the contract.
		$assassin = complete_contract($dbh, $dead_player['id']);
		//Terminate the dead_players contract. Return contract ID for reassignment
		$terminated_contract = terminate_contract($dbh, $dead_player['id']);

		//SMS target. Tell them they dead
		send_sms($dead_player['mobile_number'], $dead_player['nickname'].', you have been smited for breaking the rules. We told you we would be watching. Your services are no longer required. Goodbye.', $client);

		if(!endgame($dbh)){
			//Assign dead assassins target to killer
			$target = reassign_target($dbh, $assassin['id'], $terminated_contract['id']);

			//SMS assassin tell them next target
			$message = "Agent ".$assassin['nickname'].", your previous contract has been cancelled. Your next target is ".$target['nickname'].'. Your killcode is '.$target['killcode'].". Good luck.";
			send_sms($assassin['mobile_number'], $message, $client);
			print "Consider it done.";
		} else {
			//endgame
			//SMS assassin. Tell them they won.
			$message = "Well done Agent ".$assassin['nickname'].". You have eliminated all hostile targets and the mission is over. View your debriefing here. https://youtu.be/nyaXtKfKsa0";
			send_sms($assassin['mobile_number'], $message, $client);

			//SMS Dylan. Tell him who won.
			send_sms('423106850', $assassin['nickname'].' has won the game.', $client);
			
			//SMS everyone. Tell them game over. 
			$mobiles = get_all_mobiles($dbh);

			foreach ($mobiles as $mobile){
				send_sms($mobile['mobile_number'], "The mission is over. Better luck next time. Debrief now: https://youtu.be/nyaXtKfKsa0", $client);
			}

			//return win message
			return json_encode(['result'=>"Kill confirmed. Further instructions will be sent to you."]);
		}
}
?>