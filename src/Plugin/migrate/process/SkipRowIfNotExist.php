<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Skips processing the current row when a destination value does not exist.
 *
 * The skip_row_if_not_exist process plugin checks whether a value exists. If the
 * value exists, it is returned. Otherwise, a MigrateSkipRowException
 * is thrown.
 *
 * Available configuration keys:
 * - entity: The destination entity to check for.
 * - property: The destination entity property to check for.
 * - message: (optional) A message to be logged in the {migrate_message_*} table
 *   for this row. If not set, nothing is logged in the message table.
 *
 * Example:
 *  Do not import comments for migrated nodes that do not exist any more at the
 *  destination.
 *
 * @code
 *  process:
 *    field_your_field_name:
 *    -
 *      plugin: skip_row_if_not_exist
 *      entity: node
 *      property: nid
 *      source: some_source_value
 *      message: 'Commented entity not found.'
 * @endcode
 *
 * This will return the node id if it exists. Otherwise, the row will be
 * skipped and the message "Commented entity not found." will be logged in the
 * message table.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "skip_row_if_not_exist",
 * )
 */
class SkipRowIfNotExist extends ProcessPluginBase {

  protected $entity_type = 'node';
  protected $value_key = 'nid';
  protected $bundle_key = '';
  protected $bundle = '';

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (!empty($configuration['entity_type'])) {
      $this->entity_type = $configuration['entity_type'];
    }

    if (!empty($configuration['value_key'])) {
      $this->value_key = $configuration['value_key'];
    }

    if (!empty($configuration['bundle_key'])) {
      $this->bundle_key = $configuration['bundle_key'];
    }

    if (!empty($configuration['bundle'])) {
      $this->bundle = $configuration['bundle'];
    }

  }
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if ($this->entity_type === 'taxonomy_term') {
      $terms = \Drupal::entityQuery($this->entity_type)
        ->condition($this->value_key, $value)
        ->condition($this->bundle_key, $this->bundle)
        ->accessCheck(FALSE)
        ->execute();
      $term = reset($terms);
      $count = empty($term) ? false : true;
    }
    else {
      $count = \Drupal::entityQuery($this->entity_type)
        ->condition($this->value_key, $value)
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    if (!$count) {
      $message = isset($this->configuration['message']) ? $this->configuration['message'].': '.$value : '';
      throw new MigrateSkipRowException($message);
    }

    return $value;
  }

}
