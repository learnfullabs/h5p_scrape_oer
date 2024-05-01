<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessLanguageApiField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_language_api_field"
 * )
 */
class ProcessLanguageApiField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$languageItem = trim($row->getSourceProperty("default_language"));

    if (empty($languageItem)) {
      return 191;
    }
    
    if (isset($languageItem) && !empty($languageItem)) {
      if ($languageItem == "fr") {
        return 191;
      } else if ($languageItem == "en") {
        return 188;
      } else if ($languageItem == "es") {
        return 196;
      } else if ($languageItem == "tk") {
        return 197;
      } else if ($languageItem == "pt") {
        return 195;
      } else if ($languageItem == "it") {
        return 193;
      } else if ($languageItem == "de") {
        return 192;
      } else if ($languageItem == "zh") {
        return 190;
      } else if ($languageItem == "ar") {
        return 189;
      } else {
        return 198;
      }
    } else {
      return 191;
    }
  }
}