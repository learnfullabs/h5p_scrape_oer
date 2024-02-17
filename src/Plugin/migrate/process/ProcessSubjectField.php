<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'ProcessSubjectField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_subject_field"
 * )
 */
class ProcessSubjectField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$subjectItem = $row->getSourceProperty("field_subject");

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
    
    /* TODO: Here we process the field_subject field and look for parent-child terms */
    /* if (isset($subjectItem) && !empty($subjectItem)) {
      return $subjectItem;
    } else {
      return "Support Resources";
    } */

    // return [26, 20];
    return $subjectItem;
  }
}