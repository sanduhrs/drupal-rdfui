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
        //$mappings=rdf_get_mapping();
        $existingType='';


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
            //'#default_value' =>$existingType,
            '#description' => t('Specify the type you want to associated to this content type e.g. Article, Blog, etc.'),
        );

        return $form;
    }



    /**
     * Validate Schema.org mappings in \Drupal\node\NodeTypeForm
     */
    public function form_validate(array &$form, array &$form_state) {
        /*To be implemented*/
    }

    /**
     * Saves Schema.org mappings in \Drupal\node\NodeTypeForm
     */
    public function submitForm(array &$form, array &$form_state) {
        $error = FALSE;
        //validate

        $form_values = $form_state['rdf-mapping']['types'];
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

