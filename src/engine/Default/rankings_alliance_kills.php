<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Alliance Kill Rankings');
Menu::rankings(1, 2);

$rankedStats = Rankings::allianceStats('kills', $player->getGameID());

$ourRank = 0;
if ($player->hasAlliance()) {
	$ourRank = Rankings::ourRank($rankedStats, $player->getAllianceID());
	$template->assign('OurRank', $ourRank);
}

$template->assign('Rankings', Rankings::collectAllianceRankings($rankedStats, $player));

$numAlliances = count($rankedStats);
list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $numAlliances);

$template->assign('FilteredRankings', Rankings::collectAllianceRankings($rankedStats, $player, $minRank, $maxRank));

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_alliance_kills.php')->href());
