<?php

/**
 * Provides map function from RSS2 data to 'neutral' model.
 */

class FeedMeRSS2Feed extends FeedMeXMLFeedIterator implements FeedMeFeedInterface {
	private static $singular_name = 'RSS2 Feed';

	/**
	 * Map from RSS item data to FeedMe neutral format
	 * ready for updating domain model.
	 *
	 * @param array $itemDataFromFeed
	 * @return array 'neutral' map of field names to values not related to
	 * extended model fields
	 */
    public function map($itemDataFromFeed) {
        return array_combine(
            [
                $this->fieldMap[FeedMeItemModelExtension::TitleFieldName],
                $this->fieldMap[FeedMeItemModelExtension::BodyFieldName],
                $this->fieldMap[FeedMeItemModelExtension::ExternalIDFieldName],
                $this->fieldMap[FeedMeItemModelExtension::LinkFieldName],
                $this->fieldMap[FeedMeItemModelExtension::LastPublishedFieldName]
            ],
            [
                (string)$itemDataFromFeed['title'],
                (string)$itemDataFromFeed['description'],
                (string)$itemDataFromFeed['guid'],
                (string)$itemDataFromFeed['link'],
                (string)$itemDataFromFeed['pubDate']
            ]
        );
    }
}