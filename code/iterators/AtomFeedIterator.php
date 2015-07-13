<?php

/**
 * Provides map function from Atom data to 'neutral' model.
 */

class FeedMeAtomFeedIterator extends FeedMeFeedIterator {
    /**
     * Return a map of FeedMe field keys to their values which can be used
     * to map feed data fields to model fields via e.g. FeedMeItemModelExtension.feedMeImport
     *
     * @param array $itemDataFromFeed
     * @return array - 'neutral' map of field names to values not related to extended model fields
     */
    public function map(array $itemDataFromFeed) {
        // default link to feed link
        $link = $this->url;

        if (isset($itemDataFromFeed['link'])) {
            // scan through link nodes and see if we can use one
            foreach ($itemDataFromFeed['link'] as $testLink) {
                switch ($testLink['rel']) {
                case 'alternate':               // drop through we want to handle rel="alternate"
                case '':                        // no rel so probably just 'link href="..." so use it
                    $link = $testLink->link;
                    break;
                default:
                    // do nothing
                }
            }
        }
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
                (string)$itemDataFromFeed['id'],
                $link,
                (string)$itemDataFromFeed['updated']
            ]
        );
    }

}