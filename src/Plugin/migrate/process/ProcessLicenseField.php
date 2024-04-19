<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessLicenseField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_license_field"
 * )
 */
class ProcessLicenseField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$licenseItem = $row->getSourceProperty("field_license");

    if (empty($licenseItem)) {
      return [26, 20];
    }

    $termParser = new StringTermParser("license");
    
    if (isset($licenseItem) && !empty($licenseItem)) {
      return $termParser->returnTermIds($licenseItem);
    } else {
      return [26, 20];
    }
  }
}