CnCTA
=====

Command and Conquer: Tiberium Alliances Curl PHP class

$cncta = new CnCTA();

// login into Game

$cncta->login($user, $password);

// Get a sessionId

$cncta->LastWorld();

// start a game session

$cncta->OpenSession();


// replace 111 with a valid alliance id

$result = $cncta->prepData('GetPublicAllianceInfo', array('id' => "111"));
