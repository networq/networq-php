<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PackageTest extends NetworqPHPTestCase
{
    public function testReportsAllPackages(): void
    {
        $packages = $this->getGraph()->getPackages();

        $this->assertInstanceOf(\Networq\Model\Package::class, $packages['example:games']);
        $this->assertCount(1, $packages);
    }
}
