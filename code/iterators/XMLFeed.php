<?php
abstract class FeedMeXMLFeedIterator extends FeedMeFeedIterator {
	const ContentType = 'xml';

	public function __construct($url, $xpath) {
		parent::__construct($url, $xpath);
		$doc = simplexml_load_file($url);
		$this->items = $doc->xpath($xpath);
	}
}