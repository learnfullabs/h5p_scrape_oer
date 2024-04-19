<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'ProcessKnowledgeField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_knowledge_field"
 * )
 */
class ProcessKnowledgeField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$knowledgeFormatItem = $row->getSourceProperty("field_traditional_knowledge_labe");

    if (empty($knowledgeFormatItem)) {
      return [];
    }

    $knowledgeList = explode("|", $knowledgeFormatItem);
    $termIds = [];

    foreach ($knowledgeList as $id => $value) {
      if (!empty($value)) {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'traditional_knowledge_label', 'name' => $value]);
  
        /* If term exists, just assign the tid to the array */
        if ($terms) {
          $term = reset($terms);
  
          $termIds[] = $term->id();
        } else {
          // Create the terms
          $term = Term::create(['name' => $value, 'vid' => "traditional_knowledge_label"])->save(); 
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