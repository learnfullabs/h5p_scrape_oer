<?php

namespace Drupal\h5p_scrape_oer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Event\MigrateEvents;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\migrate_plus\Plugin\MigrationConfigEntityPluginManager;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


/**
 * Class ImportFormAttachedDocument.
 */
class ParseResourcesSpreadsheet extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_form_attached_document';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['code'] = array(
      '#markup' => '<div class="code">
        <p>'.t('If you are going to create new attached documents, please verify that the document name does not exist yet and the project ID is different').'</p></div>',
      '#weight' => 1
    );

    $form['uploadedFile'] = [
      '#type' => 'file',
      '#title' => $this->t('File'),
      '#weight' => 3
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#weight' => 5
    ];

    $form['#attributes'] = array('enctype' => 'multipart/form-data');

    $form['description'] = array(
      '#markup' => '<div class="help">
        <h3>'.t('Download templates:').'</h3>
        <ul>
          <li><a href="/sites/default/files/migrate_templates/migration_attached_documents_es.xlsx" target="_blank">'.t('Download the Attached Document base template').'</a></li>
        </div>',
      '#weight' => 6
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $fileName = $_FILES['files']['name']['uploadedFile'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $extenciones = ['xlsx'];

    if (!in_array($fileExtension,$extenciones)){
      $form_state->setErrorByName('uploadedFile', $this->t('Invalid file format. Only xlsx is allowed'));
    }
    else {
      $file = $file = $_FILES['files']['tmp_name']['uploadedFile'];
      if ($file) {
        $novelties = loadFileAttached($file);

        if ($novelties) {
          $header = [
            'column' => $this->t('Columna'),
            'field' => $this->t('Campo'),
            'message' => $this->t('Mensaje'),
            'value' => $this->t('Valor')
          ];

          $form['mytable'] = [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $novelties,
            '#weight' => 4
          ];
          $form_state->setErrorByName('uploadedFile', $this->t('Excel tiene inconsistencias. Verifique y cargue el archivo nuevamente.'));
        }
      }
      else {
        $form_state->setErrorByName('uploadedFile', $this->t('Error al cargar el archivo.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = "es";

    $file = $_FILES['files']['tmp_name']['uploadedFile'];
    $fileName = $_FILES['files']['name']['uploadedFile'];
    $fileSize = $_FILES['files']['size']['uploadedFile'];
    $fileType = $_FILES['files']['type']['uploadedFile'];

    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $uri = 'public://migration_attached_documents_es.' . $fileExtension;

    if (file_exists($uri)) unlink($uri);
    move_uploaded_file($file, $uri);

    $migrations = ['pc_commerce_attached_documents_es'];

    $process = new Process('drush migrate-reset-status pc_commerce_attached_documents_es');
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    foreach ($migrations as $mid) {
      $migration = \Drupal::service('plugin.manager.migration')->createInstance($mid);
      $executable = new MigrateExecutable($migration, new MigrateMessage());

      if ($executable->import()) {
          drupal_set_message('Migración:' . $mid . ' ejecutada');
      }
      else {
        drupal_set_message(t('Migration not executed, the type of the product is not defined'));
      }
    }
  }
}

function loadFileAttached($file) {
  $type = IOFactory::identify($file);
  $reader = IOFactory::createReader($type);
  $reader->setReadDataOnly(TRUE);
  $reader->setLoadSheetsOnly('datos');
  $workbook = $reader->load($file);
  $sheetData = $workbook->getActiveSheet();
  $rowIterator = $sheetData->getRowIterator();
  $row_id = 3;
  $novelties = array();
  $row = array();

  foreach($rowIterator as $row) {
    $ind = $row->getRowIndex();

    if ($ind < $row_id) {
      continue;
    }

    $cellIterator = $row->getCellIterator();

    foreach ($cellIterator as $cell) {
      $data[$ind][$cell->getColumn()] = $cell->getCalculatedValue();
    }

    $row = $data[$ind];

    if ($ind == $row_id) {
      $id_row = $row;
    } else {
      $novelties = validate_row_attached($id_row, $row, $novelties);
    }
  }


  return $novelties;
}

function validate_row_attached($id_row, $row, $novelties) {
  $fields_id = fields_id_attached();

  foreach ($id_row as $key => $id) {
    if (isset($fields_id[$id])) {
      $required = $fields_id[$id]['required'];
      switch ($fields_id[$id]['validate']) {
        case 'numeric':
          $novelties = validate_numeric_attached($key, $id_row, $row, $novelties, $required);
          break;
        case 'type':
          $type = $row[$key];
          break;
        case 'node':
        case 'taxonomy_term':
          $novelties = validate_field_attached($key, $fields_id[$id]['validate'], $fields_id[$id]['value_key'], $fields_id[$id]['bundle_key'], $fields_id[$id]['bundle'], $id_row, $row, $novelties, $required);
          break;
        case 'date':
          $novelties = validate_date_attached($key, $fields_id[$id]['format'], $id_row, $row, $novelties, $required);
          break;
        case 'required':
          $novelties = $row[$key] ? $novelties : novelty_message_attached($key, 'Campo requerido', $id_row, $row, $novelties);
          break;
        case 'static_map':
          $novelties = validate_static_map_attached($key, $fields_id[$id]['map'], $id_row, $row, $novelties, $required);
          break;
        case 'nas_path':
          $novelties = validateNasPathAttached($key, $id_row, $row, $novelties, $required);
          break;
        case 'document_status':
          $novelties = validateNasDocumentStatus($key, $id_row, $row, $novelties, $required);
          break;
      }
    }
  }

  return $novelties;
}

function validate_numeric_attached($col, $id_row, $row, $novelties, $required = FALSE) {
  if (!is_numeric($row[$col]) && $required) {
    $novelties = novelty_message_attached($col, 'Campo debe ser númerico', $id_row, $row, $novelties);
  }

  return $novelties;
}

function validate_field_attached($col, $entity_type, $value_key, $bundle_key, $bundle, $id_row, $row, $novelties, $required = FALSE) {
  if (!$required && $row[$col] == '') return $novelties;

  $items = explode(";", $row[$col]);

  foreach ($items as $item) {
    if ($entity_type === 'taxonomy_term') {
      $terms = \Drupal::entityQuery($entity_type)
        ->condition($value_key, $item)
        ->condition($bundle_key, $bundle)
        ->accessCheck(FALSE)
        ->execute();
      $term = reset($terms);

      if ($bundle == "authorized_for"){
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $item, 'vid' => "authorized_for"]); 
      }

      $count = empty($term) ? false : true;
    }
    else {
      $count = \Drupal::entityQuery($entity_type)
        ->condition($value_key, $item)
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    if (!$count) {
      $novelties = novelty_message_attached($col, 'No catalogado', $id_row, $row, $novelties, $item);
    }
  }

  return $novelties;
}

function validate_date_attached($col, $format, $id_row, $row, $novelties, $required = FALSE) {
  $date = \DateTime::createFromFormat($format, $row[$col]);
  if (!$date && $required) {
    $novelties = novelty_message_attached($col, 'Formato de fecha errada', $id_row, $row, $novelties);
  }

  return $novelties;
}

function validate_static_map_attached($col, $map, $id_row, $row, $novelties, $required = FALSE) {
  if (!$required && $row[$col] == '') return $novelties;

  $items = explode(";", $row[$col]);
  foreach ($items as $item) {
    in_array($item, $map) ? '' : $novelties = novelty_message_attached($col, 'No catalogado', $id_row, $row, $novelties, $item);
  }

  return $novelties;
}

function novelty_message_attached($col, $message, $id_row, $row, $novelties, $item = FALSE) {
  $novelties[] = [
    $col,
    t($id_row[$col]),
    t($message),
    $item ? $item : $row[$col]
  ];

  return $novelties;
}

function validateNasPathAttached($col, $id_row, $row, $novelties, $required){
  $value = $row[$col];

  if(!$value && $required){
    $novelties = novelty_message_attached($col, 'Ruta es obligatoria', $id_row, $row, $novelties);
  } else if ($value){
    $fileName = $row["G"];
    $nasDir = $value;

    $fullNasPath = $nasDir . $fileName;

    $fileUrl = file_create_url($fullNasPath);
    $destination = 'public://';
    $managed = TRUE;

    if (strpos($fileUrl, "https://") === 0){
      $fileUrl = str_replace("https://", "http://", $fileUrl);
    }
 
    if(!($file = system_retrieve_file($fileUrl, $destination, $managed))){
      $novelties = novelty_message_attached($col, 'Ruta no existe', $id_row, $row, $novelties);
    } else {
    }
  }

  return $novelties;
}

function validateNasDocumentStatus($col, $id_row, $row, $novelties, $required){
  $value = $row[$col];

  if(!$value && $required){
    $novelties = novelty_message_attached($col, 'Estatus de Documento es obligatoria', $id_row, $row, $novelties);
  } else if ($value){
    if ($value == "Entregado" || $value == "Pendiente"){
      //do nothing
    } else {
      $novelties = novelty_message_attached($col, 'Estado de documento debe ser Entregado o Pendiente', $id_row, $row, $novelties);
    }
  }

  return $novelties;
}

function fields_id_attached() {
  $fields_id = [
    'migrateRow' => [
      'validate' => 'numeric',
      'required' => TRUE
    ],
    'projectId' => [
      'validate' => 'node',
      'value_key' => 'nid',
      'bundle_key' => '',
      'bundle' => '',
      'required' => TRUE
    ],
    'campaignId' => [
      'validate' => 'node',
      'value_key' => 'nid',
      'bundle_key' => '',
      'bundle' => '',
      'required' => TRUE
    ],
    'documentType' => [
      'validate' => 'taxonomy_term',
      'value_key' => 'name',
      'bundle_key' => 'vid',
      'bundle' => 'attached_documents_types',
      'required' => TRUE
    ],
    'documentName' => [
      'validate' => 'required',
      'required' => TRUE
    ],
    'documentFile' => [
      'validate' => 'nas_path',
      'required' => TRUE
    ],
    'providerId' => [
      'validate' => 'node',
      'value_key' => 'field_id_number',
      'bundle_key' => 'type',
      'bundle' => 'provider',
      'required' => TRUE
    ],
    'startDate' => [
      'validate' => 'date',
      'format' => 'Ymd',
      'required' => TRUE
    ],
    'finishDate' => [
      'validate' => 'date',
      'format' => 'Ymd',
      'required' => TRUE
    ],
    'axis' => [
      'validate' => 'taxonomy_term',
      'value_key' => 'name',
      'bundle_key' => 'vid',
      'bundle' => 'axes',
      'required' => TRUE
    ],
    'broadcastMedia' => [
      'validate' => 'taxonomy_term',
      'value_key' => 'name',
      'bundle_key' => 'vid',
      'bundle' => 'broadcast_media',
      'required' => TRUE
    ],
    'documentStatus' => [
      'validate' => 'document_status',
      'required' => TRUE
    ],
    'authorizedUse' => [
      'validate' => 'taxonomy_term',
      'value_key' => 'name',
      'bundle_key' => 'vid',
      'bundle' => 'authorized_for',
      'required' => TRUE
    ],
  ];

  return $fields_id;
}
