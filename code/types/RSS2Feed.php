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
				$this->fieldMap[ FeedMeItemModelExtension::ContentFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::ExternalIDFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LastPublishedFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::AuthorFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::SourceFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LinkFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LinkTitleFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::LinkTextFieldName ],
				$this->fieldMap[ FeedMeItemModelExtension::ImageURLFieldName ],
			],
			[
				isset( $fieldData['title'] ) ? (string) $fieldData['title'] : '',
				isset( $fieldData['description'] ) ? (string) $fieldData['description'] : '',
				isset( $fieldData['guid'] ) ? (string) $fieldData['guid'] : '',
				isset( $fieldData['pubDate'] ) ? (string) $fieldData['pubDate'] : '',
				isset( $fieldData['author'] ) ? (string) $fieldData['author'] : '',
				isset( $fieldData['source'] ) ? (string) $fieldData['source'] : '',
				isset( $fieldData['link'] ) ? (string) $fieldData['link'] : '',
				isset( $fieldData['title'] ) ? (string) $fieldData['title'] : '',
				isset( $fieldData['title'] ) ? (string) $fieldData['title'] : '',
				'', // empty image link out if not set in incoming feed data
			]
		);
		// now add image if one present
		if ( isset( $xmlElement->enclosure ) ) {
			$enclosure = $xmlElement->enclosure;
			$imageURL  = (string) $enclosure['url'];
			$imageType = (string) $enclosure['type'];

			$mimeType = $imageType ?: ( new Mimey\MimeTypes() )->getMimeType( pathinfo( parse_url( $imageURL, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			if ( $mimeType ) {
				if ( substr( $mimeType, 0, 5 ) == 'image' ) {
					// try without a '_rss' in the file name
					$mangled = str_replace( '_rss.', '.', $imageURL );

					if ( ( $mangled != $imageURL ) && get_headers( $mangled, true ) ) {
						$imageURL = $mangled;
					}
					$fields[ $this->fieldMap[ FeedMeItemModelExtension::ImageURLFieldName ] ] = $imageURL;
					echo "added image '$imageURL'\n";
				}
			}
		}

		return $fields;
	}
}