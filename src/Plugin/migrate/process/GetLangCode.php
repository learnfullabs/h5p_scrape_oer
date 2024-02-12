<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'GetLangCode' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "get_lang_code"
 * )
 */
class GetLangCode extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$langResource = $row->getSourceProperty("language_of_resource") ?? "English";
    $langCode = "";

    if (strtolower(trim($langResource)) == "french"){
      $langCode = "fr";
    } else {
      $langCode = "en";
    }

    return $langCode;
  }
}