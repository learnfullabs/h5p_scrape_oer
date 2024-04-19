<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessMediaFormatField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_media_format_field"
 * )
 */
class ProcessMediaFormatField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$mediaFormatItem = $row->getSourceProperty("field_media_format");

    if (empty($mediaFormatItem)) {
      // Return "Other" by default
      return [178];
    }

    $mediaList = explode("|", $mediaFormatItem);
    $termIds = [];

    foreach ($mediaList as $id => $value) {
      if (!empty($value)) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'media_format', 'name' => $value]);
  
        /* If term exists, just assign the tid to the array */
        if ($terms) {
          $term = reset($terms);
  
          $termIds[] = $term->id();
        } else {
          // Create the terms
          $term = Term::create(['name' => $value, 'vid' => "media_format"])->save(); 
          $termIds[] = $term;
        }
      }
    }
    
    if (count($termIds) > 0) {
      return $termIds;
    } else {
      // Return "Other" by default
      return [178];
    }
  }
}