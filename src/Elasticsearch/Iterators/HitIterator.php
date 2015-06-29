<?php

namespace Elasticsearch\Iterators;

use Iterator;

/**
 * Class HitIterator
 *
 * @category Elasticsearch
 * @package  Elasticsearch\Iterators
 * @author   Arturo Mejia <arturo.mejia@kreatetechnology.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache2
 * @link     http://elasticsearch.org
 * @see      Iterator
 */
class HitIterator implements Iterator {

    /**
     * @var PageIterator
     */
    private   $page_iterator;

    /**
     * @var int
     */
    protected $current_key;

    /**
     * @var int
     */
    protected $current_hit_index;

    /**
     * @var array|null
     */
    protected $current_hit_data;

    /**
     * Constructor
     *
     * @param PageIterator $page_iterator
     */
    public function __construct(PageIterator $page_iterator)
    {
        $this->page_iterator = $page_iterator;
    }

    /**
     * Rewinds the internal PageIterator and itself
     *
     * @return void
     * @see    Iterator::rewind()
     */
    public function rewind()
    {
        $this->current_key = 0;
        $this->page_iterator->rewind();

        // The first page may be empty. In that case, the next page is fetched.
        $current_page = $this->page_iterator->current();
        if($this->page_iterator->valid() && empty($current_page['hits']['hits'])) {
            $this->page_iterator->next();
        }

        $this->readPageData();
    }

    /**
     * Advances pointer of the current hit to the next one in the current page. If there
     * isn't a next hit in the current page, then it advances the current page and moves the
     * pointer to the first hit in the page.
     *
     * @return void
     * @see    Iterator::next()
     */
    public function next()
    {
        $this->current_key++;
        $this->current_hit_index++;
        $current_page = $this->page_iterator->current();
        if(isset($current_page['hits']['hits'][$this->current_hit_index])) {
            $this->current_hit_data = $current_page['hits']['hits'][$this->current_hit_index];
        } else {
            $this->page_iterator->next();
            $this->readPageData();
        }
    }

    /**
     * Returns a boolean indicating whether or not the current pointer has valid data
     *
     * @return bool
     * @see    Iterator::valid()
     */
    public function valid()
    {
        return is_array($this->current_hit_data);
    }

    /**
     * Returns the current hit
     *
     * @return array
     * @see    Iterator::current()
     */
    public function current()
    {
        return $this->current_hit_data;
    }

    /**
     * Returns the current hit index. The hit index spans all pages.
     *
     * @return int
     * @see    Iterator::key()
     */
    public function key()
    {
        return $this->current_hit_index;
    }

    /**
     * Advances the internal PageIterator and resets the current_hit_index to 0
     *
     * @internal
     */
    private function readPageData()
    {
        if($this->page_iterator->valid()) {
            $current_page = $this->page_iterator->current();
            $this->current_hit_index = 0;
            $this->current_hit_data = $current_page['hits']['hits'][$this->current_hit_index];
        } else {
            $this->current_hit_data = null;
        }

    }
}
