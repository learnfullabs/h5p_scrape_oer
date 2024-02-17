<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'AssignResourceIfEmpty' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "assign_resource_if_empty"
 * )
 */
class AssignResourceIfEmpty extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$resourceItem = $row->getSourceProperty("field_resource_type");
    
    if (isset($resourceItem) && !empty($resourceItem)) {
      return $resourceItem;
    } else {
      return "Other";
    }
  }
}