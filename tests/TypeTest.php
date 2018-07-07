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
        $actual1 = [
            "example:games:platform",
            "example:games:character",
            "example:games:game",
            "example:games:url",
            "example:games:base"
        ];
        $expected1 = array_keys($this->types);

        sort($expected1) && sort($actual1);

        $this->assertEquals($expected1, $actual1);

        $expected2 = ["example:games:platform", "example:games:character", "example:games:game", "example:games:url", "example:games:base"];
        $actual2 = array_map(function($type) { return $type->getFqtn(); }, array_values($this->types));

        sort($expected2) && sort($actual2);

        $this->assertEquals($expected2, $actual2);
    }

    public function testScheduleEqualsExpected() {
        $this->assertEquals([
            "example:games:platform" => [
                "manufacturer" => "string"
            ],
            "example:games:character" => [
                "debut" => "example:games:game",
                "games" => "example:games:game[]"
            ],
            "example:games:game" => [
                "publisher" => "string",
                "platform" => "example:games:platform",
                "characters" => "example:games:character[]",
                "urls" => "example:games:url[]"
            ],
            "example:games:url" => [
                "target" => "string"
            ],
            "example:games:base" => [
                "name" => "string",
                "image" => "string",
                "description" => "string"
            ]], $this->getTypeSchedule($this->types)
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
