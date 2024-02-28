<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides an 'AssignPrepTimeIfEmpty' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "assign_prep_time_if_empty"
 * )
 */
class AssignPrepTimeIfEmpty extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$prepTime = $row->getSourceProperty("field_prep_time");
    
    if (isset($prepTime) && !empty($prepTime)) {
      return $prepTime;
    } else {
      return "Unknown";
    }
  }
}