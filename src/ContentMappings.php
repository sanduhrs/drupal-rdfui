<?php

/**
 * @file
 * Contains \Drupal\rdfui\ContentMappings.
 */

namespace Drupal\rdfui;

use Drupal\Core\Form\FormStateInterface;

/**
 * RDF UI Rdf Mapping for Content Types.
 */
class ContentMappings {

  /**
   * BuildForm method for the Schema.org mappings in \Drupal\node\NodeTypeForm.
   *
   * @see formValidate()
   * @see submitForm()
   */
  public static function alterForm(array &$form, FormStateInterface $form_state) {
    $type_options = new SchemaOrgConverter();
    $entity_type = $form_state['controller']->getEntity();

    $existing_type = '';
    if (!$entity_type->isNew()) {
      $existing_type = rdf_get_mapping('node', $entity_type->id())->getBundleMapping();
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
      '#options' => $type_options->getListTypes(),
      '#empty_option' => '',
      '#attached' => array(
        'library' => array(
          'rdfui/drupal.rdfui.autocomplete',
        ),
        'css' => array(
          drupal_get_path('module', 'rdfui') . '/css/rdfui.autocomplete.css',
        ),
      ),
      '#default_value' => !empty($existing_type) ? $existing_type['types'][0] : '',
      '#description' => t('Specify the type you want to associated to this content type e.g. Article, Blog, etc.'),
    );

    return $form;
  }


  /**
   * Validates Schema.org mappings in \Drupal\node\NodeTypeForm.
   */
  public static function formValidate(array &$form, FormStateInterface $form_state) {
    // @TODO implement method if validation is required.
  }

  /**
   * Saves Schema.org mappings in \Drupal\node\NodeTypeForm.
   */
  public static function submitForm(array &$form, FormStateInterface $form_state) {
    if (isset($form_state['values']['types'])) {
      $entity_type = $form_state['controller']->getEntity();
      $mapping = rdf_get_mapping('node', $entity_type->id());
      if ($entity_type->isNew()) {
        $mapping = rdf_get_mapping('node', $form_state['values']['type']);
      }

      if (!empty($form_state['values']['types'])) {
        $mapping->setBundleMapping(array('types' => array($form_state['values']['types'])))
          ->save();
      }
    }
  }
}
