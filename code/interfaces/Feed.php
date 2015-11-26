<?php

/**
 * Implement this interface on a class to have it show in the 'Feed Type'
 * dropdown in the CMS for a given feed instance.
 */
interface FeedMeFeedInterface {
	/**
	 * Return a 'neutral' map of FeedMe field keys to their values which can
	 * be used to map feed data fields to model fields via
	 * e.g. FeedMeItemModelExtension.feedMeImport
	 *
	 * @param $itemDataFromFeed
	 * @return array 'neutral' map of field names to values not related to
	 * extended model fields
	 */
	public function map($itemDataFromFeed);

	/**
	 * Load the url and return items found using xpath XPath query.
	 * @param $url
	 * @param $xpath
	 * @return Traversable|array|null
	 */
	public static function load($url, $xpath);
}