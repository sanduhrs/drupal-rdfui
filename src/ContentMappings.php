<?php

/**
 * @file
 * Contains \Drupal\rdfui\ContentMappings.
 */

namespace Drupal\rdfui;

use Drupal\Core\Form\FormStateInterface;


/**
 * Rdf Ui Rdf Mapping for Content Types.
 */
class ContentMappings {
  /*@TODO Fix errors for adding content type mapping*/

  /**
   * BuildForm method for the Schema.org mappings in \Drupal\node\NodeTypeForm
   *
   * @see form_validate()
   * @see submitform()
   */
  public static function alter_form(array &$form, FormStateInterface $form_state) {
    $typeOptions = new EasyRdfConverter();
    $typeOptions->createGraph();
    $entity_type = $form_state['controller']->getEntity();

    $existingType = '';
    if (!$entity_type->isNew()) {
      $existingType = rdf_get_mapping('node', $entity_type->id())->getBundleMapping();
    }

    $form['rdf-mapping'] = array(
      '#type' => 'details',
      '#title' => t('Schema.org Mappings'),
      '#group' => 'additional_settings',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['rdf-mapping']['types'] = array(
      '#id' => 'rdf-predicate',
      '#type' => 'select',
      '#title' => t('Schema.org Type'),
      '#options' => $typeOptions->getListTypes(),
      '#empty_option' => '',
      '#attached' => array(
        'library' => array(
          'rdfui/drupal.rdfui.autocomplete',
        ),
        'css' => array(
          drupal_get_path('module', 'rdfui') . '/css/rdfui.autocomplete.css',
        ),
      ),
      '#default_value' => !empty($existingType) ? $existingType['types'][0] : '',
      '#description' => t('Specify the type you want to associated to this content type e.g. Article, Blog, etc.'),
    );

    return $form;
  }


  /**
   * Validate Schema.org mappings in \Drupal\node\NodeTypeForm
   */
  public static function form_validate(array &$form, FormStateInterface $form_state) {
    /*To be implemented*/
  }

  /**
   * Saves Schema.org mappings in \Drupal\node\NodeTypeForm
   */
  public static function submitForm(array &$form, FormStateInterface $form_state) {
    if (isset($form_state['input']['types'])) {
      $error = FALSE;

      $entity_type = $form_state['controller']->getEntity();
      $mapping = rdf_get_mapping('node', $entity_type->id());
      if ($entity_type->isNew()) {
        $mapping = rdf_get_mapping('node', $form_state['input']['type']);
      }

      if (!empty($form_state['input']['types'])) {
        $mapping->setBundleMapping(array('types' => array($form_state['input']['types'])))
          ->save();
      }
    }
  }


}

