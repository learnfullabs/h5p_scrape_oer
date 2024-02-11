<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'GetUrlAlias' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "get_url_alias"
 * )
 */
class GetUrlAlias extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$permalink = $row->getSourceProperty("permalink");
  	$nasDir = $value;

  	$urlAlias = str_replace("https://camerisefsl.ca", "", $permalink);

  	return $urlAlias;
  }
}
