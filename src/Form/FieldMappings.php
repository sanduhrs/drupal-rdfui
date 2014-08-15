<?php

/**
 * @file
 * Contains \Drupal\rdfui\Form\FieldMappings.
 */

namespace Drupal\rdfui\Form;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\field\FieldInstanceConfigInterface;
use Drupal\field_ui\OverviewBase;
use Drupal\rdfui\EasyRdfConverter;
use Drupal\rdfui\SchemaOrgConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RDF UI Field Mapping form.
 */
class FieldMappings extends OverviewBase {

  /**
   * The field type manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

  /**
   * The EasyRdfConverter.
   *
   * @var \Drupal\rdfui\EasyRdfConverter
   */
  protected $rdfConverter;

  /**
   * Constructs a new FieldOverview.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, FieldTypePluginManagerInterface $field_type_manager) {
    parent::__construct($entity_manager);
    $this->fieldTypeManager = $field_type_manager;
    $this->rdfConverter = new SchemaOrgConverter();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.field.field_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rdfui_field_mappings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL, $bundle = NULL) {
    parent::buildForm($form, $form_state, $entity_type_id, $bundle);

    // Gather bundle information.
    $instances = array_filter(\Drupal::entityManager()
      ->getFieldDefinitions($this->entity_type, $this->bundle), function ($field_definition) {
        return $field_definition instanceof FieldInstanceConfigInterface;
      });

    $mappings = rdf_get_mapping($this->entity_type, $this->bundle);
    $options = NULL;
    $bundle = $mappings->getBundleMapping();

    if (!empty($bundle)) {
      $type = $bundle['types']['0'];
      $options = $this->rdfConverter->getTypeProperties($type);
    }
    else {
      $options = $this->rdfConverter->getListProperties();
    }

    $form += array(
      '#entity_type' => $this->entity_type,
      '#bundle' => $this->bundle,
      '#fields' => array_keys($instances),
    );

    $table = array(
      '#type' => 'field_ui_table',
      '#tree' => TRUE,
      '#header' => array(
        $this->t('Label'),
        $this->t('RDF Property'),
        $this->t('Data Type'),
        $this->t('Status'),
      ),
      '#regions' => $this->getRegions(),
      '#attributes' => array(
        'class' => array('rdfui-field-mappings'),
        'id' => drupal_html_id('rdf-mapping'),
      ),
    );

    // Fields.
    foreach ($instances as $name => $instance) {
      $property = $mappings->getFieldMapping($name);
      $table[$name] = array(
        '#attributes' => array(
          'id' => drupal_html_id($name),
        ),
        'label' => array(
          '#markup' => String::checkPlain($instance->getLabel()),
        ),
        'rdf-predicate' => array(
          '#id' => 'rdf-predicate',
          '#type' => 'select',
          '#title' => $this->t('RDF Property'),
          '#title_display' => 'invisible',
          '#options' => $options,
          '#empty_option' => '',
          '#attached' => array(
            'library' => array(
              'rdfui/drupal.rdfui.autocomplete',
            ),
            'css' => array(
              drupal_get_path('module', 'rdfui') . '/css/rdfui.autocomplete.css',
            ),
          ),
          '#default_value' => !empty($property) ? $property['properties'][0] : '',
        ),
        'type' => array(
          '#title' => $this->t('Data Type'),
          '#title_display' => 'invisible',
          '#markup' => $this->t('Text'),
        ),
        'status' => array(
          '#title' => $this->t('Status'),
          '#title_display' => 'invisible',
          '#markup' => !empty($property['properties'][0]) ? 'Mapped' : 'Unmapped',
        ),
      );
    }

    $table['#regions']['content']['rows_order'] = array();
    foreach (Element::children($table) as $name) {
      $table['#regions']['content']['rows_order'][] = $name;
    }

    $form['fields'] = $table;

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    return array(
      'content' => array(
        'title' => $this->t('Content'),
        'invisible' => TRUE,
        // @todo Bring back this message in https://drupal.org/node/1963340.
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @TODO implement method if validation is required.
  }

  /**
   * Overrides \Drupal\field_ui\OverviewBase::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $form_values = $form_state['values']['fields'];
    $mapping = rdf_get_mapping($this->entity_type, $this->bundle);

    // Add mapping for title field.
    if ($this->entity_type === 'node') {
      $type = $mapping->getFieldMapping('title');
      if (empty($type)) {
        $mapping->setFieldMapping('title', array(
            'properties' => array('schema:name'),
          )
        );
      }
    }

    foreach ($form_values as $key => $value) {
      $mapping->setFieldMapping($key, array(
          'properties' => array($value['rdf-predicate']),
        )
      );
    }
    $mapping->save();

    drupal_set_message($this->t('Your settings have been saved.'));
  }

}
