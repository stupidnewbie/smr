<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!is_numeric($var['PickedAccountID'])) {
	create_error('You have to pick a player.');
}

require_once(LIB . 'Draft/alliance_pick.inc.php');
$teams = get_draft_teams($player->getGameID());
if (!$teams[$player->getAccountID()]['CanPick']) {
	create_error('You have to wait for others to pick first.');
}
$pickedPlayer = SmrPlayer::getPlayer($var['PickedAccountID'], $player->getGameID());

if ($pickedPlayer->isDraftLeader()) {
	create_error('You cannot pick another leader.');
}

if ($pickedPlayer->hasAlliance()) {
	if ($pickedPlayer->getAlliance()->isNHA()) {
		$pickedPlayer->leaveAlliance();
	} else {
		create_error('Picked player already has an alliance.');
	}
}

// assign the player to the current alliance
$pickedPlayer->joinAlliance($player->getAllianceID());

// move the player to the alliance home sector if not using traditional HQ's
if ($pickedPlayer->getSectorID() === 1) {
	$pickedPlayer->setSectorID($pickedPlayer->getHome());
	$pickedPlayer->getSector()->markVisited($pickedPlayer);
}

$pickedPlayer->update();

// Update the draft history
$db = Smr\Database::getInstance();
$db->insert('draft_history', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'leader_account_id' => $db->escapeNumber($player->getAccountID()),
	'picked_account_id' => $db->escapeNumber($pickedPlayer->getAccountID()),
	'time' => $db->escapeNumber(Smr\Epoch::time()),
]);

Page::create('skeleton.php', 'alliance_pick.php')->go();
