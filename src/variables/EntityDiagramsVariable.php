<?php
/**
 * Entity Diagrams plugin for Craft CMS 3.x
 *
 * Generate entity diagrams that show how the different parts of your Craft site relate to each other
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\entitydiagrams\variables;


use springworks\entitydiagrams\EntityDiagrams;
use springworks\entitydiagrams\models\Settings;

/**
 * EntityDiagramsVariable
 *
 * @author    Steve Rowling
 * @package   EntityDiagrams
 * @since     1.0.0
 */
class EntityDiagramsVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param array $config
     * @param array $dotOptions
     * @return string
     */
    public function getDot(array $config = [], array $dotOptions = []): string
    {
        return EntityDiagrams::$plugin->generateDot->generateDot($config, $dotOptions);
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return EntityDiagrams::$plugin->getSettings()->toArray();
    }

    /**
     * Checks that Commerce plugin is installed
     *
     * @return bool
     */
    public function isCommerceEnabled(): bool
    {
        $plugins = Craft::$app->getPlugins();

        return $plugins->isPluginEnabled('commerce');
    }
}
