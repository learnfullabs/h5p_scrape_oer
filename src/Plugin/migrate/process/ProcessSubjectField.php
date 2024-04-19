<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\h5p_scrape_oer\StringTermParser;

/**
 * Provides a 'ProcessSubjectField' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "process_subject_field"
 * )
 */
class ProcessSubjectField extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$subjectItem = $row->getSourceProperty("field_subject");

    if (empty($subjectItem)) {
      return $subjectItem;
    }

    $termParser = new StringTermParser("subjects");
    
    if (isset($subjectItem) && !empty($subjectItem)) {
      return $termParser->returnTermIds($subjectItem);
    } else {
      return [26, 20];
    }
  }
}