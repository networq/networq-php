<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TypeTest extends NetworqPHPTestCase
{
    protected $types;

    protected function setUp()
    {
        $this->types = $this->getGraph()->getTypes();
    }

    public function testReportsAllTypes(): void
    {
        $this->assertCount(5, $this->types);
        $this->assertContainsOnlyInstancesOf(\Networq\Model\Type::class, $this->types);
    }

    public function testContainsCorrectTypes() {
        $this->assertEquals(["platform", "character", "game", "url", "base"], array_keys($this->types));
        $this->assertEquals(
            ["example:games:platform", "example:games:character", "example:games:game", "example:games:url", "example:games:base"],
            array_map(function($type) { return $type->getFqtn(); }, array_values($this->types))
        );
    }

    public function testScheduleEqualsExpected() {
        $this->assertEquals([
                "platform" => [
                    "manufacturer" => "string"
                ],
                "character" => [
                    "debut" => "example:games:game",
                    "games" => "example:games:game[]"
                ],
                "game" => [
                    "publisher" => "string",
                    "platform" => "example:games:platform",
                    "characters" => "example:games:character[]",
                    "urls" => "example:games:url[]"
                ],
                "url" => [
                    "target" => "string"
                ],
                "base" => [
                    "name" => "string",
                    "image" => "string",
                    "description" => "string"
                ],
            ],
            $this->getTypeSchedule($this->types)
        );
    }

    private function getTypeSchedule($types) {
        return array_map(function($type) {
            return array_reduce($type->getFields(), function($carry, $field) {
                $carry[$field->getName()] = $field->getType();
                return $carry;
            });
        }, $types);
    }
}
