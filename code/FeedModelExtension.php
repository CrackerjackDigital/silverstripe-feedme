<?php

class FeedMeFeedModelExtension extends FeedMeModelExtension {
    const DescriptionFieldName = 'Description';
    const DescriptionFieldType = 'HTMLText';

    // relationship to feed items.
    const RelationshipNameDefault = 'FeedMeItems';

    const FieldMapConfigVariable = 'feedme_field_map';

    const FieldTypesConfigVariable = 'feedme_field_types';

    const RelationshipNameConfigVariable = 'feedme_item_relationship';

    const InjectorServiceName = 'FeedMeFeedModelClass';

    // if true feed will be imported onAfterWrite of extended Feed object.
    protected static $feedme_import_on_write = true;

    protected static $feedme_item_relationship = self::RelationshipNameDefault;


    // default field map from 'neutral' name to model field name, these should be set in config
    // of the extended model class where fields which already exist are going ot be used instead of
    // fields added by this extension
    protected static $feedme_field_map = [
        self::TitleFieldName => '',                                     // map to extended classes Title field
        self::DescriptionFieldName => '',                               // map to extended classes Description field
        self::ExternalIDFieldName => self::ExternalIDFieldName,         // default map
        self::LinkFieldName => self::LinkFieldType,                       // default map
        self::LastPublishedFieldName => self::LastPublishedFieldName    // default map
    ];

    // default field types incase we need to create fields on the model
    protected static $feedme_field_types = [
        self::TitleFieldName => self::TitleFieldType,
        self::DescriptionFieldName=> self::DescriptionFieldType,
        self::ExternalIDFieldName => self::ExternalIDFieldType,
        self::LinkFieldName => self::LinkFieldType,
        self::LastPublishedFieldName => self::LastPublishedFieldType
    ];

    // map of feed types to an xpath which can be used to identify them using raw feed xml.
    private static $feedme_supported_formats = [
        'RSS2' => '//channel/item',
        'ATOM' => '//feed/entry'
    ];

    /**
     * Add has many relationship to class created for Injector service name for self.InjectorServiceName
     *
     * @param $class
     * @param $extension
     * @param $args
     * @return array
     */
    public static function get_extra_config($class, $extension, $args) {
        return array_merge_recursive(
            parent::get_extra_config($class, $extension, $args) ?: [],
            [
                'has_many' => get_class(Injector::inst()->create(static::InjectorServiceName))
            ]
        );
    }
    /**
     * Check we can handle the feed (format etc), if not throw a Validation exception.
     */
    public function onBeforeWrite() {
        $feedURL = $this->getFeedURL();

        if (!self::digest($feedURL)) {
            throw new ValidationException(
                _t('FeedMe.InvalidFeedMessage',
                    'Bad feed {url}, check address or is one of these supported types: {types}',
                    array(
                        'url' => $feedURL,
                        'types' => implode(',', array_keys(self::supported_formats()))
                    )
                )
            );
        }
    }
    /**
     * Import feed after write if owner.config.feedme_import_on_write is true.
     */
    public function onAfterWrite() {
        if ($this->importOnWrite()) {
            $this->feedMeImport();
        }
    }

    /**
     * Use self.digest to obtain an iterator of feed item Models and then either update or add them to
     * the Posts relationship depending on match on PostModel.ExternalID.
     */
    public function feedMeImport() {
        $fieldURL = $this->getFeedURL();

        if ($items = self::digest($fieldURL)) {
            $relationshipMame = self::relationship_name();
            $externalIDField = FeedMeItemModelExtension::ExternalIDFieldName;

            /** @var HasManyList $existing */
            $existing = $this->$relationshipMame();

            /** @var DataObject $model */
            foreach ($items as $model) {
                // if post with the ExternalID already exists then update that one.
                if ($found = $existing->find($externalIDField, $model->{$externalIDField})) {

                    // update the found one from map of the new one
                    $found->update($model->toMap());
                    if ($found->isChanged()) {
                        // we changed so write out
                        $found->write();
                    }
                } else {
                    /// not found add as a new post.
                    $this->$relationshipMame()->add($model);
                }
            }
        }
    }


    /**
     * Returns the value of the URL field on the extended class.
     *
     * @return string|null
     */
    protected function getFeedURL() {
        $fieldName = self::link_field();
        return $this->owner->{$fieldName};
    }
    /**
     * Check if we can open and handle (the format of) the supplied url. Return a FeedMeFeedIterator for items if so,
     * otherwise null. The concrete iterator class is chosen using injector.create method with a service name
     * of 'FeedMe{feedType}FeedIterator' where the feedType is chosen using the key of the config.supported_formats
     * the value is the first matching xpath expression.
     *
     * @param $feedURL
     * @return FeedMeFeedIterator|null
     */
    public static function digest($feedURL) {
        $doc = new SimpleXMLElement(file_get_contents($feedURL));
        if ($doc) {
            $xpath = new DOMXPath($doc);

            // iterate through handled formats and see if a format's xpath matches feed's xml contents.
            foreach (self::supported_formats() as $feedType => $itemPath) {
                $items = $xpath->query($itemPath);
                if ($items && $items->length) {
                    $className = "FeedMe{$feedType}FeedIterator}";

                    // create and return a new FeedIterator of the detected type
                    return Injector::inst()->create($className, $items, $feedURL);
                }
            }
        }
        return null;
    }


    /**
     * Return the extended objects config.feedme_import_on_write.
     *
     * @return boolean
     */
    private function importOnWrite() {
        return $this->owner->config()->get('feedme_import_on_write');
    }


    /**
     * Return this extensions config.feedme_supported_formats (not the extended objects).
     * @return array
     */
    private static function supported_formats() {
        return Config::inst()->get(__CLASS__, 'feedme_supported_formats');
    }



}