<?php
/**
 * entity-diagrams.php
 *
 * Optional manual config to create custom groupings of sections/category groups/globals/user groups/tag groups/asset volumes/products
 * to create entity diagrams of related parts of the system
 */

use craft\helpers\App;

return [
    'docGroups' => [ // Create predefined groups of elements to show in a diagram, 'name' => config array
    ],
    'options' => [ // default options, overridden by docGroups and UI
        'includeFields' => 1,
        'includeOnlyRelationFields' => 0,
        'expandMatrixBlocks' => 1,
        'includeAuthor' => 0,
        'includeCustomNodes' => 0,
        'includeCustomLinks' => 0,
    ],
    'dotOptions' => [
        'rankDir' => 'LR',
        'splines' => 'splines',
        'title' => 'Site Diagram',
    ],
];
