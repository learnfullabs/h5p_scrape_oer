<?php

namespace Drupal\h5p_scrape_oer;

/**
 * String Term Parser.
 *
 * Provides a class that parses taxonomy strings from the Excel spreadsheets
 */
class StringTermParser {


  /**
   * Array of the Term IDs corresponding the term nams.
   *
   * @var array
   */
  protected $termIds;

  /**
   * Array of term names and its relations
   *
   * @var array
   */
  protected $termString;

  /**
   * Vocabulary name
   *
   * @var array
   */
  protected $vocabularyName;

  /**
   * Constructor for StringTermParser objects.
   */
  public function __construct($vocabulary_name) {
    $this->termIds = [];
    $this->termString = "";
    $this->vocabularyName = $vocabulary_name;
  }

  private function processTermString() {
    // start with an empty array
    // parse the string, explode it with |
    $initialTerms = explode("|", $this->termString);

    // for each exploded substring, attempt to explode it again by using ->
    foreach ($initialTerms as $singleTerm) {
      $nextTerms = explode("->", $singleTerm);
    }

    // if no results, it means that these are either parent terms or child terms
    if (!$nextTerms) {
      // loop through these substrings and find the term ids, add these ids to the array
      foreach ($initialTerms as $id => $value) {
        $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

        $terms = $storage->loadByProperties([
          'name' => $value,
          'vid' => $this->vocabularyName,
        ]);

        if ($terms) {
          $term = reset($terms);

          $termIds[] = $term->id();
        }
        
      }
    } else {
      // if there are results when exploding ->, these will be arrays with two or three strings,
      // the 1st string is the parent, the second is the child term, and the third
      // array will be the child of the previous (child term)
      // loop through these substrings and find out the term id
      // add the ids in the array, in the same order
      foreach ($nextTerms as $singleTerm) {

      }
    }

  
    // return the array.
  }

  public function returnTermIds($term_string = "") {
    $this->$termString = $term_string;
    $this->processTermString();

    return $this->termIds;
  }

}
