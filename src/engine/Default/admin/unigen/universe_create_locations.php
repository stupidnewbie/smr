<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$session->getRequestVarInt('gal_on');
$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($var['game_id']));

$container = Page::create('skeleton.php', 'admin/unigen/universe_create_locations.php');
$container->addVar('game_id');
$template->assign('JumpGalaxyHREF', $container->href());

$locations = SmrLocation::getAllLocations();

// Initialize all location counts to zero
$totalLocs = [];
foreach ($locations as $location) {
	$totalLocs[$location->getTypeID()] = 0;
}

// Determine the current amount of each location
$galSectors = SmrSector::getGalaxySectors($var['game_id'], $var['gal_on']);
foreach ($galSectors as $galSector) {
	foreach ($galSector->getLocations() as $sectorLocation) {
		$totalLocs[$sectorLocation->getTypeID()]++;
	}
}
$template->assign('TotalLocs', $totalLocs);

$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
$template->assign('Galaxy', $galaxy);

// Though we expect a location to be only in one category, it is possible to
// edit a location in the Admin Tools so that it is in two or more categories.
// For simplicity here, it will only show up in the first category it matches,
// but it will identify all other categories that it is in.
// If multi-category locations becomes common, this code should be modified.
class Categories {

	public array $locTypes = [];
	private array $locAdded = []; // list of locs added to a category
	public function addLoc(int $locID, string $category): string {
		if ($this->added($locID)) {
			return "<b>Also in $category</b><br />";
		}
		$this->locTypes[$category][] = $locID;
		$this->locAdded[] = $locID;
		return '';
	}
	public function added(int $locID): bool {
		return in_array($locID, $this->locAdded);
	}

}

// Remove any linked locations, as they will be added automatically
// with any corresponding HQs.
foreach ($locations as $location) {
	foreach ($location->getLinkedLocations() as $linkedLoc) {
		unset($locations[$linkedLoc->getTypeID()]);
	}
}

// Set any extra information to be displayed with each location
$locText = [];
$categories = new Categories();
foreach ($locations as $location) {
	$extra = '<span class="small"><br />';
	if ($location->isWeaponSold()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Weapons');
		foreach ($location->getWeaponsSold() as $weapon) {
			$extra .= $weapon->getName() . '&nbsp;&nbsp;&nbsp;(' . $weapon->getShieldDamage() . '/' . $weapon->getArmourDamage() . '/' . $weapon->getBaseAccuracy() . ')<br />';
		}
	}
	if ($location->isShipSold()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Ships');
		foreach ($location->getShipsSold() as $shipSold) {
			$extra .= $shipSold->getName() . '<br />';
		}
	}
	if ($location->isHardwareSold()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Hardware');
		foreach ($location->getHardwareSold() as $hardware) {
			$extra .= $hardware['Name'] . '<br />';
		}
	}
	if ($location->isBar()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Bars');
	}
	if ($location->isBank()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Banks');
	}
	if ($location->isHQ() || $location->isUG() || $location->isFed()) {
		$extra .= $categories->addLoc($location->getTypeID(), 'Headquarters');
		foreach ($location->getLinkedLocations() as $linkedLoc) {
			$extra .= $linkedLoc->getName() . '<br />';
		}
	}
	if (!$categories->added($location->getTypeID())) {
		// Anything that doesn't fit the other categories
		$extra .= $categories->addLoc($location->getTypeID(), 'Miscellaneous');
	}
	$extra .= '</span>';

	$locText[$location->getTypeID()] = $location->getName() . $extra;
}
$template->assign('LocText', $locText);
$template->assign('LocTypes', $categories->locTypes);

// Form to make location changes
$container = Page::create(
	'admin/unigen/universe_create_save_processing.php',
	'admin/unigen/universe_create_sectors.php',
	$var
);
$template->assign('CreateLocationsFormHREF', $container->href());

// HREF to cancel and return to the previous page
$container = Page::create('skeleton.php', 'admin/unigen/universe_create_sectors.php', $var);
$template->assign('CancelHREF', $container->href());
