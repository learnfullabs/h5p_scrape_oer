<?php

namespace Drupal\h5p_scrape_oer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\migrate_plus\Plugin\MigrationConfigEntityPluginManager;

/**
 * Implements the form to query OER Commons page
 */
class ScrapeOERSearchResults extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scrape_oer_search_results';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search_query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('What are you looking for?'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search in OER Commons'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if(strlen($form_state->getValue('search_query')) < 1) {
      $form_state->setErrorByName('search_query', $this->t('Please enter a valid Search Query'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage(t("Search Query is:"));
    
	  foreach ($form_state->getValues() as $key => $value) {
	    \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }
}