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
	 * @return \Traversable
	 */
	public static function load($url, $xpath) {
		if ($doc = new DOMDocument()) {
			$doc->validateOnParse = false;
			$doc->xmlStandalone = true;
			$doc->preserveWhiteSpace = false;
			$doc->recover = true;
			$doc->strictErrorChecking = false;
			libxml_use_internal_errors(true);

			if ($doc->loadHTMLFile($url)) {
				$query = new DOMXPath($doc);
				return $query->query($xpath);
			}
		}
	}

	/**
	 * Return item of DOMNodeList at index $index.
	 *
	 * @param $index
	 * @return \DOMNode
	 */
	public function item($index) {
		return $this->items->item($index);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then
	 *                 evaluated. Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->index < $this->items->length;
	}



	/**
	 * @param DOMElement $itemDataFromFeed
	 * @return array
	 */
	public function map($itemDataFromFeed) {
		$map = array_fill_keys(
			[
				$this->fieldMap[FeedMeItemModelExtension::TitleFieldName],
				$this->fieldMap[FeedMeItemModelExtension::BodyFieldName],
				$this->fieldMap[FeedMeItemModelExtension::ExternalIDFieldName],
				$this->fieldMap[FeedMeItemModelExtension::LinkFieldName],
				$this->fieldMap[FeedMeItemModelExtension::LastPublishedFieldName],
			],
			''
		);
		if ($itemDataFromFeed->hasChildNodes()) {
			$this->children($itemDataFromFeed->childNodes, $map);
		}
		return $map;
	}

	/**
	 * Handle mapping of a child node (a news item) using
	 * config.tag_to_neutral_map to neutral name, and special cases such as 'a'
	 * from 'href' attribute.
	 *
	 * @param \DOMNodeList $children
	 * @param array        $map  reference of neutral property map which is
	 *                           modified in place.
	 * @internal param \DOMNode $child news item
	 */
	protected function children(DOMNodeList $children, array &$map) {
		// NB: atm path_to_neutral_map is a simple tag -> property name map not xpath.
		$nodeToPropMap = Config::inst()
			->get(get_called_class(), 'path_to_neutral_map');

		foreach ($children as $child) {
			// should always be?
			if ($child instanceof DOMNode) {

				if ($child->hasChildNodes()) {
					$this->children($child->childNodes, $map);
				}

				// ignore text nodes for now.
				if ($child instanceof DOMElement) {
					$tagName = $child->tagName;

					if (isset($nodeToPropMap[$tagName]) && $nodeToPropMap[$tagName]) {
						switch ($tagName) {
						case 'a':
							// links set the link and an md5 hash of it for the ExternalID.
							$map[$this->fieldMap[$nodeToPropMap[$tagName]]] = $child->getAttribute('href');

							if (empty($map[$this->fieldMap[FeedMeItemModelExtension::ExternalIDFieldName]])) {
								// tested so if this set 'really' then don't overwrite
								$map[$this->fieldMap[FeedMeItemModelExtension::ExternalIDFieldName]] = md5($child->getAttribute('href'));
							}
							break;
						default:
							// concatenate as we could say have multiple 'p' tags.
							$map[$this->fieldMap[$nodeToPropMap[$child->tagName]]] .= (string) $child->textContent;
						}
					}
				}
			}
		}
	}

}