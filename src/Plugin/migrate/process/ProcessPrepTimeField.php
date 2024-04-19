<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessPrepTimeField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_prep_time_field"
 * )
 */
class ProcessPrepTimeField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$prepFormatItem = $row->getSourceProperty("field_prep_time");

    if (empty($prepFormatItem)) {
      return [];
    }

    $prepList = explode("|", $prepFormatItem);
    $termIds = [];

    foreach ($prepList as $id => $value) {
      if (!empty($value)) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'prep_time', 'name' => $value]);
  
        /* If term exists, just assign the tid to the array */
        if ($terms) {
          $term = reset($terms);
  
          $termIds[] = $term->id();
        } else {
          // Create the terms
          $term = Term::create(['name' => $value, 'vid' => "prep_time"])->save(); 
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