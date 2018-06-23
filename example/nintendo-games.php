<?php

require_once 'common.php';

$games = $graph->getType('example:games:game')->getNodesWhere('example:games:game', 'publisher', 'Nintendo');
echo "Nintendo games: " . count($games) . PHP_EOL;
foreach ($games as $game) {
    echo " * " . $game->getFqnn() . ' (' . $game->getPropertyValue('example:games:base', 'name') . ')' . PHP_EOL;
}
