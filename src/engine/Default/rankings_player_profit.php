<?php declare(strict_types=1);

$template->assign('PageTopic', 'Profit Rankings');

Menu::rankings(0, 1);

$profitType = array('Trade', 'Money', 'Profit');
$profitTypeEscaped = $db->escapeArray($profitType, ':', false);

// what rank are we?
$db->query('SELECT count(*)
			FROM player p
			LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . '
			WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND (
				amount > '.$db->escapeNumber($player->getHOF($profitType)) . '
				OR (
					COALESCE(amount,0) = '.$db->escapeNumber($player->getHOF($profitType)) . '
					AND player_name <= ' . $db->escapeString($player->getPlayerName()) . '
				)
			)');
$db->requireRecord();
$ourRank = $db->getInt('count(*)');
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$profitRanks = function (int $minRank, int $maxRank) use ($player, $db, $profitTypeEscaped) : array {
	$offset = $minRank - 1;
	$limit = $maxRank - $offset;
	$db->query('SELECT p.*, COALESCE(ph.amount,0) amount FROM player p LEFT JOIN player_hof ph ON p.account_id = ph.account_id AND p.game_id = ph.game_id AND ph.type = ' . $profitTypeEscaped . ' WHERE p.game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY amount DESC, player_name ASC LIMIT ' . $offset . ', ' . $limit);
	return Rankings::collectRankings($db, $player, $offset);
};

$template->assign('Rankings', $profitRanks(1, 10));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_profit.php')));

$template->assign('FilteredRankings', $profitRanks($minRank, $maxRank));
