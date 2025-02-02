<?php declare(strict_types=1);
try {
	require_once('../bootstrap.php');

	$session = Smr\Session::getInstance();

	$gameID = Smr\Request::getInt('game');
	if (!$session->hasAccount() || !SmrGame::gameExists($gameID)) {
		header('Location: /login.php');
		exit;
	}

	$account = $session->getAccount();
	if (!SmrGame::getGame($gameID)->isEnabled() && !$account->hasPermission(PERMISSION_UNI_GEN)) {
		header('location: /error.php?msg=You do not have permission to view this map!');
		exit;
	}

	$nodes = [];
	$links = [];

	// The d3 graph nodes are the galaxies
	foreach (SmrGalaxy::getGameGalaxies($gameID) as $galaxy) {
		$nodes[] = [
			'name' => $galaxy->getName(),
			'id' => $galaxy->getGalaxyID(),
			'group' => array_search($galaxy->getGalaxyType(), SmrGalaxy::TYPES),
			'size' => $galaxy->getSize(),
		];
	}

	// The d3 graph links are the warp connections between galaxies
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT sector_id, warp FROM sector WHERE warp !=0 AND game_id = ' . $db->escapeNumber($gameID));
	foreach ($dbResult->records() as $dbRecord) {
		$warp1 = SmrSector::getSector($gameID, $dbRecord->getInt('sector_id'));
		$warp2 = SmrSector::getSector($gameID, $dbRecord->getInt('warp'));
		$links[] = [
			'source' => $warp1->getGalaxy()->getName(),
			'target' => $warp2->getGalaxy()->getName(),
		];
	}

	// Encode the data for use in the javascript
	$data = json_encode([
		'nodes' => $nodes,
		'links' => $links,
	]);

} catch (Throwable $e) {
	handleException($e);
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE . ': ' . SmrGame::getGame($gameID)->getName(); ?></title>
		<meta charset="utf-8">
		<style>
		body { background-image: url("images/stars2.png"); }
		</style>
	</head>

	<body>
		<script src="https://d3js.org/d3.v7.min.js"></script>
		<script src="<?php echo JQUERY_URL; ?>"></script>
		<script>
			const graph = <?php echo $data; ?>;
		</script>
		<script src="/js/map_warps.js"></script>
	</body>
</html>
