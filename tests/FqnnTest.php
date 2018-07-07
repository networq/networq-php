<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Networq\Model\Fqnn;

final class FqnnTest extends NetworqPHPTestCase
{
    public function testFqnnConstructor(): void
    {
        $fqnn = Fqnn::byFqnn('networq:example:hello');
        $this->assertEquals($fqnn->getOwner(), 'networq');
        $this->assertEquals($fqnn->getPackage(), 'example');
        $this->assertEquals($fqnn->getName(), 'hello');
    }

    public function testFqnnException(): void
    {
        $this->expectException(RuntimeException::class);
        $fqnn = Fqnn::byFqnn('invalid fqnn'); // fqnn should contain 3 parts seperated by a `:`
    }

}
