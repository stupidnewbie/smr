<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use SmrScoutDrones;

/**
 * @covers SmrScoutDrones
 */
class SmrScoutDronesTest extends TestCase {

	public function test_getMaxDamage(): void {
		$sds = new SmrScoutDrones(100); // doesn't matter how many
		$this->assertSame(20, $sds->getMaxDamage());
	}

}
