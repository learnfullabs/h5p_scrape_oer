<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessSubjectApiField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_subject_api_field"
 * )
 */
class ProcessSubjectApiField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$subjectItem = trim($row->getSourceProperty("subject"));

    if (empty($subjectItem)) {
      return [];
    }
    
    if (isset($subjectItem) && !empty($subjectItem)) {
      if ($subjectItem == "Language") {
        return [5, 14];
      } else if ($subjectItem == "Biology") {
        return [42, 44];
      } else if ($subjectItem == "Business & Management") {
        return [19];
      } else if ($subjectItem == "Chemistry") {
        return [42, 46];
      } else if ($subjectItem == "Engineering & Technology") {
        return [54];
      } else if ($subjectItem == "Geosciences") {
        return [42, 48];
      } else if ($subjectItem == "Humanities") {
        return [5];
      } else {
        return [];
      }
    } else {
      return [26, 20];
    }
  }
}