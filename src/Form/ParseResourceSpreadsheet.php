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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\migrate_plus\Plugin\MigrationConfigEntityPluginManager;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Drush\Drush;

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
        if (!$this->purgeFileAttached($file)) {
          $form_state->setErrorByName('uploadedFile', $this->t('Error Cleaning up the Resources XLSX File.'));
        } else {
          $this->messenger()->addMessage('Uploaded file ' . $file . ' has been cleaned up for migration');
        }
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

    foreach ($migrations as $mid) {
      $migration = \Drupal::service('plugin.manager.migration')->createInstance($mid);
      $executable = new MigrateExecutable($migration, new MigrateMessage());

      $status = $migration->getStatus();

      if ($status == MigrationInterface::STATUS_IDLE) {
        $this->messenger()->addMessage('Migration:' . $mid . ' is already idle');
      }
      else {
        $migration->setStatus(MigrationInterface::STATUS_IDLE);
        $this->messenger()->addMessage('Set migration status of ' . $mid . ' to idle');
      }

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
  public function purgeFileAttached($file) {
    $workbook = IOFactory::load($file);

    $sheetData = $workbook->getActiveSheet();
    $sheetData->setTitle("Resources");
    $rowIterator = $sheetData->getRowIterator();
    $row_id = 2;
    $row = [];

    foreach ($rowIterator as $row) {
      $ind = $row->getRowIndex();
  
      if ($ind < $row_id) {
        continue;
      }

      // Apply label changes to the header row
      if ($ind == 2) {
        $cellIterator = $row->getCellIterator();
  
        // Change header labels to map the YML fields
        foreach ($cellIterator as $cell) {
          $data[$ind][$cell->getColumn()] = $cell->getCalculatedValue();
          
          switch ($cell->getColumn()) {
            case 'B':
              $cell->setValue('id');
              break;

            case 'E':
              $cell->setValue('created_date');
              break;

            case 'F':
              $cell->setValue('permalink');
              break;

            case 'H':
              $cell->setValue('field_thumbnail_image_url');
              break;

            case 'I':
              $cell->setValue('field_thumbnail_image_title');
              break;

            case 'J':
              $cell->setValue('field_thumbnail_image_alt_text_caption');
              break;

            case 'K':
              $cell->setValue('field_thumbnail_image_alt_text_description');
              break;

            case 'L':
              $cell->setValue('field_thumbnail_image_alt_text');
              break;

            case 'M':
              $cell->setValue('field_thumbnail_image_featured');
              break;

            case 'Y':
              $cell->setValue('field_media_file_upload');
              break;  
            
            default:
              # code...
              break;
          }
        }
      } else {
        // Apply misc changes to data
        $cellIterator = $row->getCellIterator();

        foreach ($cellIterator as $cell) {
          if ($cell->getColumn() == "E") {
            if ($cell->getValue()) {
              $value = $cell->getValue();
              $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);
              $cell->setValue($date);
            }
          } else if ($cell->getColumn() == "AC") {
            if ($cell->getValue()) {
              $value = $cell->getValue();
              file_put_contents("/tmp/keys", $value);
              $keywords = str_replace(",", "|", $value);
              $cell->setValue($keywords);
            }
          }
        }
      }
    }

    $writer = new Xlsx($workbook);
    $writer->save($file);

    return TRUE;
  }

}