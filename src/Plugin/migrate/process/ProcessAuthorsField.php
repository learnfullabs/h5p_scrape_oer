<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessAuthorsField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_authors_field"
 * )
 */
class ProcessAuthorsField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$authorsItem = $row->getSourceProperty("field_authors");

    if (empty($authorsItem)) {
      return [];
    }  

    $authorsList = explode(",", $authorsItem);
    $termIds = [];

    foreach ($authorsList as $id => $value) {
      // Some elements of the $authorsList array are empty, we need to test
      // that before doing any operation with terms
      if (!empty($value)) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'authors', 'name' => trim($value)]);

        /* If term exists, just assign the tid to the array */
        if ($terms) {
          $term = reset($terms);
  
          $termIds[] = $term->id();
        } else {
          // Create the terms
          $term = Term::create(['name' => $value, 'vid' => "authors"])->save(); 
          $termIds[] = $term;
        }
      }
    }
    
    if (count($termIds) > 0) {
      return $termIds;
    } else {
      return [];
    }
  }
}