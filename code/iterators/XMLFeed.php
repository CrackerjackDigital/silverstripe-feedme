<?php
abstract class FeedMeXMLFeedIterator extends FeedMeFeedIterator {
	const ContentType = 'xml';

	public static function load($url, $xpath) {
		if ($doc = simplexml_load_file($url)) {
			return $doc->xpath($xpath);
		}
	}
	/**
	 * Return item at index $index.
	 * @param $index
	 * @return \DOMNode
	 */
	public function item($index) {
		return $this->items[$index];
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
		return $this->index < count($this->items);
	}


}