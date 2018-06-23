<?php

require_once 'common.php';

$types = $graph->getTypes();
echo "Types: " . count($types) . PHP_EOL;
foreach ($types as $type) {
    echo " * " . $type->getFqtn() . PHP_EOL;
    foreach ($type->getFields() as $field) {
        echo '    - ' . $field->getName() . ' (' . $field->getType() . ')' . PHP_EOL;
    }
}
