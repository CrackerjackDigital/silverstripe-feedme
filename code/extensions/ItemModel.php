<?php

/**
 * Add feedme feed related feeds to the model which will implement feed item instances, e.g. a 'Post' model.
 *
 * FeedMeItemModelExtension
 */
class FeedMeItemModelExtension extends FeedMeModelExtension implements FeedMeInterface {

	const BodyFieldName = 'FeedMeBody';
	const BodyFieldType = 'HTMLText';

	// back relationship to the Feed this item 'belongs' to.
	const RelationshipNameDefault = 'FeedMeFeed';

	const FieldMapConfigVariable = 'feedme_field_map';

	const FieldTypesConfigVariable = 'feedme_field_types';

	const RelationshipNameConfigVariable = 'feedme_feed_relationship';

	const InjectorServiceName = 'FeedMeItemModel';

	private static $feedme_feed_relationship = self::RelationshipNameDefault;

	// map from feed source to intermediate field representation
	private static $mappable_map = [];

	// default field map from 'neutral' name to model field name, these should be set in config
	// of the extended model class where fields which already exist are going ot be used instead of
	// fields added by this extension
	private static $feedme_field_map = [
		self::TitleFieldName         => self::TitleFieldName,              // override in config for extended model
		self::BodyFieldName          => self::BodyFieldName,                     // override in config for extended model
		self::ExternalIDFieldName    => self::ExternalIDFieldName,         // default map
		self::LinkFieldName          => self::LinkFieldName,                     // default map
		self::LastPublishedFieldName => self::LastPublishedFieldName,    // default map
		self::AuthorFieldName        => self::AuthorFieldName,
		self::SourceFieldName        => self::SourceFieldName,
		self::ImageURLFieldName      => self::ImageURLFieldName,
	];

	// default field types incase we need to create fields on the model
	private static $feedme_field_types = [
		// NB no FeedID field as this is made by relationship
		self::TitleFieldName         => self::TitleFieldType,
		self::BodyFieldName          => self::BodyFieldType,
		self::ExternalIDFieldName    => self::ExternalIDFieldType,
		self::LinkFieldName          => self::LinkFieldType,
		self::LastPublishedFieldName => self::LastPublishedFieldType,
		self::AuthorFieldName        => self::AuthorFieldType,
		self::SourceFieldName        => self::SourceFieldType,
		self::ImageURLFieldName      => self::ImageURLFieldType,
	];

	public function feedMeFeedFieldName() {
		return $this->getModelFieldName( $this->feedMeRelationship() );
	}

}