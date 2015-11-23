<?php
/**
 * Base class for FeedMe item iterators which return a populated model class on current() call.
 */
abstract class FeedMeFeedIterator implements Iterator {
	// override in derived class with e.g. 'xml', 'html', 'json' etc
	const ContentType = '';

    /** @var array */
    protected $items = [];

    /** @var int  */
    protected $index = 0;

    /** @var  array */
    protected $fieldMap;

    /**
     * Create a model instance using injector.FeedMeModel and then import values from
     * current iterator item via FeedMeItemModelExtension.feedMeImport
     *
     * @throws FeedMeException
     * @return DataObject|null
     */
    public function current() {
        // probably don't need to call valid as should be valid, it's cheap though.
        if ($this->valid()) {
            $item = $this->items[$this->index];

            // import the feed item via a 'neutral' map constructed by concrete class map call.
            if ($map = $this->map((array)$item)) {

	            // model should have 'FeedMeItemModelExtension' extension
	            $model = Injector::inst()->create('FeedMeItemModel', $map);

	            // give chance to patch up any extra variables from map etc
	            $model->feedMeImported($map);

	            return $model;
            } else {
	            user_error("Didn't get a valid map back for feed item", E_USER_WARNING);
            }
        }
    }

	public static function content_type() {
		return static::ContentType;
	}
    /**
     * Return a map of FeedMe field keys to their values which can be used
     * to map feed data fields to model fields via e.g. FeedMeItemModelExtension.feedMeImport
     *
     * @param $itemDataFromFeed
     * @return array - map of standard feedme columns to values , e.g 'Title' => 'Item Title'
     */
    abstract protected function map($itemDataFromFeed);

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next() {
        $this->index++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return $this->valid() ? $this->index : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid() {
        return $this->index < count($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind() {
        $this->index = 0;
    }
}