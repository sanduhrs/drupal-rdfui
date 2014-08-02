<?php
/**
 * Created by PhpStorm.
 * User: sachini
 * Date: 7/27/14
 * Time: 1:50 AM
 */

namespace Drupal\rdf_builder\Form;


use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\rdfui\EasyRdfConverter;

class ContentBuilderForm extends FormBase {
  /*@TODO Resolve naming conflicts and long field/content_type names
   * length-machine name-32
   * max. length name type-32, property-26
   */

  /**
   * @var /Drupal/rdfui/EasyRdfConverter
   */
  protected $converter;

  /**
   *  The field type manager.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $entity;

  /**
   * @var array
   */
  protected $properties;

  /**
   * @var \Drupal\rdf\Entity\RdfMapping
   */
  protected $rdf_mapping;

  /**
   * Constructs a new ContentBuilder.
   */
  public function __construct() {
    $this->converter = new EasyRdfConverter();
    $this->converter->createGraph();
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return "rdf_builder_content_builder_form";
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Display page 2 if $form_state['page_num'] == 2
    if (!empty($form_state['page_num']) && $form_state['page_num'] == 2) {
      return $this->buildForm_page_two($form, $form_state);
    }

    // Otherwise we build page 1.
    $form_state['page_num'] = 1;

    $form['#title'] = $this->t('Content types');
    $form['description'] = array(
      '#type' => 'item',
      '#title' => t('Create a content type by importing Schema.Org entity type.'),
    );

    $form['rdf-type'] = array(
      '#title' => t('Type'),
      '#id' => 'rdf-predicate',
      '#type' => 'select',
      '#required' => TRUE,
      '#options' => $this->converter->getListTypes(),
      '#empty_option' => '',
      '#default_value' => !empty($form_state['values']['rdf-type']) ? $form_state['values']['rdf-type'] : '',
      '#attached' => array(
        'library' => array(
          'rdfui/drupal.rdfui.autocomplete',
        ),
        'css' => array(
          drupal_get_path('module', 'rdfui') . '/css/rdfui.autocomplete.css',
        ),
      ),
      '#description' => t('Specify the type you want to associated to this content type e.g. Article, Blog, etc.'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['next'] = array(
      '#type' => 'submit',
      '#value' => 'Next >>',
      '#button_type' => 'primary',
      '#submit' => array(array($this, 'next_submit')),
      '#validate' => array(array($this, 'next_validate')),
    );
    return $form;
  }

  /**
   * Returns the form for the second page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  protected function buildForm_page_two(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Content types');
    $form['description'] = array(
      '#type' => 'item',
      '#title' => t('Choose fields to start with.'),
    );

    $rdf_type = $form_state['page_values'][1]['rdf-type'];
    $properties = $this->converter->getTypeProperties($rdf_type);
    //$widgets=\Drupal::service('plugin.manager.field.widget')->getDefinitions();
    $field_types = \Drupal::service('plugin.manager.field.field_type')
      ->getUiDefinitions();

    $field_type_options = array();
    foreach ($field_types as $name => $field_type) {
      // Skip field types which should not be added via user interface.
      if (empty($field_type['no_ui'])) {
        $field_type_options[$name] = $field_type['label'];
      }
    }
    asort($field_type_options);

    $table = array(
      '#type' => 'field_ui_table',
      '#tree' => TRUE,
      '#header' => array(
        $this->t('Enable'),
        $this->t('Property'),
        $this->t('Data Type'),
      ),
      '#regions' => array(),
      '#attributes' => array(
        'class' => array('rdfui-field-mappings'),
        'id' => 'rdf-builder',
      ),
    );

    foreach ($properties as $key => $value) {
      $table[$key] = array(
        '#attributes' => array(
          'id' => drupal_html_class($key),
        ),
        'enable' => array(
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#title_display' => 'invisible',
        ),
        'property' => array(
          '#markup' => String::checkPlain($value),
        ),
        'type' => array(
          '#type' => 'select',
          '#title' => $this->t('Data Type'),
          '#title_display' => 'invisible',
          '#options' => $field_type_options,
          '#empty_option' => $this->t('- Select a field type -'),
          '#attributes' => array('class' => array('field-type-select')),
          '#prefix' => '<div class="add-new-placeholder">&nbsp;</div>',
        ),
      );
    }
    // Fields.

    $table['#regions']['content']['rows_order'] = array();
    foreach (Element::children($table) as $name) {
      $table['#regions']['content']['rows_order'][] = $name;
    }

    $form['fields'] = $table;

    /*        $form['#title'] = $this->t('Content types');
            $form['description'] = array(
                '#type' => 'item',
                '#title' => t('Choose fields to start with'),
            );
    */
    /*$field_options=array();
    foreach($properties as $key=>$value){

        $field_options[$key]=array(
        //    '#parents' => array('frequent_options'),
            'name'=>array(
                '#markup' => $this->t($value),
            ),
            'enable'=>array(
                '#type'=>'checkbox',
                '#title'=>$this->t('Enable'),
                //'#title_display' => 'invisible',
            ),
            'type'=>array(
                '#type' => 'select',
                '#title' => $this->t($key),
                //'#title_display' => 'invisible',
                '#options' => $field_type_options,
                '#empty_option' => $this->t('- Select a field type -'),
                '#attributes' => array('class' => array('field-type-select')),
                '#prefix' => '<div class="add-new-placeholder">&nbsp;</div>',
            ),
        );
    }

    $form['fields']['#tree']=TRUE;
    $form['fields']['frequent_options'] = array(
        '#title' => t('More frequently used fields'),
        '#type' => 'details',
        '#open' => TRUE,
      //  '#attributes' => array('class' => array('package-listing')),
       // '#theme' => 'views_view_grid',
        //'#options'=>$field_options,
    );

    $form['fields']['frequent_options']+=$field_options;*/

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
    );

    $form['actions']['previous'] = array(
      '#type' => 'submit',
      '#value' => t('<< Back'),
      '#submit' => array(array($this, 'page_two_back_submit')),
      '#limit_validation_errors' => array(),
      '#validate' => array(array($this, 'page_two_back_validate')),
    );
    return $form;
  }

