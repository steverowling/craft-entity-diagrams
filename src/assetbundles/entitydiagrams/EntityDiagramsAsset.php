<?php
/**
 * Entity Diagrams plugin for Craft CMS 3.x
 *
 * Generate entity diagrams that show how the different parts of your Craft site relate to each other
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\entitydiagrams\assetbundles\entitydiagrams;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * EntityDiagramsAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Steve Rowling
 * @package   EntityDiagrams
 * @since     1.0.0
 */
class EntityDiagramsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@springworks/entitydiagrams/assetbundles/entitydiagrams/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/EntityDiagrams.js',
        ];

        $this->css = [
            'css/EntityDiagrams.css',
        ];

        parent::init();
    }
}
