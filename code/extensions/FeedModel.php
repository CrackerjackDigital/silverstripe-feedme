<?php

use Modular\Interfaces\Mappable as MappableInterface;
use Modular\Traits\enabler;
use Modular\Traits\mappable_model;

class FeedMeFeedModelExtension extends FeedMeModelExtension {
	use mappable_model;
	use enabler;

	const DescriptionFieldName  = 'FeedMeDescription';
	const DescriptionFieldType  = 'HTMLText';
	const FeedTypeFieldName     = 'FeedMeFeedType';
	const FeedTypeFieldType     = 'Varchar(32)';
	const XPathFieldName        = 'FeedMeXPath';
	const XPathFieldType        = 'Text';
	const ValidateFeedFieldName = 'ValidateFeed';   // no type this is ephemeral

	// relationship to feed items.
	const RelationshipNameDefault = 'FeedMeItems';

	const FieldMapConfigVariable = 'feedme_field_map';

	const FieldTypesConfigVariable = 'feedme_field_types';

	const RelationshipNameConfigVariable = 'feedme_item_relationship';

	const InjectorServiceName = 'FeedMeFeedModel';

	private static $db = [
		self::FeedTypeFieldName => self::FeedTypeFieldType,
		self::XPathFieldName    => self::XPathFieldType,
	];

	// if true feed will be imported onAfterWrite of extended Feed object.
	private static $feedme_import_on_write = true;

	private static $feedme_item_relationship = self::RelationshipNameDefault;

	// default field map from 'neutral' name to model field name, these can be reset in config
	// of the extended model class where fields which already exist are going ot be used instead of
	// fields added by default by this extension
	private static $feedme_field_map = [
		self::TitleFieldName         => self::TitleFieldName,                   // map to extended classes Title field
		self::DescriptionFieldName   => self::DescriptionFieldName,       // map to extended classes Description field
		self::ExternalIDFieldName    => self::ExternalIDFieldName,         // default map
		self::LinkFieldName          => self::LinkFieldName,                     // default map
		self::LastPublishedFieldName => self::LastPublishedFieldName,   // default map
		self::FeedTypeFieldName      => self::FeedTypeFieldName,
		self::XPathFieldName         => self::XPathFieldName,
		self::ImageURLFieldName      => self::ImageURLFieldName,
	];

	// default field types incase we need to create fields on the model
	private static $feedme_field_types = [
		self::TitleFieldName         => self::TitleFieldType,
		self::DescriptionFieldName   => self::DescriptionFieldType,
		self::ExternalIDFieldName    => self::ExternalIDFieldType,
		self::LinkFieldName          => self::LinkFieldType,
		self::LastPublishedFieldName => self::LastPublishedFieldType,
		self::FeedTypeFieldName      => self::FeedTypeFieldType,
		self::XPathFieldName         => self::XPathFieldType,
		self::ImageURLFieldName      => self::ImageURLFieldType,
	];

	public function mappableSourcePathDelimiter() {
		return '/';
	}

	/**
	 * Add URL field and a read-only External ID field to CMS form.
	 *
	 * @param FieldList $fields
	 */
	public function updateCMSFields( FieldList $fields ) {
		$descriptionFieldName = $this->getModelFieldName( self::DescriptionFieldName );

		$fields->insertBefore(
			new TextField( $this->linkFieldName(), 'Feed URL' ),
			$descriptionFieldName
		);
		$fields->insertAfter(
			new DropdownField( self::FeedTypeFieldName, 'Type', static::feed_types() ),
			$descriptionFieldName
		);
		$fields->insertAfter(
			$field = new TextField( self::XPathFieldName, 'XPath' ),
			$descriptionFieldName
		);
		$field->setAttribute( 'placeholder', $this->defaultXPath() );

		$fields->insertAfter(
			new CheckboxField( self::ValidateFeedFieldName, 'Validate feed', true ),
			$descriptionFieldName
		);

		if ( $idFieldName = $this->externalIDFieldName() ) {
			if ( $externalID = $this->owner->$idFieldName ) {
				$fields->push(
					new LiteralField( $externalID, 'Feed ID', "<p>$externalID</p>" )
				);
			}
		}
	}

	/**
	 * Check we can handle the feed (format etc), if not throw a Validation exception.
	 */
	public function validate( ValidationResult $result ) {
		parent::validate( $result );
		// if no specified xpath then use the default, if no default then
		// throw validation exception
		if ( $this->owner->ValidateFeed && ! $xpath = $this->getFeedXPath() ) {
			if ( ! $xpath = $this->defaultXPath() ) {
				$result->error(
					_t( 'FeedMe.MissingXPathMessage',
						'Please provide a source xpath for the feed (no default provided)'
					)
				);
			}
		}
		// test the feed to see if we get any items back
		if ( $this->owner->ValidateFeed && ! $this->feed() ) {
			throw new ValidationException(
				_t( 'FeedMe.InvalidFeedMessage',
					'Bad feed {url}, check address or the correct type from: {types}',
					array(
						'url'   => $this->getFeedURL(),
						'types' => implode( ',', static::feed_types() ),
					)
				)
			);
		}

		return $result;
	}

