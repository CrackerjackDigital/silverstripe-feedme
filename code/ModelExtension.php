<?php


class FeedMeModelExtension extends DataExtension {
    // name of the configuration variable which stores the relationship name between feed/item (and item/feed).
    const RelationshipNameConfigVariable = '';

    // override in concrete extension class to name the config variable used to store the field_map
    // which may differ as a Feed may also be an Item
    const FieldMapConfigVariable = '';

    const FieldTypesConfigVariable = '';

    // name of the model which is passed to Injector to find the implementing class
    const InjectorServiceName = '';

    // fields should be defined on extended object (nb not the field names, just the key to map to correct field names)
    const TitleFieldName = 'FeedMeTitle';
    const TitleFieldType = 'Varchar(255)';

    // fields added by FeedMe extension
    const ExternalIDFieldName = 'FeedMeExternalID';
    const ExternalIDFieldType = 'Varchar(64)';

    const LinkFieldName = 'FeedMeLink';
    const LinkFieldType = 'Text';

    const LastPublishedFieldName = 'FeedMeLastPublished';
    const LastPublishedFieldType = 'Varchar(64)';

    protected static $feedme_field_map = [];

    protected static $feedme_field_types = [];

    /**
     * Update from a neutral map as defined in config.feedme_feed_field_map to actual
     * fields in the data object skipping map entries with no value (destination field)
     * and map entries where the key is not in the passed map.
     *
     * @param array $feedMeNeutralMap
     * @param array &$imported - fields which were imported are listed here by model field name
     * @return $this
     * @fluent
     */
    public function feedMeUpdate(array $feedMeNeutralMap, array &$imported = []) {
        $fieldMap = $this->getFieldMap();

        foreach ($fieldMap as $mapKey => $fieldName) {
            if ($fieldName && array_key_exists($mapKey, $feedMeNeutralMap)) {
                $this->owner->$fieldName = $feedMeNeutralMap[$mapKey];
                $imported[] = $mapKey;
            }
        }
        $this->owner->feedMeUpdated($imported);
        return $this;
    }

    /**
     * Return the field map as declared on config of the extended class (not on this extension).
     *
     * Will fail if a supplied $neutralFieldName isn't a key in the field map.
     *
     * @param string|null $neutralFieldName - optional return only one field using this as key
     * @return string|array
     */
    private function getFieldMap($neutralFieldName = null) {
        $map = $this->owner->config()->get(static::FieldMapConfigVariable);
        return $neutralFieldName ? $map[$neutralFieldName] : $map;
    }

    public static function get_extra_config($class, $extension, $args) {
        $config = Config::inst()->forClass(get_called_class());

        $fieldMap = $config->get(static::FieldMapConfigVariable);
        $fieldTypes = $config->get(static::FieldTypesConfigVariable);

        $dbFields = [];

        // if the field on the model still starts with 'FeedMe' then we need to add it to the model
        // otherwise it has probably been changed to a field which already exists on the model.
        foreach ($fieldMap as $neutralFieldName => $_) {
            if (substr($fieldMap[$neutralFieldName], 0, 6) === 'FeedMe') {
                $dbFields[$fieldMap[$neutralFieldName]] = $fieldTypes[$neutralFieldName];
            }
        }
        return array_merge_recursive(
            parent::get_extra_config($class, $extension, $args) ?: [],
            [
                'db' => $dbFields
            ]
        );

    }

    /**
     * Return the name of the class being used to represent items.
     *
     * @return string
     */
    protected static function model_class() {
        return get_class(Injector::inst()->get(static::InjectorServiceName));
    }

    /**
     * Return this extensions relationship name to other compoenent of the feed/item relationship (not the extended classes).
     *
     * @return string
     */
    protected static function relationship_name() {
        return Config::inst()->get(get_called_class(), static::RelationshipNameConfigVariable);
    }

    /**
     * Return the name of the field on the extended model where the Title is stored (via field map).
     * @return mixed
     */
    protected static function title_field() {
        return static::get_model_field_name(static::TitleFieldName);
    }

    /**
     * Return the name of the field on the extended model where the External ID is stored (via field map).
     * @return mixed
     */
    protected static function link_field() {
        return static::get_model_field_name(static::LinkFieldName);
    }

    /**
     * Return the name of the field on the extended model where the External ID is stored (via field map).
     * @return mixed
     */
    protected static function external_id_field() {
        return static::get_model_field_name(self::ExternalIDFieldName);
    }

    /**
     * Return the name of the field on extended model where last published date is stored (via field map).
     *
     * @return string
     */
    protected static function last_published_field() {
        return static::get_model_field_name(static::LastPublishedFieldName);
    }

    /**
     * Return the name of model field which stores the value for the provided 'neutral' field name.
     *
     * @param $neutralName
     * @return string|null
     */
    protected static function get_model_field_name($neutralName) {
        $map = Config::inst()->get(get_called_class(), static::FieldMapConfigVariable);
        return isset($map[$neutralName]) ? $map[$neutralName] : null;
    }


}