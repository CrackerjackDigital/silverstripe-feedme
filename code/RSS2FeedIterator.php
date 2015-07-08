<?php


class FeedMeRSS2FeedIterator extends FeedMeFeedIterator {
    /**
     * Return a map of FeedMe field keys to their values which can be used
     * to map feed data fields to model fields via e.g. FeedMeItemModelExtension.feedMeImport
     *
     * @param array $itemDataFromFeed
     * @return array - 'neutral' map of field names to values not related to extended model fields
     */
    public function map(array $itemDataFromFeed) {
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