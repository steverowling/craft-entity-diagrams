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

-Insert text here-

## Using Entity Diagrams

Go to the Entity Diagrams control panel page, select what elements you want to include in your diagram, or choose a custom Document Group (defined in the `entity-diagrams.php` config file), then click `Generate diagram`. The diagram will be injected into the iframe below, where you can use the mouse and scroll wheel to zoom in and pan around the diagram.

The plugin generates a DOT file for the diagram, which is rendered by d3-graphviz as an SVG. You can download this SVG to use it in printed documentation or convert to a PDF.

There are various options to change how the diagram is rendered, though some of these aren't really well-suited for more complex diagrams. They are included more for completeness that anything else.

## Support

The plugin is released under the MIT license, meaning you can do what ever you want with it as long as you don't blame us (and honor the original license). It's free, which means there is absolutely no support included, but you might get it anyway. Just post an issue here on GitHub if you have one, and we'll see what we can do.

Brought to you by [Steve Rowling](https://springworks.co.uk)
