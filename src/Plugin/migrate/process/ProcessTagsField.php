<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessTagsField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_tags_field"
 * )
 */
class ProcessTagsField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$tagsItem = $row->getSourceProperty("field_tags");

    if (empty($tagsItem)) {
      return [];
    }

    $tagsList = explode("|", $tagsItem);
    $termIds = [];

    foreach ($tagsList as $id => $value) {
      // Some elements of the $tagsList array are empty, we need to test
      // that before doing any operation with terms
      if (!empty($value)) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'tags', 'name' => trim($value)]);

        /* If term exists, just assign the tid to the array */
        if ($terms) {
          $term = reset($terms);
  
          $termIds[] = $term->id();
        } else {
          // Create the terms
          $term = Term::create(['name' => $value, 'vid' => "tags"])->save(); 
          $termIds[] = $term;
        }
      }
    }
    
    if (count($termIds) > 0) {
      return $termIds;
    } else {
      return [224];
    }
  }
}