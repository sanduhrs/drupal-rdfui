<?php

/**
 * @file
 * Contains \Drupal\rdfui\FieldRdfForm.
 */

namespace Drupal\rdfui;

use Drupal\Core\Entity\EntityForm;
use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Form controller for node type forms.
 */
class FieldRdfForm extends EntityForm {

    /**
     * {@inheritdoc}
     */
    public function form(array $form, array &$form_state) {
        $form = parent::form($form, $form_state);

        $type = $this->entity;

        $node_settings = $type->getModuleSettings('node');

        // Prepare node options to be used for 'checkboxes' form element.
        $keys = array_keys(array_filter($node_settings['options']));
        $node_settings['options'] = array_combine($keys, $keys);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    protected function actions(array $form, array &$form_state) {
        $actions = parent::actions($form, $form_state);
        $actions['submit']['#value'] = t('Save content type');
        $actions['delete']['#value'] = t('Delete content type');
        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $form, array &$form_state) {
        parent::validate($form, $form_state);

        $id = trim($form_state['values']['type']);
        // '0' is invalid, since elsewhere we check it using empty().
        if ($id == '0') {
            $this->setFormError('type', $form_state, $this->t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, array &$form_state) {
        $type = $this->entity;
        $type->type = trim($type->id());
        $type->name = trim($type->name);

        // title_label is required in core; has_title will always be TRUE, unless a
        // module alters the title field.
        $type->has_title = ($type->title_label != '');

        $status = $type->save();

        $t_args = array('%name' => $type->label());

        if ($status == SAVED_UPDATED) {
            drupal_set_message(t('The content type %name has been updated.', $t_args));
        }
        elseif ($status == SAVED_NEW) {
            drupal_set_message(t('The content type %name has been added.', $t_args));
            watchdog('node', 'Added content type %name.', $t_args, WATCHDOG_NOTICE, l(t('View'), 'admin/structure/types'));
        }

        $form_state['redirect_route']['route_name'] = 'node.overview_types';
    }

}
