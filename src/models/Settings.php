<?php
/**
 * Entity Diagrams plugin for Craft CMS 3.x
 *
 * Generate entity diagrams that show how the different parts of your Craft site relate to each other
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\entitydiagrams\models;

use craft\base\Model;
use yii\validators\InlineValidator;

/**
 * EntityDiagrams Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Steve Rowling
 * @package   EntityDiagrams
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * List of section handles to include in entity diagram
     * Set to '*' to include all sections
     *
     * @var string|array
     */
    public $sections = '*';

    /**
     * List of category group handles to include in entity diagram
     * Set to '*' to include all category groups
     *
     * @var string|array
     */
    public $categories = '*';

    /**
     * List of user group handles to include in entity diagram
     * Set to '*' to include all user groups
     *
     * @var string|array
     */
    public $userGroups = '*';

    /**
     * List of global set handles to include in entity diagram
     * Set to '*' to include all global sets
     *
     * @var string|array
     */
    public $globals = '*';

    /**
     * List of tag group handles to include in entity diagram
     * Set to '*' to include all tag groups
     *
     * @var string|array
     */
    public $tags = '*';

    /**
     * List of asset volume handles to include in entity diagram
     * Set to '*' to include all asset volumes
     *
     * @var string|array
     */
    public $volumes = '*';

    /**
     * List of product type handles to include in entity diagram (only relevant of Commerce plugin is installed)
     * Set to '*' to include all product types
     *
     * @var string|array
     */
    public $products = [];

    /**
     * Use this to map section handles to an array of user group handles that represent authors of those sections
     * Useful to indicate if certain sections will always be authored by user from a particular user group
     *
     * @var array
     */
    public $authorMap = [];

    /**
     * Use this to add any custom nodes not handled automatically by Craft, e.g. custom database tables
     * Each element of the array should look like this:
     * [
     *     'name' => 'Name of My Custom Node', // This can be anything that makes sense for you
     *     'handle' => 'handleForCustomNode', // Must be unique
     *     'type' => 'DATABASE TABLE', // Can be anything that makes sense for you, e.g. DATABASE TABLE, EXTERNAL API, etc.
     *     'fields' => [ // Array of field handles in the custom source, e.g. if this is a database table, add the relevant columns here
     *         'field1',
     *         'field2',
     *         'field3',
     *     ],
     * ]
     *
     * @var array
     */
    public $customNodes = [];

    /**
     * Use this to add any custom links not handled automatically by Craft, e.g. linking to a matrix block id in a field.
     * Note that you can also link fields in custom nodes to any other node using this feature
     * Format: "nodeHandle[:field.handle] -> nodeHandle"
     * e.g.: "mySectionHandle:matrixBlockId -> myOtherSectionHandle"
     *
     * @var array
     */
    public $customLinks = [];

    /**
     * Options
     *
     * @var array
     */
    public $options = [
        'includeFields' => 1,
        'includeOnlyRelationFields' => 0,
        'expandMatrixBlocks' => 1,
        'includeAuthor' => 0,
        'includeCustomNodes' => 0,
        'includeCustomLinks' => 0,
    ];

    /**
     * dotOptions
     *
     * @var array
     */
    public $dotOptions = [
        'rankDir' => 'LR',
        'splines' => 'splines',
        'title' => 'Site Diagram',
    ];

    // Public Methods
    // =========================================================================

    /**
     * @param string $attribute the attribute currently being validated
     * @param mixed $params the value of the "params" given in the rule
     * @param InlineValidator $validator related InlineValidator instance.
     * This parameter is available since version 2.0.11.
     * @param mixed $current the currently validated value of attribute.
     * This parameter is available since version 2.0.36.
     */
    public function validateElementList($attribute, $params, $validator, $current): void
    {
        if (!is_array($this->$attribute) && $this->$attribute !== '*') {
            $this->addError($attribute, '{attribute} must be an array of handles or the string "*".');
        }
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['sections','categories','userGroups','globals','tags','volumes','products'], 'validateElementList'],
            ['options', 'each', 'rule' => ['boolean']],
            ['dotOptions', 'each', 'rule' => ['string']],
        ];
    }
}
