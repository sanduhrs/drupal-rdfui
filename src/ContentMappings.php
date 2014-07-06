<?php

/**
 * @file
 * Contains \Drupal\rdfui\ContentMappings.
 */

namespace Drupal\rdfui;


/**
 * Rdf Ui Rdf Mapping for Content Types.
 */
class ContentMappings{

    /**
     * Form constructor for the Schema.org mappings in \Drupal\node\NodeTypeForm
     *
     * @see form_validate()
     * @see form_submit()

     * @ingroup forms
     */
    public static function alter_form($form, &$form_state){
        $typeOptions=new EasyRdfConverter();
        $entity_type=$form_state['controller']->getEntity();

        $existingType='';
        if(!$entity_type->isNew()){
            $existingType=rdf_get_mapping('node',$entity_type->id())->getBundleMapping()['types'][0];
        }

        $form['rdf-mapping'] = array(
            '#type' => 'details',
            '#title' => t('Schema.org Mappings'),
            '#group' => 'additional_settings',
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
        );

        $form['rdf-mapping']['types'] = array(
            '#type' => 'select',
            '#title' => t('Schema.org Type'),
            '#options' => $typeOptions->getListTypes(),
            '#empty_option' => t('- Select Predicate -'),
            '#default_value' =>$existingType,
            '#description' => t('Specify the type you want to associated to this content type e.g. Article, Blog, etc.'),
        );

        return $form;
    }



    /**
     * Validate Schema.org mappings in \Drupal\node\NodeTypeForm
     */
    public static function form_validate(array &$form, array &$form_state) {
        /*To be implemented*/
    }

    /**
     * Saves Schema.org mappings in \Drupal\node\NodeTypeForm
     */
    public static function submitForm(array &$form, array &$form_state) {
      if(isset($form_state['input']['types'])){
        $error = FALSE;

        //validate

        $entity_type=$form_state['controller']->getEntity();
        $mapping=rdf_get_mapping('node',$entity_type->id());
        /*if(!$entity_type->isNew()){
          $mappin=rdf_get_mapping('node',$entity_type->id())->getBundleMapping()['types'][0];
        }*/



        if(!empty($form_state['input']['types'])){
            $mapping->setBundleMapping(array('types' => array($form_state['input']['types'])))->save();
        }
      }
    }


}

