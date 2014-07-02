<?php

/**
 * @file
 * Contains \Drupal\rdfui\Form\FieldMappings.
 */

namespace Drupal\rdfui\Form;

use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\field_ui\OverviewBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\FieldInstanceConfigInterface;
use Drupal\rdfui\EasyRdfConverter;

/**
 * Rdf Ui Rdf Mapping form.
 */
class FieldMappings extends OverviewBase {

  /**
   *  The field type manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

  /**
   *  The Easy Rdf Converter
   * @var \Drupal\rdfui\EasyRdfConverter
   */
  protected $rdfConverter;

  /**
   * Constructs a new FieldOverview.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type manager
   */
  public function __construct(EntityManagerInterface $entity_manager, FieldTypePluginManagerInterface $field_type_manager) {
    parent::__construct($entity_manager);
    $this->fieldTypeManager = $field_type_manager;
    $this->rdfConverter=new EasyRdfConverter();
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
  public function getRegions() {
    return array(
      'content' => array(
        'title' => $this->t('Content'),
        'invisible' => TRUE,
        // @todo Bring back this message in https://drupal.org/node/1963340.
        //'message' => $this->t('No fields are present yet.'),
      ),
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
  public function buildForm(array $form, array &$form_state, $entity_type_id = NULL, $bundle = NULL) {
    parent::buildForm($form, $form_state, $entity_type_id, $bundle);

    // Gather bundle information.
    $instances = array_filter(\Drupal::entityManager()->getFieldDefinitions($this->entity_type, $this->bundle), function ($field_definition) {
      return $field_definition instanceof FieldInstanceConfigInterface;
    });
    $field_types = $this->fieldTypeManager->getDefinitions();

    // Field prefix.
    $field_prefix = \Drupal::config('field_ui.settings')->get('field_prefix');

    $form += array(
      '#entity_type' => $this->entity_type,
      '#bundle' => $this->bundle,
      '#fields' => array_keys($instances),
    );

    $table = array(
      '#type' => 'field_ui_table',          //theme element used in field_ui_theme() hook
      '#tree' => TRUE,
      '#header' => array(
        $this->t('Label'),
        $this->t('Rdf Predicate'),
        $this->t('Data Type'),
        $this->t('Status'),
      ),
      '#regions' => $this->getRegions(),
      '#attributes' => array(
        'class' => array('rdfui-field-mappings'),
        'id' => 'rdf-mapping',
      ),
    );

    // Fields.
    foreach ($instances as $name => $instance) {
      $table[$name] = array(
        '#attributes' => array(
          'id' => drupal_html_class($name),
        ),
        'label' => array(
          '#markup' => check_plain($instance->getLabel()),
        ),
        'rdf-predicate' => array(
          '#type' => 'select',
          '#title_display' => 'invisible',
          '#options' => $this->rdfConverter->getListProperties(),
          '#default_value' => '--select predicate--',
        ),/*
        'type' => array(
          '#type' => 'link',
          '#title' => $field_types[$field->getType()]['label'],
          '#route_name' => 'field_ui.field_edit_' . $this->entity_type,
          '#route_parameters' => $route_parameters,
          '#options' => array('attributes' => array('title' => $this->t('Edit field settings.'))),
        ),*/
      );

      /*$table[$name]['operations']['data'] = array(
        '#type' => 'operations',
        '#links' => $this->entityManager->getListBuilder('field_instance_config')->getOperations($instance),
      );

      if (!empty($field->locked)) {
        $table[$name]['operations'] = array('#markup' => $this->t('Locked'));
        $table[$name]['#attributes']['class'][] = 'menu-disabled';
      }*/
    }

    // We can set the 'rows_order' element, needed by theme_field_ui_table(),
    // here instead of a #pre_render callback because this form doesn't have the
    // tabledrag behavior anymore.
    $table['#regions']['content']['rows_order'] = array();
    foreach (Element::children($table) as $name) {
      $table['#regions']['content']['rows_order'][] = $name;
    }

    $form['fields'] = $table;

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
      /*To be implemented*/
  }

  /**
   * Overrides \Drupal\field_ui\OverviewBase::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    drupal_set_message($this->t('Form submit method has not been implemented yet.'));
  }

}