  /**
   * Validate handler for the next button on first page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function next_validate(array $form, FormStateInterface $form_state) {
    //validate optional
  }

  /**
   * Submit handler for Content Builder next button.
   * Capture the values from page one and store them away so they can be used
   * at final submit time.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function next_submit(array &$form, FormStateInterface &$form_state) {

    $form_state['page_values'][1] = $form_state['values'];

    if (!empty($form_state['page_values'][2])) {
      $form_state['values'] = $form_state['page_values'][2];
    }

    // When form rebuilds, it will look at this to figure which page to build.
    $form_state['page_num'] = 2;
    $form_state['rebuild'] = TRUE;
  }

  /**
   * Validate handler for the back button on second page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function page_two_back_validate(array $form, FormStateInterface $form_state) {
    //validation optional
  }

  /**
   * Back button handler submit handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function page_two_back_submit(array &$form, FormStateInterface &$form_state) {
    $form_state['values'] = $form_state['page_values'][1];
    $form_state['page_num'] = 1;
    $form_state['rebuild'] = TRUE;
  }

  /**
   * @inheritdoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state['values']['fields'] as $key => $property) {
      if ($property['enable'] === 1) {
        if (empty($property['type'])) {
          $this->setFormError('fields][$key][type', $form_state, $this->t('Create field: you need to provide a data type for %field.', array('%field' => explode(':', $key)[1])));
        }
      }
    }

  }

  /**
   * @inheritdoc
   *
   * This is the final submit handler. Gather all the data together and create new content type
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->properties = array();
    foreach ($form_state['values']['fields'] as $key => $property) {
      if ($property['enable'] === 1) {
        if (empty($property['type'])) {
          $this->setFormError('fields][$key][type', $form_state, $this->t('Create field: you need to provide a data type for %field.', array('%field' => explode(':', $key)[1])));
        }
        $this->properties[$key] = $property;
      }
    }

    $page_one_values = $form_state['page_values'][1];
    $rdf_type = $page_one_values['rdf-type'];
    //$this->entity=
    $this->createNodeType($rdf_type);

    $this->rdf_mapping = rdf_get_mapping('node', $this->entity->id());
    $this->rdf_mapping->setBundleMapping(array('types' => array($rdf_type)));

    $this->createField();
    $this->rdf_mapping->save();

    drupal_set_message($this->t('Content Type %label created', array('%label' => $this->entity->label())));
    /*@TODO Revert all saved content type and fields in case of error*/
    $form_state['redirect_route']['route_name'] = 'node.overview_types';
  }

  /**
   * Create new node_type
   *
   * @param string $rdf_type uri of the resource
   */
  protected function createNodeType($rdf_type) {
    $type = explode(':', $rdf_type)[1];
    $values = array(
      'name' => $this->converter->label($rdf_type),
      'type' => strtolower($type),
      'description' => $this->converter->description($rdf_type),
    );

    try {
      $this->entity = entity_create('node_type', $values);
      $this->entity->save();
    }
    catch (\Exception $e) {
      drupal_set_message('type', $this->t("Error saving content type %invalid.", array('%invalid' => $rdf_type)));
    }
  }

  /**
   * create fields for the selected properties
   */
  protected function createField() {
    $entity_type = 'node';
    $bundle = $this->entity->id();
    foreach ($this->properties as $key => $value) {
      $label = $this->converter->label($key);
      // Add the field prefix.
      $field_name = \Drupal::config('field_ui.settings')
          ->get('field_prefix') . strtolower($label);

      $field_storage = array(
        'name' => $field_name,
        'entity_type' => $entity_type,
        'type' => $value['type'],
        'translatable' => TRUE,
      );
      $instance = array(
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'label' => $label,
        // Field translatability should be explicitly enabled by the users.
        'translatable' => FALSE,
      );

      // Create the field and instance.
      try {
        $field = entity_create('field_storage_config', $field_storage)->save();
        $ins = entity_create('field_instance_config', $instance)->save();

        // Make sure the field is displayed in the 'default' form mode (using
        // default widget and settings). It stays hidden for other form modes
        // until it is explicitly configured.
        entity_get_form_display($entity_type, $bundle, 'default')
          ->setComponent($field_name)
          ->save();

        // Make sure the field is displayed in the 'default' view mode (using
        // default formatter and settings). It stays hidden for other view
        // modes until it is explicitly configured.
        entity_get_display($entity_type, $bundle, 'default')
          ->setComponent($field_name)
          ->save();

        //rdf-mapping
        $this->rdf_mapping->setFieldMapping($field_name, array(
            'properties' => array($key),
          )
        );
      }
      catch (\Exception $e) {
        $error = TRUE;
        drupal_set_message($this->t('There was a problem creating field %label: !message', array(
          '%label' => $instance['label'],
          '!message' => $e->getMessage()
        )), 'error');
      }
    }
  }
}
