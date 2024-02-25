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
 * Class ParseResourceSpreadsheet
 */
class ParseResourceSpreadsheet extends FormBase {

  /**
   * URI of the XLSX file
   *
   * @var string
   */
  protected $resourceFileUri = "public://2024-02/wordpress-resources-client.xlsx";

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'parse_resources_spreadsheet';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => $this->t('Upload here the WordPress Resources XLSX file to be parsed and modified for the Resources Migration Process'),
      '#weight' => 2
    ];

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

    $form['#attributes'] = ['enctype' => 'multipart/form-data'];

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
    $extensions = ['xlsx'];

    if (!in_array($fileExtension, $extensions)){
      $form_state->setErrorByName('uploadedFile', $this->t('Invalid file format. Only xlsx is allowed'));
    }
    else {
      $file = $file = $_FILES['files']['tmp_name']['uploadedFile'];

      if ($file) {
        //$novelties = loadFileAttached($file);

      }
      else {
        $form_state->setErrorByName('uploadedFile', $this->t('Error loading the Resources XLSX File.'));
      }
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $_FILES['files']['tmp_name']['uploadedFile'];
    $fileName = $_FILES['files']['name']['uploadedFile'];
    $fileSize = $_FILES['files']['size']['uploadedFile'];
    $fileType = $_FILES['files']['type']['uploadedFile'];

    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    if (file_exists($this->resourceFileUri)) unlink($this->resourceFileUri);
    move_uploaded_file($file, $this->resourceFileUri);

    $migrations = ['h5p_migrate_wordpress_resources_docclient'];

    $process = new Process(['drush', 'migrate-reset-status', 'h5p_migrate_wordpress_resources_docclient']);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    foreach ($migrations as $mid) {
      $migration = \Drupal::service('plugin.manager.migration')->createInstance($mid);
      $executable = new MigrateExecutable($migration, new MigrateMessage());

      if ($executable->import()) {
        $this->messenger()->addMessage('Migration:' . $mid . ' executed');
      }
      else {
        $this->messenger()->addError('Migration not executed, the type of the product is not defined');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadFileAttached(File $file) {
    $type = IOFactory::identify($file);
    $reader = IOFactory::createReader($type);
    $reader->setReadDataOnly(FALSE);
    $reader->setLoadSheetsOnly('datos');
    $workbook = $reader->load($file);
    $sheetData = $workbook->getActiveSheet();
    $rowIterator = $sheetData->getRowIterator();
    $row_id = 3;
    $row = [];
  
  }

}