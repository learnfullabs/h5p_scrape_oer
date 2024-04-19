<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessTeachingDurationField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_teaching_duration_field"
 * )
 */
class ProcessTeachingDurationField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$teachingFormatItem = $row->getSourceProperty("field_teaching_duration");

    if (empty($teachingFormatItem)) {
      return [];
    }

    $teachingList = explode("|", $teachingFormatItem);
    $termIds = [];

    foreach ($teachingList as $id => $value) {
      if (!empty($value)) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'teaching_duration', 'name' => $value]);
  
        /* If term exists, just assign the tid to the array */
        if ($terms) {
          $term = reset($terms);
  
          $termIds[] = $term->id();
        } else {
          // Create the terms
          $term = Term::create(['name' => $value, 'vid' => "teaching_duration"])->save(); 
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