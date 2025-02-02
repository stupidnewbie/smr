<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$start = Smr\Request::getVarInt('from');
$target = Smr\Request::getVarInt('to');

// perform some basic checks on both numbers
if (empty($start) || empty($target)) {
	create_error('Where do you want to go today?');
}

if ($start == $target) {
	create_error('Hmmmm...if ' . $start . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');
}

try {
	$startSector = SmrSector::getSector($player->getGameID(), $start);
	$targetSector = SmrSector::getSector($player->getGameID(), $target);
} catch (Smr\Exceptions\SectorNotFound) {
	create_error('The sectors have to exist!');
}

$player->log(LOG_TYPE_MOVEMENT, 'Player plots to ' . $target . '.');

$path = Plotter::findReversiblePathToX($targetSector, $startSector, true);

// common processing
require('course_plot_processing.inc.php');
