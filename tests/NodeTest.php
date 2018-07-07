<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NodeTest extends NetworqPHPTestCase
{
    protected $games;

    protected function setUp()
    {
        $this->games = $this->getGraph()
                            ->getType('example:games:game')
                            ->getNodesWhere('example:games:game', 'publisher', 'Nintendo');
    }

    public function testReportsAllTypes(): void
    {
        
        $this->assertCount(3, $this->games);
        $this->assertContainsOnlyInstancesOf(\Networq\Model\Node::class, $this->games);

        $actual = [
            ['example:games:smb', 'Super Mario Bros'],
            ['example:games:dk', 'Donkey Kong'],
            ['example:games:loz', 'The Legend of Zelda'],
        ];
        $expected = array_map(function($game) {
            return [$game->getFqnn(), $game->getPropertyValue('example:games:base', 'name')];
        }, $this->games);

        sort($actual) && sort($expected);

        $this->assertEquals($expected, $actual);
    }
}
