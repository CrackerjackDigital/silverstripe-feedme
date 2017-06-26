<?php

/**
 * Base interface for common things for FeedMeFeed models such as Field names, schemas etc.
 */
interface FeedMeInterface {
	// fields should be defined on extended object (nb not the field names, just the key to map to correct field names)
	const TitleFieldName = 'Title';
	const TitleFieldType = 'Varchar(255)';

	//
	// default field names and types, override in e.g. FeedMeItemModelExtension
	//
	const ExternalIDFieldName = 'FeedMeExternalID';
	const ExternalIDFieldType = 'Varchar(64)';

	const LinkFieldName = 'FeedMeLink';
	const LinkFieldType = 'Text';

	const LinkTitleFieldName = 'FeedMeLinkText';
	const LinkTitleFieldType = 'Varchar(255)';

	const LinkTextFieldName = 'FeedMeLinkText';
	const LinkTextFieldType = 'Text';

	const LastPublishedFieldName = 'FeedMeLastPublished';
	const LastPublishedFieldType = 'Varchar(64)';

	const AuthorFieldName = 'FeedMeAuthor';
	const AuthorFieldType = 'Varchar(255)';

	const SourceFieldName = 'FeedMeSource';
	const SourceFieldType = 'Varchar(255)';

	const ImageURLFieldName = 'FeedMeImageURL';
	const ImageURLFieldType = 'Text';

}