<?php

/**
 * Provides map function from Atom data to 'neutral' model.
 */
class FeedMeAtomFeed extends FeedMeXMLFeedIterator implements FeedMeFeedInterface {

	private static $singular_name = 'Atom Feed';

	private static $xpath = '//feed/entry';

	/**
	 * Map from Atom item data to FeedMe neutral format
	 * ready for updating domain model.
	 *
	 * @param \SimpleXMLElement $xmlElement
	 *
	 * @return array 'neutral' map of field names to values not related to extended model fields
	 */
	public function map( $xmlElement ) {
		$link = $this->getFeedURL();

		$itemDataFromFeed = (array) $xmlElement;

		if ( isset( $itemDataFromFeed['link'] ) ) {
			// scan through link nodes and see if we can use one
			foreach ( $itemDataFromFeed['link'] as $testLink ) {
				switch ( $testLink['rel'] ) {
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
				$this->fieldMap[ FeedMeItemModelExtension::TitleFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::ContentFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::ExternalIDFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LinkFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LastPublishedFieldName ],
			],
			[
				(string) $itemDataFromFeed['title'],
				(string) $itemDataFromFeed['description'],
				(string) $itemDataFromFeed['id'],
				$link,
				(string) $itemDataFromFeed['updated'],
			]
		);
	}

}