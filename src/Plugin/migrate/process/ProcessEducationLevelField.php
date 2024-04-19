<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessEducationLevelField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_education_level_field"
 * )
 */
class ProcessEducationLevelField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$educationLevelItem = $row->getSourceProperty("field_education_level");

    if (empty($educationLevelItem)) {
      return $educationLevelItem;
    }

    $termParser = new StringTermParser("education_level");
    
    if (isset($educationLevelItem) && !empty($educationLevelItem)) {
      return $termParser->returnTermIds($educationLevelItem);
    } else {
      return [];
    }
  }
}