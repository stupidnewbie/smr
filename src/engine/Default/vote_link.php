<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$container = Page::create('skeleton.php', 'current_sector.php');

$player = $session->getPlayer();
if ($player->getGame()->hasStarted()) {
	// Allow vote
	Smr\VoteSite::getSite($var['link_id'], $player->getAccountID())->setLinkClicked();
	$voting = '<b><span class="red">v</span>o<span class="blue">t</span><span class="red">i</span>n<span class="blue">g</span></b>';
	$container['msg'] = "Thank you for $voting! You will receive bonus turns once your vote is processed.";
} else {
	create_error('You cannot gain bonus turns until the game has started!');
}

$container->go();
