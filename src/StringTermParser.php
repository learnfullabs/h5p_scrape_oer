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

    foreach ($initialTerms as $id => $value) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $this->vocabularyName, 'name' => $value]);

      if ($terms) {
        $term = reset($terms);

        $termIds[] = $term->id();
      }
    }

    // 
    if (strpos($this->termString, "->") > 0) {
      // for each exploded substring, attempt to explode it again by using ->
      foreach ($initialTerms as $singleTerm) {
        $nextTerms = explode("->", $singleTerm);
      }

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

    $this->termIds = $termIds;
  }

  public function returnTermIds($term_string = "") {
    $this->termString = $term_string;
    $this->processTermString();

    return $this->termIds;
  }

}
