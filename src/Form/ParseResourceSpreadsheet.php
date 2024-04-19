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

    /* foreach ($migrations as $mid) {
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
    } */
  }

  /**
   * {@inheritdoc}
   */
  public function purgeFileAttached($file) {
    $workbook = IOFactory::load($file);

    $sheetData = $workbook->getActiveSheet();
    $sheetData->setTitle("Resources");
    /* Format Date column to String */
    $sheetData->getStyle('F')->getNumberFormat()->setFormatCode('@');
    $rowIterator = $sheetData->getRowIterator();
    $row_id = 1;
    $row = [];

    foreach ($rowIterator as $row) {
      $ind = $row->getRowIndex();
  
      if ($ind < $row_id) {
        continue;
      }

      // Apply label changes to the header row
      if ($ind == 1) {
        $cellIterator = $row->getCellIterator();
  
        // Change header labels to map the YML fields
        foreach ($cellIterator as $cell) {
          $data[$ind][$cell->getColumn()] = $cell->getCalculatedValue();
          
          switch ($cell->getColumn()) {
            case 'A':
              $cell->setValue('id');
              break;
            case 'D':
              $cell->setValue('username');
              break;
            case 'F':
              $cell->setValue('created_date');
              break;
            case 'G':
              $cell->setValue('permalink');
              break;
            case 'J':
              $cell->setValue('field_thumbnail_image_url');
              break;
            case 'K':
              $cell->setValue('field_thumbnail_image_title');
              break;
            case 'L':
              $cell->setValue('field_thumbnail_image_alt_text_caption');
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
          /* Parse creation date field */
          if ($cell->getColumn() == "F") {
            if ($cell->getValue()) {
              $value = $cell->getValue();
              $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);
              $cell->setValue($date);
            }

          /* Parse tags column */
          } else if ($cell->getColumn() == "AA") {
            if ($cell->getValue()) {
              $value = $cell->getValue();
              $keywords = str_replace(",,", ",", $keywords);
              $keywords = str_replace(", ,", ",", $keywords);
              $keywords = str_replace(", ", "|", $value);
              $keywords = str_replace(",", "|", $keywords);
              $keywords = str_replace(" ,", "|", $keywords);

              $keywords = str_replace("; ", "|", $keywords);
              $keywords = str_replace(";", "|", $keywords);
              $keywords = str_replace(" ;", "|", $keywords);

              //TO FIX
              if (str_contains($keywords, "Maxime Laporte") || str_contains($keywords, "Consolidation des apprentissages")){
                $keywords = str_replace("\n", "|", $keywords);
                $keywords = str_replace("| |", "|", $keywords);
              }

              $cell->setValue($keywords);
            }
          
          /* Trucate publisher cell if longer */
          } else if ($cell->getColumn() == "Z") {
            if ($cell->getValue()) {
              $value = $cell->getValue();
              $publisher = substr($value, 0, 254);
              $cell->setValue($publisher);
            }

          /* Parse authors column */
          } else if ($cell->getColumn() == "Y") {
            if ($cell->getValue()) {
              $value = $cell->getValue();
              $authors = str_replace(", ", ",", $authors);
              $authors = str_replace(" ,", ",", $authors);

              $cell->setValue($authors);
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