	/**
	 * If no xpath provided then update to the default for the Feed Type if there is one.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ( ! $this->getFeedXPath() ) {
			if ( $xpath = $this->defaultXPath() ) {
				$this->owner->{$this->xpathFieldName()} = $xpath;
			}
		}
	}

	/**
	 * Import feed after write if owner.config.feedme_import_on_write is true.
	 */
	public function onAfterWrite() {
		if ( $this->enabled() ) {
			$this->feedMeImport();
		}
	}

	/**
	 * Use self.digest to obtain an iterator of feed item Models and then either update or add them to
	 * the Posts relationship depending on match on Post.ExternalID.
	 */
	public function feedMeImport() {
		/** @var FeedMeFeedIterator $items */
		// feed is an iterator of it's items.
		if ( $feed = $this->feed() ) {
			echo "read feed, importing items\n";

			$relationshipName = $this->feedMeRelationship();

			$externalIDFieldName = Injector::inst()->get( 'FeedMeItemModel' )->externalIDFieldName();

			/** @var HasManyList $existingItems */
			$existingItems = $this->owner->$relationshipName();

			/** @var DataObject $itemModel , iterator turns feed items into a domain model */
			foreach ( $feed as $itemModel ) {
				// if post with the ExternalID already exists then update that one.
				if ( $found = $existingItems->find( $externalIDFieldName, $itemModel->{$externalIDFieldName} ) ) {
					echo "updating item '$itemModel->Title'\n";

					// update the found one from map of the new one
					$found->update( $itemModel->toMap() );

					if ( $found->isChanged() ) {
						// we changed so write out
						$found->write();
					}
				} else {
					echo "adding new item '$itemModel->Title'\n";

					$itemModel->write();

					/// not found add as a new post.
					$this->owner->$relationshipName()->add( $itemModel );
				}
			}
		}
	}

	/**
	 * Returns a map of known feed types (implementors of FeedMeFeedInterface)
	 * suitable for use in a dropdown field.
	 *
	 * @return array [ ClassName => Label ]
	 */
	public static function feed_types() {
		$types = [];
		foreach ( ClassInfo::implementorsOf( 'FeedMeFeedInterface' ) as $className ) {
			$types[ $className ] = Config::inst()->get( $className, 'singular_name' ) ?: $className;
		}

		return $types;
	}

	/**
	 * Returns map from 'neutral' field names to model properties for this model
	 * from config.feedme_field_map.
	 *
	 * @return array
	 */
	public static function field_map() {
		return Config::inst()->get( get_called_class(), 'feedme_field_map' );
	}

	/**
	 * Read the feed from remote source using owner.FeedMeLinkFieldName and
	 * owner.FeedMeFeedType. Return feed item Traversable with items mapped to
	 * domain model, for example a FeedMeAtomFeedIterator for an atom feed.
	 *
	 * @return \FeedMeFeedInterface
	 * @throws \FeedMeException
	 */
	protected function feed() {
		$url       = $this->getFeedURL();
		$xpath     = $this->getFeedXPath() ?: $this->defaultXPath();
		$feedClass = $this->feedClassName();

		if ( $url && $xpath && $feedClass ) {
			$itemModel = Injector::inst()->get( 'FeedMeItemModel' );
			$fieldMap  = $itemModel->fieldMap();

			return Injector::Inst()->create( $feedClass, $url, $xpath, $fieldMap );
		} else {
			throw new FeedMeException( _t( "FeedMe.MissingInfoException", 'Missing info for feed {title}', [ 'title' => $this->owner->Title ] ) );
		}
	}

	/**
	 * Return default xpath defined on the Feed class.
	 *
	 * @return string|null
	 */
	protected function defaultXPath() {
		if ( $feedClassName = $this->feedClassName() ) {
			return Config::inst()->get( $feedClassName, 'xpath' );
		}
	}

	/**
	 * Return the name of class which handles fields of the field Type, in this
	 * case the type is the class.
	 *
	 * @return string
	 */
	protected function feedClassName() {
		$feedType = $this->getFeedType();

		return $feedType;
	}

	/**
	 * Returns the value of the URL field on the extended class.
	 *
	 * @return string
	 */
	protected function getFeedURL() {
		return $this->owner->{$this->linkFieldName()};
	}

	/**
	 * Returns value of the FeedType field in the extended class.
	 *
	 * @return string
	 */
	protected function getFeedType() {
		return $this->owner->{$this->feedTypeFieldName()};
	}

	/**
	 * Returns value of the XPath field in the extended class.
	 *
	 * @return string
	 */
	protected function getFeedXPath() {
		return $this->owner->{$this->xpathFieldName()};
	}

	/**
	 * Returns name of the FeedType field on the extended model
	 *
	 * @return null|string
	 */
	public function feedTypeFieldName() {
		return $this->getModelFieldName( static::FeedTypeFieldName );
	}

	/**
	 * Returns name of the XPath field on the extended model
	 *
	 * @return null|string
	 */
	public function xpathFieldName() {
		return $this->getModelFieldName( static::XPathFieldName );

	}

	/**
	 * @return \DataObject|MappableInterface
	 */
	public function model() {
		return $this->owner;
	}
}