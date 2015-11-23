<?php

/**
 * HTML Feed abstract class which should be superclassed for a specific HTML
 * feed instance as the xpath to items and the map from items to domain model
 * will probably always be instance specific unlike ATOM and RSS where the
 * xpath is a defined standard.
 */
abstract class FeedMeHTMLFeed extends FeedMeHTMLFeedIterator {
	/**
	 * Load $url as a DOMDocument via loadHTMLFile and initialise items
	 * from xpath.
	 *
	 * @param $url
	 * @param $xpath
	 */
	public function __construct($url, $xpath) {
		$doc = new DOMDocument();
		$doc->loadHTMLFile($url);
		$query = new DOMXPath($doc);
		$this->items = $query->query($xpath);
	}
}