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
   * @var string
   */
  protected $termString;

  /**
   * Vocabulary name
   *
   * @var string
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

  /**
   * Process the Term String from the XLS file (which has a special format)
   * and returns an array of term ids.
   *
   * @return array
   *   Array of term ids.
   */
  private function processTermString() {
    // start with an empty array
    // parse the string, explode it with |
    $initialTerms = explode("|", $this->termString);
    $termIds = [];

    foreach ($initialTerms as $id => $value) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $this->vocabularyName, 'name' => $value]);

      if ($terms) {
        $term = reset($terms);

        $termIds[] = $term->id();
      }
    }

    // Check if there are parent -> child items
    if (strpos($this->termString, "->") > 0) {
      // for each exploded substring, attempt to explode it again by using ->
      foreach ($initialTerms as $singleTerm) {
        $nextTerms = explode("->", $singleTerm);

        foreach ($nextTerms as $id => $value) {
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
      }
    }

    $this->termIds = $termIds;
  }

  /**
   * Wrapper method for processTermString() 
   *
   * @return array
   *   Array of term ids.
   */
  public function returnTermIds($term_string = "") {
    $this->termString = $term_string;
    $this->processTermString();

    return $this->termIds;
  }

}
