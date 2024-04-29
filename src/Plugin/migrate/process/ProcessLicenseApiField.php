<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessLicenseApiField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_license_api_field"
 * )
 */
class ProcessLicenseApiField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$licenseItem = trim($row->getSourceProperty("license"));

    if (empty($licenseItem)) {
      return 354;
    }
    
    if (isset($licenseItem) && !empty($licenseItem)) {
      if ($licenseItem == "CC BY") {
        return 254;
      } else if ($licenseItem == "CC BY-SA") {
        return 257;
      } else if ($licenseItem == "CC BY-ND") {
        return 261;
      } else if ($licenseItem == "CC BY-NC") {
        return 256;
      } else if ($licenseItem == "CC BY-NC-SA") {
        return 258;
      } else if ($licenseItem == "CC BY-NC-ND") {
        return 260;
      } else if ($licenseItem == "CC0") {
        return 335;
      } else if ($licenseItem == "PDM") {
        return 253;
      } else if ($licenseItem == "PD") {
        return 253;
      } else if ($licenseItem == "U") {
        return 191;
      }
    } else {
      return 191;
    }
  }
}