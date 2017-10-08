<?php

abstract class FeedMeXMLFeedIterator extends FeedMeFeedIterator {
	const ContentType = 'xml';

	public static function load( $url, $xpath ) {
		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.0", // something like Firefox
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 10,      // timeout on connect
			CURLOPT_TIMEOUT        => 10,      // timeout on response
			CURLOPT_MAXREDIRS      => 5,       // stop after 10 redirects
		);
		$curl    = curl_init();
		curl_setopt_array( $curl, $options );
		$content = curl_exec( $curl );
		curl_close( $curl );

		libxml_use_internal_errors(true);

		if ( $doc = simplexml_load_string( $content ) ) {
			return $doc->xpath( $xpath );
		}
	}

	/**
	 * Return item at index $index.
	 *
	 * @param $index
	 *
	 * @return \DOMNode
	 */
	public function item( $index ) {
		return isset( $this->items[ $index ] ) ? $this->items[ $index ] : null;
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
		return isset( $this->items ) ? ( $this->index < count( $this->items ) ) : false;
	}

}