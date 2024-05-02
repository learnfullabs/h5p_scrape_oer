<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessAuthorsApiField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_authors_api_field"
 * )
 */
class ProcessAuthorsApiField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$authorsItem = trim($row->getSourceProperty("shared_by"));

    if (empty($authorsItem)) {
      return [];
    }  

    $termIds = [];

    if (!empty($authorsItem)) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'authors', 'name' => $authorsItem]);

      /* If term exists, just assign the tid to the array */
      if ($terms) {
        $term = reset($terms);

        $termIds[] = $term->id();
      } else {
        // Create the terms
        $term = Term::create(['name' => $authorsItem, 'vid' => "authors"])->save(); 
        $termIds[] = $term;
      }
    }

    if (count($termIds) > 0) {
      return $termIds;
    } else {
      return [];
    }
  }
}