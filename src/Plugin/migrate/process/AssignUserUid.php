<?php

namespace Drupal\h5p_scrape_oer\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides an 'AssignUserUid' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "assign_user_uid"
 * )
 */
class AssignUserUid extends ProcessPluginBase {

  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  	$userEmail = $row->getSourceProperty("uid");
    
    if (isset($userEmail) && !empty($userEmail)) {
      /* Load User by Email */
      $users = \Drupal::entityTypeManager()->getStorage('user')
      ->loadByProperties(['mail' => $userEmail]);

      $user = reset($users);

      if ($user) {
        $uid = $user->id();
        
        return $uid;
      } else {
        return 1;
      }
    } else {
      return 1;
    }
  }
}