<?php
/**
 * Entity Diagrams plugin for Craft CMS 3.x
 *
 * Generate entity diagrams that show how the different parts of your Craft site relate to each other
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\entitydiagrams\services;

use Craft;
use craft\base\Volume;
use craft\elements\GlobalSet;
use craft\errors\MissingComponentException;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Entries;
use craft\fields\Matrix;
use craft\fields\Tags;
use craft\fields\Users;
use craft\helpers\App;
use craft\helpers\Component;
use craft\helpers\FileHelper;
use craft\models\CategoryGroup;
use craft\models\Section;
use craft\models\TagGroup;
use craft\models\UserGroup;
use Exception;
use modules\oscar\archives\BaseArchive;
use springworks\entitydiagrams\EntityDiagrams;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;

/**
 * GenerateDot Service
 *
 * All of your module’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other modules can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Steve Rowling
 * @package   EntityDiagrams
 * @since     1.0.0
 */
class GenerateDot extends Component
{
    /**
     * @var array
     */
    protected $sections = [];
    /**
     * @var array
     */
    protected $categories = [];
    /**
     * @var array
     */
    protected $userGroups = [];
    /**
     * @var array
     */
    protected $globals = [];
    /**
     * @var array
     */
    protected $tags = [];
    /**
    /**
     * @var array
     */
    protected $volumes = [];
    /**
     * @var array
     */
    protected $products = [];
    /**
     * @var array
     */
    protected $customNodes = [];

    // Public Methods
    // =========================================================================

    /**
     * Generate developer documentation DOT file for graphviz
     *
     * @param array $config
     * @param array $dotOptions
     * @return string
     */
    public function generateDot(array $config = [], array $dotOptions = []) : string
    {
        $settings = EntityDiagrams::$plugin->getSettings();

        if (!$dotOptions) {
            $dotOptions = $settings['dotOptions'];
        }

        unset($settings['dotOptions']);

        if (!$config) {
            $config = $settings;
        }

        $sectionsService = Craft::$app->getSections();
        $categoriesService = Craft::$app->getCategories();
        $userGroupsService = Craft::$app->getUserGroups();
        $globalsService = Craft::$app->getGlobals();
        $tagsService = Craft::$app->getTags();
        $volumesService = Craft::$app->getVolumes();

        // Gather all data for documentation
        // =========================================================================

        // Sections
        if (!is_array($config['sections']) && $config['sections'] === '*') {
            $this->sections = $sectionsService->getAllSections();
        } else {
            foreach ($config['sections'] as $sectionHandle) {
                $this->sections[] = $sectionsService->getSectionByHandle($sectionHandle);
            }
        }

//        if (!$config['globalOptions']['onlyIncludeSections']) {
            // Categories
            if (!is_array($config['categories']) && $config['categories'] === '*') {
                $this->categories = $categoriesService->getAllGroups();
            } else {
                foreach ($config['categories'] as $groupHandle) {
                    $this->categories[] = $categoriesService->getGroupByHandle($groupHandle);
                }
            }

            // User Groups
            if (!is_array($config['userGroups']) && $config['userGroups'] === '*') {
                $this->userGroups = $userGroupsService->getAllGroups();
            } else {
                foreach ($config['userGroups'] as $groupHandle) {
                    $this->userGroups[] = $userGroupsService->getGroupByHandle($groupHandle);
                }
            }

            // Globals
            if (!is_array($config['globals']) && $config['globals'] === '*') {
                $this->globals = $globalsService->getAllSets();
            } else {
                foreach ($config['globals'] as $setHandle) {
                    $this->globals[] = $globalsService->getSetByHandle($setHandle);
                }
            }

            // Tags
            if (!is_array($config['tags']) && $config['tags'] === '*') {
                $this->tags = $tagsService->getAllTagGroups();
            } else {
                foreach ($config['tags'] as $groupHandle) {
                    $this->tags[] = $tagsService->getTagGroupByHandle($groupHandle);
                }
            }

            // Volumes
            if (!is_array($config['volumes']) && $config['volumes'] === '*') {
                $this->volumes = $volumesService->getAllVolumes();
            } else {
                foreach ($config['volumes'] as $handle) {
                    $this->volumes[] = $volumesService->getVolumeByHandle($handle);
                }
            }

            // Custom Nodes
            if ($config['customNodes']) {
                foreach ($config['customNodes'] as $customNode) {
                    $this->customNodes[] = $customNode;
                }
            }
//        }

        // Generate DOT template for documentation
        // =========================================================================

        $dotStart = 'digraph "' . $dotOptions['title'] . '" {
    rankdir = ' . $dotOptions['rankDir'] . ';
    graph [fontname = "Helvetica,Arial,sans-serif", fontsize="24", label = "' . $dotOptions['title'] . '" splines="' . $dotOptions['splines'] . '" ranksep="1.5" overlap="false" nodesep="1.5"];
    node [shape="plaintext", fontname = "Helvetica,Arial,sans-serif "];
    ';

        $dotEnd = '}';

        $nodes = [];
        $links = [];

        // Sections
        foreach ($this->sections as $docSection) {
            $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docSection->name) . '</b></td></tr><tr><td align="left">' . strtoupper($docSection->type) . '</td></tr>';

            // Author
            if ($config['options']['includeAuthor'] && isset($config['authorMap'][$docSection->handle]) && $config['authorMap'][$docSection->handle]) {
                $labelHTML .= '<tr><td align="left" port="author"><font point-size="12">Author</font></td></tr>';
                foreach ($config['authorMap'][$docSection->handle] as $userGroupHandle) {
                    foreach ($this->userGroups as $userGroup) {
                        if ($userGroup->handle === $userGroupHandle) {
                            $links[] = '"' . $docSection->uid . '":author:e -> "' . $userGroup->uid . '"';
                        }
                    }
                }
            }

            // Fields
            if ($config['options']['includeFields']) {
                $this->_generateFields($config, $docSection, $labelHTML, $links);
            }

            $labelHTML .= '</table>>]';
            $nodes[] = '"' . $docSection->uid . '" ' . $labelHTML;
        }

//        if (!$config['globalOptions']['onlyIncludeSections']) {

