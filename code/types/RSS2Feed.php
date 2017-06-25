<?php

/**
 * Provides map function from RSS2 data to 'neutral' model.
 */
class FeedMeRSS2Feed extends FeedMeXMLFeedIterator implements FeedMeFeedInterface {
	private static $singular_name = 'RSS2 Feed';

	private static $xpath = '//channel/item';

	/**
	 * Map from RSS item data to FeedMe neutral format
	 * ready for updating domain model.
	 *
	 * @param SimpleXMLElement $xmlElement
	 *
	 * @return array 'neutral' map of field names to values not related to
	 * extended model fields
	 */
	public function map( $xmlElement ) {

		$fieldData = (array) $xmlElement;

		$fields = array_combine(
			[
				$this->fieldMap[ FeedMeItemModelExtension::TitleFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::BodyFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::ExternalIDFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LinkFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LastPublishedFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::AuthorFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::SourceFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::ImageURLFieldName ],
			],
			[
				(string) $fieldData['title'],
				(string) $fieldData['description'],
				(string) $fieldData['guid'],
				(string) $fieldData['link'],
				(string) $fieldData['pubDate'],
				isset( $fieldData['author'] ) ? (string) isset( $fieldData['author'] ) : '',
				isset( $fieldData['source'] ) ? (string) isset( $fieldData['source'] ) : '',
				'',
			]
		);
		// now add image if one present
		if ( isset( $xmlElement->enclosure ) ) {
			$enclosure = $xmlElement->enclosure;
			$url       = (string) $enclosure['url'];
			$type      = (string) $enclosure['type'];

			$mimeType = $type ?: ( new Mimey\MimeTypes() )->getMimeType( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			if ( $mimeType ) {
				if ( substr( $mimeType, 0, 5 ) == 'image' ) {
					$fields[ $this->fieldMap[ FeedMeItemModelExtension::ImageURLFieldName ] ] = $url;
				}
			}
		}
		return $fields;
	}
}