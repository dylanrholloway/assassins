# assassins
This is the repo for the assassins game. This is a more high tech version of the classic 'Murder winks' party game. 

Gameplay
All players (assassins) wear a hat with a QR code on it. Assassins are assigned a target and a killcode, which is shared with them via text message. When a player scans their targets QR code and enters the killcode, that target is assassinated and out of the game. The assassin is then assigned a new target and killcode, and so the game continues until only one assassin is left. 

Installation
Copy all files to the '/assassinate' directory. You will need to sign up for a Twilio account to use the SMS functionality. Add a new file called 'env.php' and add your database connection variables and Twilio authorisation codes there. Use the 'database.sql' to create the tables in your database.

Setup
You will need to manually enter the details for your players in the 'players' table. You can use the 'generate_killcode()' and 'generate_hash()' functions to create an initial hash and killcode for each player. I used https://developers.google.com/chart/infographics/docs/qr_codes to create QR codes for each player that directed to www.yourURL.com/assassinate?h={player_hash}. Once each player has been entered into the database, has a hash, killcode and related QR code you're good to go.

Start game
Populate the contracts table by running the issue_contracts.php script. NB: there is no validation or security on this script. Running it will reissue new contracts and potentially break or restart your game. Do not refresh your page or load it again while the game is running.

(Optional) Send your targets a briefing text with the prepare_game.php script.

Start the game with the start_game.php script. You will need to set the 'pw' GET variable to run this (currently set to bruce). This is to stop the game accidentally being run twice.

After this the game is running! Go scan your targets and assassinate your friends.

To reboot the game when it is finished you will need to mark all players as 'alive' again and reissue new contracts.