            // Categories
            foreach ($this->categories as $docCategory) {
                $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docCategory->name) . '</b></td></tr><tr><td align="left">CATEGORY GROUP</td></tr>';

                // Fields
                if ($config['options']['includeFields']) {
                    $this->_generateFields($config, $docCategory, $labelHTML, $links);
                }

                $labelHTML .= '</table>>]';
                $nodes[] = '"' . $docCategory->uid . '" ' . $labelHTML;
            }

            // User Groups
            foreach ($this->userGroups as $docUserGroup) {
                $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docUserGroup->name) . '</b></td></tr><tr><td align="left">USER GROUP</td></tr>';

                // Fields
                if ($config['options']['includeFields']) {
                    $this->_generateFields($config, $docUserGroup, $labelHTML, $links);
                }

                $labelHTML .= '</table>>]';
                $nodes[] = '"' . $docUserGroup->uid . '" ' . $labelHTML;
            }

            // Globals
            foreach ($this->globals as $docGlobal) {
                $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docGlobal->name) . '</b></td></tr><tr><td align="left">GLOBAL</td></tr>';

                // Fields
                if ($config['options']['includeFields']) {
                    $this->_generateFields($config, $docGlobal, $labelHTML, $links);
                }

                $labelHTML .= '</table>>]';
                $nodes[] = '"' . $docGlobal->uid . '" ' . $labelHTML;
            }

            // Tags
            foreach ($this->tags as $docTag) {
                $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docTag->name) . '</b></td></tr><tr><td align="left">TAG GROUP</td></tr>';

                // Fields
                if ($config['options']['includeFields']) {
                    $this->_generateFields($config, $docTag, $labelHTML, $links);
                }

                $labelHTML .= '</table>>]';
                $nodes[] = '"' . $docTag->uid . '" ' . $labelHTML;
            }

            // Volumes
            foreach ($this->volumes as $docVolume) {
                $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docVolume->name) . '</b></td></tr><tr><td align="left">ASSET VOLUME</td></tr>';

                // Fields
                if ($config['options']['includeFields']) {
                    $this->_generateFields($config, $docVolume, $labelHTML, $links);
                }

                $labelHTML .= '</table>>]';
                $nodes[] = '"' . $docVolume->uid . '" ' . $labelHTML;
            }

