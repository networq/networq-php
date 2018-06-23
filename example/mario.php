<?php

require_once 'common.php';

$node = $graph->getNode('example:games:mario');

echo $node->getFqnn() . PHP_EOL;

foreach ($node->getTags() as $tag) {
    echo '   ' . $tag->getType()->getFqtn() . PHP_EOL;
    foreach ($tag->getProperties() as $property) {
        echo '      ' . $property->getField()->getName() . '=';
        $value = $property->getValue();
        if (is_array($value)) {
            foreach ($value as $v) {
                echo '[' . $v . '] ';
            }
        } else {
            echo $value;
        }
        echo PHP_EOL;
    }
}

if (!$node->hasTag('example:games:character')) {
    echo "Not a character" . PHP_EOL;
} else {

    $tag = $node->getTag('example:games:character');

    $games = $graph->getType('example:games:game')->getNodesWhere('example:games:game', 'characters', $node->getFqnn());
    echo "Plays in games: " . count($games) . PHP_EOL;
    foreach ($games as $game) {
        echo " * " .
            $game->getFqnn() .
            ' (' . $game['example:games:base']['name'] . ') ' .
            $game['example:games:game']['platform']['example:games:base']['name'] . PHP_EOL;

        // echo " * " . $game->getFqnn() . ' (' . $game->getPropertyValue('example:games:base', 'name') . ') ' . PHP_EOL;
    }

}
