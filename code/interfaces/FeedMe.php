<?php

/**
 * Base interface for common things for FeedMeFeed models such as Field names, schemas etc.
 */
interface FeedMeInterface {
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

	const AuthorFieldName = 'FeedMeAuthor';
	const AuthorFieldType = 'Varchar(255)';

	const SourceFieldName = 'FeedMeSource';
	const SourceFieldType = 'Varchar(255)';

	const ImageURLFieldName = 'ImageURL';
	const ImageURLFieldType = 'Text';

}