<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessSourceField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_source_field"
 * )
 */
class ProcessSourceField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$sourceItem = trim($row->getSourceProperty("field_source"));
    
    if (isset($sourceItem) && !empty($sourceItem)) {
      if (($sourceItem == "File Upload") || ($sourceItem == "File upload")) {
        return "file_upload";
      } else {
        return "link";
      }
    } else {
      return "link";
    }
  }
}