//        }

        // Custom nodes
        if ($config['customNodes']) {
            foreach ($this->customNodes as $docCustomNode) {
                $labelHTML = '[label=< <table border="0" cellborder="1" cellspacing="0" cellpadding="4"><tr><td align="center"><b>' . htmlspecialchars($docCustomNode['name']) . '</b></td></tr><tr><td align="left">' . htmlspecialchars($docCustomNode['type']) . '</td></tr>';

                // Author
                if ($config['options']['includeAuthor'] && isset($config['authorMap'][$docCustomNode['handle']]) && $config['authorMap'][$docCustomNode['handle']]) {
                    $labelHTML .= '<tr><td align="left" port="author"><font point-size="12">Author</font></td></tr>';
                    foreach ($config['authorMap'][$docCustomNode['handle']] as $userGroupHandle) {
                        foreach ($this->userGroups as $userGroup) {
                            if ($userGroup->handle === $userGroupHandle) {
                                $links[] = '"' . $docCustomNode['handle'] . '":author -> "' . $userGroup->uid . '"';
                            }
                        }
                    }
                }

                // Fields
                if ($config['options']['includeFields']) {
                    foreach ($docCustomNode['fields'] as $index => $customNodeField) {
                        $sides = ($index + 1) === count($docCustomNode['fields']) ? 'lrb' : 'lr';
                        $labelHTML .= '<tr><td align="left" sides="' . $sides . '" port="' . $customNodeField . '"><font point-size="12">' . $customNodeField . '</font></td></tr>';
                    }
                }

                $labelHTML .= '</table>>]';
                $nodes[] = '"' . $docCustomNode['handle'] . '" ' . $labelHTML;
            }
        }

        // Custom links
        if ($config['customLinks']) {
            foreach ($config['customLinks'] as $customLink) {
                $linkEnds = explode('->', $customLink);
                $linkFrom = $linkEnds[0] ?? '';
                $linkTo = $linkEnds[1] ?? '';

                // If $linkFrom && $linkTo exist, then we have a valid link
                if ($linkFrom && $linkTo) {
                    $linkFromElementHandle = explode(':', $linkFrom)[0] ?? '';
                    $linkFromFieldHandle = explode(':', $linkFrom)[1] ?? '';
                    $linkFromUid = '';
                    $linkToUid = '';

                    // Find elements and build link
                    foreach ($this->sections as $section) {
                        if ($section->handle === $linkFromElementHandle) {
                            $linkFromUid = $section->uid;
                        }
                        if ($section->handle === $linkTo) {
                            $linkToUid = $section->uid;
                        }
                    }
                    if (!$config['globalOptions']['onlyIncludeSections']) {
                        foreach ($this->categories as $category) {
                            if ($category->handle === $linkFromElementHandle) {
                                $linkFromUid = $category->uid;
                            }
                            if ($category->handle === $linkTo) {
                                $linkToUid = $category->uid;
                            }
                        }
                        foreach ($this->userGroups as $userGroup) {
                            if ($userGroup->handle === $linkFromElementHandle) {
                                $linkFromUid = $userGroup->uid;
                            }
                            if ($userGroup->handle === $linkTo) {
                                $linkToUid = $userGroup->uid;
                            }
                        }
                        foreach ($this->tags as $tag) {
                            if ($tag->handle === $linkFromElementHandle) {
                                $linkFromUid = $tag->uid;
                            }
                            if ($tag->handle === $linkTo) {
                                $linkToUid = $tag->uid;
                            }
                        }
                        foreach ($this->volumes as $volume) {
                            if ($volume->handle === $linkFromElementHandle) {
                                $linkFromUid = $volume->uid;
                            }
                            if ($volume->handle === $linkTo) {
                                $linkToUid = $volume->uid;
                            }
                        }
                        foreach ($this->customNodes as $customNode) {
                            if ($customNode['handle'] === $linkFromElementHandle) {
                                $linkFromUid = $customNode['handle'];
                            }
                            if ($customNode['handle'] === $linkTo) {
                                $linkToUid = $customNode['handle'];
                            }
                        }
                    }
                    if ($linkFromUid && $linkToUid && $linkFromFieldHandle) {
                        $links[] = '"' . $linkFromUid . '":' . $linkFromFieldHandle . ' -> "' . $linkToUid . '"';
                    }

                }
            }
        }

        $dotContent = '';

        foreach ($nodes as $node) {
            $dotContent .= $node.';'.PHP_EOL;
        }

        foreach ($links as $link) {
            $dotContent .= $link.';'.PHP_EOL;
        }

        return $dotStart.$dotContent.$dotEnd;
    }

    // Private Methods
    // =========================================================================

    /**
     * @param $config
     * @param $docElement
     * @param $labelHTML
     * @param $links
     */
    private function _generateFields($config, $docElement, &$labelHTML, &$links): void
    {
        $fields = Craft::$app->getFields();

        $fieldLayouts = [];

        // Get field layouts for $docElement
        switch (get_class($docElement)) {
            /** @var Section $docElement */
            case Section::class:
                // Section - get field layouts for this section
                $entryTypes = $docElement->getEntryTypes();
                foreach ($entryTypes as $entryType) {
                    $fieldLayouts[$entryType->name] = $fields->getLayoutById($entryType->fieldLayoutId);
                }
                break;

            /** @var UserGroup $docElement */
            case UserGroup::class:
                // UserGroup - get field layout for Users
                $currentUser = Craft::$app->getUser()->getIdentity();
                $fieldLayouts = [$currentUser->getFieldLayout()];
                break;

            /** @var CategoryGroup $docElement */
            case CategoryGroup::class:
                // CategoryGroup - get field layouts for this category group
                $fieldLayoutId = $docElement->fieldLayoutId;
                $fieldLayouts = $fieldLayoutId ? [$fields->getLayoutById($fieldLayoutId)] : [];
                break;

            /** @var GlobalSet $docElement */
            case GlobalSet::class:
                // Global - get field layouts for this global
                $fieldLayouts = [$docElement->getFieldLayout()];
                break;

            /** @var TagGroup $docElement */
            case TagGroup::class:
                // TagGroup - get field layouts for this tag group
                $fieldLayoutId = $docElement->fieldLayoutId;
                $fieldLayouts = $fieldLayoutId ? [$fields->getLayoutById($fieldLayoutId)] : [];
                break;

            /** @var Volume $docElement */
            case Volume::class:
                // Volume - get field layouts for this tag group
                $fieldLayoutId = $docElement->fieldLayoutId;
                $fieldLayouts = $fieldLayoutId ? [$fields->getLayoutById($fieldLayoutId)] : [];
                break;
        }

        foreach ($fieldLayouts as $key => $fieldLayout) {
            if (is_string($key)) {
                $labelHTML .= '<tr><td align="left" bgcolor="#bebebe"><font point-size="10">ENTRY TYPE: ' . htmlspecialchars($key) . '</font></td></tr>';
            }
            foreach ($fieldLayout->getTabs() as $tab) {
                if (!$config['options']['includeOnlyRelationFields']) {
                    $labelHTML .= '<tr><td align="left" bgcolor="#ebebeb"><font point-size="10">TAB: ' . htmlspecialchars($tab->name) . '</font></td></tr>';
                }
                foreach ($tab->getFields() as $index => $field) {
                    $sides = ($index + 1) === count($tab->getFields()) ? 'lrb' : 'lr';
                    $lastField = ($index + 1) === count($tab->getFields());
                    if (get_class($field) === Matrix::class && $config['options']['expandMatrixBlocks']) {
                        $sides = 'lr';
                    }
                    $this->_generateField($field, '', $sides, $config, $docElement, $labelHTML, $links);
                    if (get_class($field) === Matrix::class && $config['options']['expandMatrixBlocks']) {
                        foreach ($field->getBlockTypes() as $i => $blockType) {
                            $lastBlockType = ($i + 1) === count($field->getBlockTypes());
                            $labelHTML .= '<tr><td align="left" sides="lr"><font color="#7f7f7f" point-size="10">BLOCK: ' . htmlspecialchars($blockType->name) . '</font></td></tr>';
                            $blockTypeFieldLayout = $fields->getLayoutById($blockType->fieldLayoutId);
                            foreach ($blockTypeFieldLayout->getFields() as $ind => $blockTypeField) {
                                $sides = (($ind + 1) === count($blockTypeFieldLayout->getFields()) && $lastBlockType && $lastField) ? 'lrb' : 'lr';
                                $this->_generateField($blockTypeField, '&rarr; ', $sides, $config, $docElement, $labelHTML, $links, $field->handle);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $field
     * @param $prefix
     * @param $sides
     * @param $config
     * @param $docElement
     * @param $labelHTML
     * @param $links
     */
    private function _generateField($field, $prefix, $sides, $config, $docElement, &$labelHTML, &$links, $fieldParent = null): void
    {
        $fieldHandle = $fieldParent ? $fieldParent . $field->handle : $field->handle;
        if (in_array(get_class($field), [Categories::class, Entries::class, Tags::class, Users::class])) {
            if (!$field->allowMultipleSources) {
                $this->_generateRelationFieldLinks($field, $field->source, $config, $docElement, $links, $fieldParent);
            } else {
                if ($field->sources === '*') {
                    $sources = [];
                    switch (get_class($field)) {

                        case Categories::class:
                            foreach ($this->categories as $category) {
                                $source[] = 'group:' . $category->uid;
                            }
                            break;

                        case Entries::class:
                            foreach ($this->sections as $section) {
                                $source[] = 'section:' . $section->uid;
                            }
                            break;

                        case Tags::class:
                            foreach ($this->tags as $tag) {
                                $source[] = 'group:' . $tag->uid;
                            }
                            break;

                        case Users::class:
                            foreach ($this->userGroups as $userGroup) {
                                $source[] = 'group:' . $userGroup->uid;
                            }
                            break;
                    }
                } elseif ($field->sources && !is_array($field->sources)) {
                    $sources = [$field->sources];
                } else {
                    $sources = $field->sources;
                }
                foreach ($sources as $source) {
                    $this->_generateRelationFieldLinks($field, $source, $config, $docElement, $links, $fieldParent);
                }
            }
            $sides = !$config['options']['includeOnlyRelationFields'] ? $sides : 'lrb';
            $labelHTML .= '<tr><td align="left" sides="' . $sides . '" port="' . $fieldHandle . '"><font point-size="12">' . $prefix . $field->handle . '</font> <font color="#7f7f7f" point-size="10">' . $field->displayName() . '</font></td></tr>';
        } elseif (get_class($field) === Assets::class) {
            if ($field->useSingleFolder && $field->singleUploadLocationSource) {
                $this->_generateRelationFieldLinks($field, $field->singleUploadLocationSource, $config, $docElement, $links, $fieldParent);
            } elseif (!$field->allowMultipleSources) {
                $this->_generateRelationFieldLinks($field, $field->source, $config, $docElement, $links, $fieldParent);
            } else {
                if ($field->sources === '*') {
                    $sources = [];
                    foreach ($this->volumes as $volume) {
                        $sources[] = 'volume:' . $volume->uid;
                    }
                } elseif ($field->sources && !is_array($field->sources)) {
                    $sources = [$field->sources];
                } else {
                    $sources = $field->sources;
                }
                foreach ($sources as $source) {
                    $this->_generateRelationFieldLinks($field, $source, $config, $docElement, $links, $fieldParent);
                }
            }
            $sides = !$config['options']['includeOnlyRelationFields'] ? $sides : 'lrb';
            $labelHTML .= '<tr><td align="left" sides="' . $sides . '" port="' . $fieldHandle . '"><font point-size="12">' . $prefix . $field->handle . '</font> <font color="#7f7f7f" point-size="10">' . $field->displayName() . '</font></td></tr>';
        } elseif (!$config['options']['includeOnlyRelationFields']) {
            $labelHTML .= '<tr><td align="left" sides="' . $sides . '" port="' . $fieldHandle . '"><font point-size="12">' . $prefix . $field->handle . '</font> <font color="#7f7f7f" point-size="10">' . $field->displayName() . '</font></td></tr>';
        }
    }

    /**
     * @param $field
     * @param $source
     * @param $config
     * @param $docElement
     * @param $links
     */
    private function _generateRelationFieldLinks($field, $source, $config, $docElement, &$links, $fieldParent = null): void
    {
        $includeSource = false;
        foreach ($this->sections as $section) {
            if ('section:'.$section->uid === $source) {
                $includeSource = true;
                break;
            }
        }
//        if (!$config['globalOptions']['onlyIncludeSections']) {
            if (!$includeSource) {
                foreach ($this->categories as $category) {
                    if ('group:' . $category->uid === $source) {
                        $includeSource = true;
                        break;
                    }
                }
            }
            if (!$includeSource) {
                foreach ($this->userGroups as $userGroup) {
                    if ('group:' . $userGroup->uid === $source) {
                        $includeSource = true;
                        break;
                    }
                }
            }
            if (!$includeSource) {
                foreach ($this->tags as $tag) {
                    if ('group:' . $tag->uid === $source) {
                        $includeSource = true;
                        break;
                    }
                }
            }
            if (!$includeSource) {
                foreach ($this->volumes as $volume) {
                    if ('volume:' . $volume->uid === $source) {
                        $includeSource = true;
                        break;
                    }
                }
            }
//        }
        $fieldHandle = $fieldParent ? $fieldParent . $field->handle : $field->handle;

        if ($includeSource) {
            $links[] = '"' . $docElement->uid . '":' . $fieldHandle . ' -> "' . explode(':', $source)[1] . '"';
        }
    }
}
