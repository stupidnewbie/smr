<?php declare(strict_types=1);

namespace Smr;

use SmrPlayer;

/**
 * Collection of functions to help display messages and message boxes.
 */
class Messages {

	public static function getMessageTypeNames(int $typeID = null) : array|string {
		$typeNames = [
			MSG_PLAYER => 'Player Messages',
			MSG_PLANET => 'Planet Messages',
			MSG_SCOUT => 'Scout Messages',
			MSG_ALLIANCE => 'Alliance Messages',
			MSG_POLITICAL => 'Political Messages',
			MSG_GLOBAL => 'Global Messages',
			MSG_ADMIN => 'Admin Messages',
			MSG_CASINO => 'Casino Messages',
			MSG_SENT => 'Sent Messages',
		];
		return $typeID === null ? $typeNames : $typeNames[$typeID];
	}

	public static function getAdminBoxNames() : array {
		return [
			BOX_BUGS_AUTO => 'Automatic Bug Reports',
			BOX_BUGS_REPORTED => 'Player Bug Reports',
			BOX_GLOBALS => 'Global Messages',
			BOX_ALLIANCE_DESCRIPTIONS => 'Alliance Descriptions',
			BOX_ALBUM_COMMENTS => 'Photo Album Comments',
			BOX_BARTENDER => 'Bartender Gossip',
		];
	}

	public static function getMessagePlayer(int $accountID, int $gameID, int $messageType = null) : string|SmrPlayer {
		if ($accountID == ACCOUNT_ID_PORT) {
			$return = '<span class="yellow">Port Defenses</span>';
		} else if ($accountID == ACCOUNT_ID_ADMIN) {
			$return = '<span class="admin">Administrator</span>';
		} else if ($accountID == ACCOUNT_ID_PLANET) {
			$return = '<span class="yellow">Planetary Defenses</span>';
		} else if ($accountID == ACCOUNT_ID_ALLIANCE_AMBASSADOR) {
			$return = '<span class="green">Alliance Ambassador</span>';
		} else if ($accountID == ACCOUNT_ID_CASINO) {
			$return = '<span class="yellow">Casino</span>';
		} else if ($accountID == ACCOUNT_ID_FED_CLERK) {
			$return = '<span class="yellow">Federal Clerk</span>';
		} else if ($accountID == ACCOUNT_ID_OP_ANNOUNCE || $accountID == ACCOUNT_ID_ALLIANCE_COMMAND) {
			$return = '<span class="green">Alliance Command</span>';
		} else {
			foreach (Race::getAllNames() as $raceID => $raceName) {
				if ($accountID == ACCOUNT_ID_GROUP_RACES + $raceID) {
					return '<span class="yellow">' . $raceName . ' Government</span>';
				}
			}
			if (!empty($accountID)) {
				$return = SmrPlayer::getPlayer($accountID, $gameID);
			} else {
				$return = match($messageType) {
					MSG_ADMIN => '<span class="admin">Administrator</span>',
					MSG_ALLIANCE => '<span class="green">Alliance Ambassador</span>',
					default => 'Unknown',
				};
			}
		}
		return $return;
	}

}
