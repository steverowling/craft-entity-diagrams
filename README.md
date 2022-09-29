# Entity Diagrams plugin for Craft CMS 3.x

Generate entity diagrams that show how the different parts of your Craft site relate to each other

## Requirements

This plugin requires Craft CMS 3.5.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require springworks/entity-diagrams

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Entity Diagrams.

## Entity Diagrams Overview

Have you ever wanted to get a visual overview of how the different elements of your Craft site fit together? Entity Diagrams enables you to do just that, producing an in-browser SVG that you can zoom and scale around, or download as an SVG file to re-use as you wish.

Supporting all the native top-level Craft elements (Entries, Categories, Tags, Users, Globals and Assets), you can choose to include all or none of an element type, or create custom document groupings that describe sub-sections of your site. Commerce Products are also supported if the Craft Commerce plugin is installed.

The plugin will scan the requested element types, and generate entity diagrams showing the element type, a list of all the fields (broken down by entry type and field layout tab) and will include connections between related element types directly from the relevant relations field.

There are options to include all fields or none and whether to expand Matrix blocks to include their sub-fields.

Using the `entity-diagrams.php` config file, you can add custom author links (useful if authors for a section always come from a particular user group).

You can also create custom nodes and links. These can be useful if you have custom database tables that interact with other elements of your site.

It’s a powerful tool to get a visual overview of how a complex site is structured adn can be really useful for onboarding new developers or when taking over a site developed by someone else.

## Configuring Entity Diagrams

Entity Diagrams is configured by creating a config file named `entity-diagrans.php` in your Craft config folder (i.e. alongside the `general.php` and `db.php` config files).

The config file should look something like this:

```php
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
```

The most powerful feature of this is the ability to create pre-defined Document Groups (`docGroups`) of elements that represent a self-contained sub-section of a site. You can also use this feature to add custom nodes and links to a diagram.

Document Group definitions look like this:

```php
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
        'Group Name' => [
            'sections' => [ // list all section handles to include
                'news',
                'pages',
                'blog',
            ],
            'categories' => [ // list all categoryGroup handles to include
                'newsCategories',
                'blogCategories',
            ],
            'userGroups' => [ // list all userGroup handles to include
                'contributors',
                'editors',
            ],
            'globals' => [ // list all global handles to include
                'siteSettings',
            ],
            'tags' => [ // list all tag group handles to include
            ],
            'volumes' => [ // list all asset volume handles to include
                'newsImages',
                'blogImages',
            ],
            'products' => [ // list all Commerce product type handles to include
            ],
            'authorMap' => [ // map userGroups to sections if entry authorship in a section is limited to by userGroup
                'news' => ['editors'],
                'blog' => ['editors','contributors'],
            ],
            'customNodes' => [ // add any custom nodes not handled automatically by Craft, e.g. custom database tables.
            ],
            'customLinks' => [ // add any custom links not handled automatically by Craft, e.g. linking to a matrix block id in a field. Format: "section.handle|categoryGroup.handle|userGroup.handle[:field.handle] -> section.handle|categoryGroup.handle|userGroup.handle"
            ],
            'options' => [
                'includeFields' => 1,
                'includeOnlyRelationFields' => 0,
                'expandMatrixBlocks' => 1,
                'includeAuthor' => 1,
                'includeCustomNodes' => 0,
                'includeCustomLinks' => 1,
            ],
        ],
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
```

You can add as many Document Groups as you like.

### Including all or no elements of a particular type

If you want to include all elements of a particular type, then set the array value to `'*'`, like this:

```php
   ...
   'sections' => '*', // include all section handles
   ...
```

Similarly, to exclude all elements of a particular type, set the array value to an empty array, like this:

```php
   ...
   'tags' => [], // don't include any tag groups
   ...
```

### Custom nodes and links

You can include custom nodes in a Document Group, e.g. custom database tables, etc., in an array like this:

```php
   ...
   'customNodes' => [ // add any custom nodes not handled automatically by Craft, e.g. custom database tables.
       [
           'name' => 'User Progress - Tasks',
           'handle' => 'userProgressTasks',
           'type' => 'DATABASE TABLE',
           'fields' => [
               'task',
               'user',
               'status',
           ],
       ],
   ],
   ...
```

Similarly, custom links can be defined like this:

```php
   ...
   'customLinks' => [ // add any custom links not handled automatically by Craft, e.g. linking to a matrix block id in a field. Format: "section.handle|categoryGroup.handle|userGroup.handle[:field.handle] -> section.handle|categoryGroup.handle|userGroup.handle"
       'userProgressTasks:task->taskEntries',
   ],
   ...
```

## Using Entity Diagrams

Go to the Entity Diagrams control panel page, select what elements you want to include in your diagram, or choose a custom Document Group (defined in the `entity-diagrams.php` config file), then click `Generate diagram`. The diagram will be injected into the iframe below, where you can use the mouse and scroll wheel to zoom in and pan around the diagram.

The plugin generates a DOT file for the diagram, which is rendered by d3-graphviz as an SVG. You can download this SVG to use it in printed documentation or convert to a PDF.

There are various options to change how the diagram is rendered, though some of these aren't really well-suited for more complex diagrams. They are included more for completeness that anything else.

## Support

The plugin is released under the MIT license, meaning you can do what ever you want with it as long as you don't blame us (and honor the original license). It's free, which means there is absolutely no support included, but you might get it anyway. Just post an issue here on GitHub if you have one, and we'll see what we can do.

Brought to you by [Steve Rowling](https://springworks.co.uk)
