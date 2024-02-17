<?php

namespace Drupal\h5p_scrape_oer;

/**
 * String Term Parser.
 *
 * Provides a class that parses taxonomy strings from the Excel spreadsheets
 */
class StringTermParser {


  /**
   * Modules to enable.
   *
   * @var array
   */
  protected $termIds;

  /**
   * Constructor for StringTermParser objects.
   */
  public function __construct() {
    $this->termIds = [];
  }

  private function processTermString() {
    // start with an empty array
    // parse the string, explode it with |
    // for each exploded substring, attempt to explode it again by using ->
    // if no results, it means that these are either parent terms or child terms
    // loop through these substrings and find the term ids, add these ids to the array
    // if there are results when exploding ->, these will be arrays with two or three strings,
    // the 1st string is the parent, the second is the child term, and the third
    // array will be the child of the previous (child term)
    // loop through these substrings and find out the term id
    // add the ids in the array, in the same order
    // return the array.
  }

  public function returnTermIds() {
    return $termIds;
  }

}
