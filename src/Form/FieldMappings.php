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

    $mappings=rdf_get_mapping($this->entity_type,$this->bundle);

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
      $property=$mappings->getFieldMapping($name)['properties'][0];
      $table[$name] = array(
        '#attributes' => array(
          'id' => drupal_html_class($name),
        ),
        'label' => array(
          '#markup' => check_plain($instance->getLabel()),
        ),
        'rdf-predicate' => array(
          '#type' => 'select',
          '#title' => $this->t('Rdf Property'),
          '#title_display' => 'invisible',
          '#empty_option' => $this->t('- Select Predicate -'),
          '#default_value' =>$property,
          '#options' => $this->rdfConverter->getListProperties(),
        ),
        'type' => array(
         // '#type' => 'link',
          '#title' => 'Data Type',
          '#title_display' => 'invisible',
          /*'#route_name' => 'field_ui.field_edit_' . $this->entity_type,
          '#route_parameters' => $route_parameters,*/
          '#markup'=>$this->t('Text'),
        ),
        'status'=>array(
            '#title' => 'Status',
            '#title_display' => 'invisible',
            '#markup'=>$property?'Mapped':'Unmapped',
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
    $error = FALSE;
    //validate

    $form_values = $form_state['values']['fields'];
    $x=array();
    $mapping = rdf_get_mapping($this->entity_type, $this->bundle);

    foreach($form_values as $key=>$value){
        if(!empty($value['rdf-predicate'])){
            $mapping->setFieldMapping($key, array(
                'properties' => array($value['rdf-predicate']),
                )
            );
        }
        $x[$key]=$value['rdf-predicate'];
    }
    $mapping->save();

    drupal_set_message($this->t('Your settings have been saved.'));
  }

}
