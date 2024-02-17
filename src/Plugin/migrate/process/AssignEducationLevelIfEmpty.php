<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'AssignEducationLevelIfEmpty' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "assign_education_level_if_empty"
 * )
 */
class AssignEducationLevelIfEmpty extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$educationLevelItem = $row->getSourceProperty("field_education_level");
    
    if (isset($educationLevelItem) && !empty($educationLevelItem)) {
      return $educationLevelItem;
    } else {
      return "All levels";
    }
  }
}