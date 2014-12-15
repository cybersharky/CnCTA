CnCTA

// Credit goes to https://github.com/ozon/cncta_php, for helping me understand what I needed to do

Command and Conquer: Tiberium Alliances Curl PHP class

$cncta = CnCTA::getInstance();

// login into Game

$cncta->login($user, $password); //your game username and password

// Get a sessionId

$cncta->LastWorld();

// start a game session

$cncta->OpenSession();


// replace 111 with a valid alliance id

$result = $cncta->prepData('GetPublicAllianceInfo', array('id' => "111"));
