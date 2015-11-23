<?php

/**
 * HTML Feed abstract class which should be superclassed for a specific HTML
 * feed instance as the xpath to items and the map from items to domain model
 * will probably always be instance specific unlike ATOM and RSS where the
 * xpath is a defined standard.
 */
abstract class FeedMeHTMLFeed extends FeedMeHTMLFeedIterator {
	/** @var  DOMDocument */
	private $doc;

	public function __construct($url) {
		parent::__construct($url);
		$this->doc = new DOMDocument();
		$this->doc->loadHTMLFile($url);
	}
}