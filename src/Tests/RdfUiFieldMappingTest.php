<?php

/**
 * @file
 * Contains \Drupal\rdfui\Tests\RdfUiFieldMappingTest.
 */

namespace Drupal\rdfui\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the functionality of the Field UI route subscriber.
 */
class RdfUiFieldMappingTest extends WebTestBase {

    /**
     * The machine name of the field type to test.
     *
     * @var string
     */
    protected $fieldType='text';

    /**
     * The name of the field to create for testing.
     *
     * @var string
     */
    protected $fieldName = 'field_test';

    /**
     * Modules to enable.
     */
    public static $modules = array('rdfui', 'node', 'rdf', 'field');

    /**
     * {@inheritdoc}
     */
    public static function getInfo() {
        return array(
            'name' => 'RdfUi Field Mapping',
            'description' => 'Tests the functionality of the FieldMapping Form.',
            'group' => 'RdfUI',
        );
    }

    /**
     * {@inheritdoc}
     */
    function setUp() {
        parent::setUp();
        $this->createTestField();
    }

    function testMappingCreation() {
        $valid = is_valid('foo');
        $this->assertTrue($valid, 'foo is a valid variable.');

        /*$mapping_config_name = "{$this->prefix}.{$this->entity_type}.{$this->bundle}";

        // Save bundle mapping config.
        rdf_get_mapping($this->entity_type, $this->bundle)->save();
        // Test that config file was saved.
        $mapping_config = \Drupal::configFactory()->listAll('rdf.mapping.');
        $this->assertTrue(in_array($mapping_config_name, $mapping_config), 'Rdf mapping config saved.');*/
    }

    /**
     * Creates the field for testing.
     */
    protected function createTestField() {
        entity_create('field_config', array(
            'name' => $this->fieldName,
            'entity_type' => 'entity_test',
            'type' => $this->fieldType,
        ))->save();
        entity_create('field_instance_config', array(
            'entity_type' => 'entity_test',
            'field_name' => $this->fieldName,
            'bundle' => 'entity_test',
        ))->save();
    }

}
   /* public function testWidgetUI() {
        $manage_fields = 'admin/structure/types/manage/' . $this->type;
        $rdf_mappings = $manage_fields . '/fields-rdf';

        // Create a field, and a node with some data for the field.
        $edit = array(
            'fields[_add_new_field][label]' => 'Test field',
            'fields[_add_new_field][field_name]' => 'test',
        );
        $this->fieldUIAddNewField($manage_fields, $edit);

        // Clear the test-side cache and get the saved field instance.
        $display = entity_get_form_display('node', $this->type, 'default');
        $display_options = $display->getComponent('field_test');
        $widget_type = $display_options['type'];
        $default_settings = \Drupal::service('plugin.manager.field.widget')->getDefaultSettings($widget_type);
        $setting_name = key($default_settings);
        $setting_value = $display_options['settings'][$setting_name];
    }


        /**
     * Tests the manage fields page.
     *
     * @param string $type
     *   (optional) The name of a content type.
     */
    /*function manageFieldsPage($type = '') {
        $type = empty($type) ? $this->type : $type;
        $this->drupalGet('admin/structure/types/manage/' . $type . '/fields-rdf');
        // Check all table columns.
        $table_headers = array(
            t('Label'),
            t('Rdf Predicate'),
            t('Data Type'),
            t('Status'),
        );
        foreach ($table_headers as $table_header) {
            // We check that the label appear in the table headings.
            $this->assertRaw($table_header . '</th>', format_string('%table_header table header was found.', array('%table_header' => $table_header)));
        }

        // "Add new field" and "Re-use existing field" aren't a table heading so just
        // test the text.
        foreach (array('Add new field', 'Re-use existing field') as $element) {
            $this->assertText($element, format_string('"@element" was found.', array('@element' => $element)));
        }

        // Assert entity operations for all field instances.
        $result = $this->xpath('//ul[@class = "dropbutton"]/li/a');
        $url = base_path() . "admin/structure/types/manage/$type/fields/node.$type.body";
        $this->assertIdentical($url, (string) $result[0]['href']);
        $this->assertIdentical("$url/field", (string) $result[1]['href']);
        $this->assertIdentical("$url/delete", (string) $result[3]['href']);
    }

    /**
     * Tests adding a new field.
     *
     * @todo Assert properties can bet set in the form and read back in $field and
     * $instances.
     */
   /* function createField() {
        // Create a test field.
        $edit = array(
            'fields[_add_new_field][label]' => $this->field_label,
            'fields[_add_new_field][field_name]' => $this->field_name_input,
        );
        $this->fieldUIAddNewField('admin/structure/types/manage/' . $this->type, $edit);
    }

    /**
     * Tests editing an existing field.
     */
    /*function updateField() {
        $instance_id = 'node.' . $this->type . '.' . $this->field_name;
        // Go to the field edit page.
        $this->drupalGet('admin/structure/types/manage/' . $this->type . '/fields/' . $instance_id . '/field');

        // Populate the field settings with new settings.
        $string = 'updated dummy test string';
        $edit = array(
            'field[settings][test_field_setting]' => $string,
        );
        $this->drupalPostForm(NULL, $edit, t('Save field settings'));

        // Go to the field instance edit page.
        $this->drupalGet('admin/structure/types/manage/' . $this->type . '/fields/' . $instance_id);
        $edit = array(
            'instance[settings][test_instance_setting]' => $string,
        );
        $this->drupalPostForm(NULL, $edit, t('Save settings'));

        // Assert the field settings are correct.
        $this->assertFieldSettings($this->type, $this->field_name, $string);

        // Assert redirection back to the "manage fields" page.
        $this->assertUrl('admin/structure/types/manage/' . $this->type . '/fields');
    }

    /**
     * Tests adding an existing field in another content type.
     */
    /*function addExistingField() {
        // Check "Re-use existing field" appears.
        $this->drupalGet('admin/structure/types/manage/page/fields');
        $this->assertRaw(t('Re-use existing field'), '"Re-use existing field" was found.');

        // Check that fields of other entity types (here, the 'comment_body' field)
        // do not show up in the "Re-use existing field" list.
        $this->assertFalse($this->xpath('//select[@id="edit-add-existing-field-field-name"]//option[@value="comment"]'), 'The list of options respects entity type restrictions.');

        // Add a new field based on an existing field.
        $edit = array(
            'fields[_add_existing_field][label]' => $this->field_label . '_2',
            'fields[_add_existing_field][field_name]' => $this->field_name,
        );
        $this->fieldUIAddExistingField("admin/structure/types/manage/page", $edit);
    }

    /**
     * Tests the cardinality settings of a field.
     *
     * We do not test if the number can be submitted with anything else than a
     * numeric value. That is tested already in FormTest::testNumber().
     */
    /*function cardinalitySettings() {
        $field_edit_path = 'admin/structure/types/manage/article/fields/node.article.body/field';

        // Assert the cardinality other field cannot be empty when cardinality is
        // set to 'number'.
        $edit = array(
            'field[cardinality]' => 'number',
            'field[cardinality_number]' => '',
        );
        $this->drupalPostForm($field_edit_path, $edit, t('Save field settings'));
        $this->assertText('Number of values is required.');

        // Submit a custom number.
        $edit = array(
            'field[cardinality]' => 'number',
            'field[cardinality_number]' => 6,
        );
        $this->drupalPostForm($field_edit_path, $edit, t('Save field settings'));
        $this->assertText('Updated field Body field settings.');
        $this->drupalGet($field_edit_path);
        $this->assertFieldByXPath("//select[@name='field[cardinality]']", 'number');
        $this->assertFieldByXPath("//input[@name='field[cardinality_number]']", 6);

        // Set to unlimited.
        $edit = array(
            'field[cardinality]' => FieldDefinitionInterface::CARDINALITY_UNLIMITED,
        );
        $this->drupalPostForm($field_edit_path, $edit, t('Save field settings'));
        $this->assertText('Updated field Body field settings.');
        $this->drupalGet($field_edit_path);
        $this->assertFieldByXPath("//select[@name='field[cardinality]']", FieldDefinitionInterface::CARDINALITY_UNLIMITED);
        $this->assertFieldByXPath("//input[@name='field[cardinality_number]']", 1);
    }

    /**
     * Tests deleting a field from the instance edit form.
     */
   /* protected function deleteFieldInstance() {
        // Delete the field instance.
        $instance_id = 'node.' . $this->type . '.' . $this->field_name;
        $this->drupalGet('admin/structure/types/manage/' . $this->type . '/fields/' . $instance_id);
        $this->drupalPostForm(NULL, array(), t('Delete field'));
        $this->assertResponse(200);
    }

    /**
     * Asserts field settings are as expected.
     *
     * @param $bundle
     *   The bundle name for the instance.
     * @param $field_name
     *   The field name for the instance.
     * @param $string
     *   The settings text.
     * @param $entity_type
     *   The entity type for the instance.
     */
    /*function assertFieldSettings($bundle, $field_name, $string = 'dummy test string', $entity_type = 'node') {
        // Assert field settings.
        $field = FieldConfig::loadByName($entity_type, $field_name);
        $this->assertTrue($field->getSetting('test_field_setting') == $string, 'Field settings were found.');

        // Assert instance settings.
        $instance = FieldInstanceConfig::loadByName($entity_type, $bundle, $field_name);
        $this->assertTrue($instance->getSetting('test_instance_setting') == $string, 'Field instance settings were found.');
    }

    /**
     * Tests that the 'field_prefix' setting works on Field UI.
     */
    /*function testFieldPrefix() {
        // Change default field prefix.
        $field_prefix = strtolower($this->randomName(10));
        \Drupal::config('field_ui.settings')->set('field_prefix', $field_prefix)->save();

        // Create a field input and label exceeding the new maxlength, which is 22.
        $field_exceed_max_length_label = $this->randomString(23);
        $field_exceed_max_length_input = $this->randomName(23);

        // Try to create the field.
        $edit = array(
            'fields[_add_new_field][label]' => $field_exceed_max_length_label,
            'fields[_add_new_field][field_name]' => $field_exceed_max_length_input,
        );
        $this->drupalPostForm('admin/structure/types/manage/' . $this->type . '/fields', $edit, t('Save'));
        $this->assertText('New field name cannot be longer than 22 characters but is currently 23 characters long.');

        // Create a valid field.
        $edit = array(
            'fields[_add_new_field][label]' => $this->field_label,
            'fields[_add_new_field][field_name]' => $this->field_name_input,
        );
        $this->fieldUIAddNewField('admin/structure/types/manage/' . $this->type, $edit);
        $this->drupalGet('admin/structure/types/manage/' . $this->type . '/fields/node.' . $this->type . '.' . $field_prefix . $this->field_name_input);
        $this->assertText(format_string('@label settings for @type', array('@label' => $this->field_label, '@type' => $this->type)));
    }

    /**
     * Tests that default value is correctly validated and saved.
     */
    /*function testDefaultValue() {
        // Create a test field and instance.
        $field_name = 'test';
        entity_create('field_config', array(
            'name' => $field_name,
            'entity_type' => 'node',
            'type' => 'test_field'
        ))->save();
        $instance = entity_create('field_instance_config', array(
            'field_name' => $field_name,
            'entity_type' => 'node',
            'bundle' => $this->type,
        ));
        $instance->save();

        entity_get_form_display('node', $this->type, 'default')
            ->setComponent($field_name)
            ->save();

        $admin_path = 'admin/structure/types/manage/' . $this->type . '/fields/' . $instance->id();
        $element_id = "edit-default-value-input-$field_name-0-value";
        $element_name = "default_value_input[{$field_name}][0][value]";
        $this->drupalGet($admin_path);
        $this->assertFieldById($element_id, '', 'The default value widget was empty.');

        // Check that invalid default values are rejected.
        $edit = array($element_name => '-1');
        $this->drupalPostForm($admin_path, $edit, t('Save settings'));
        $this->assertText("$field_name does not accept the value -1", 'Form vaildation failed.');

        // Check that the default value is saved.
        $edit = array($element_name => '1');
        $this->drupalPostForm($admin_path, $edit, t('Save settings'));
        $this->assertText("Saved $field_name configuration", 'The form was successfully submitted.');
        $instance = FieldInstanceConfig::loadByName('node', $this->type, $field_name);
        $this->assertEqual($instance->default_value, array(array('value' => 1)), 'The default value was correctly saved.');

        // Check that the default value shows up in the form
        $this->drupalGet($admin_path);
        $this->assertFieldById($element_id, '1', 'The default value widget was displayed with the correct value.');

        // Check that the default value can be emptied.
        $edit = array($element_name => '');
        $this->drupalPostForm(NULL, $edit, t('Save settings'));
        $this->assertText("Saved $field_name configuration", 'The form was successfully submitted.');
        $instance = FieldInstanceConfig::loadByName('node', $this->type, $field_name);
        $this->assertEqual($instance->default_value, NULL, 'The default value was correctly saved.');

        // Check that the default widget is used when the field is hidden.
        entity_get_form_display($instance->entity_type, $instance->bundle, 'default')
            ->removeComponent($field_name)->save();
        $this->drupalGet($admin_path);
        $this->assertFieldById($element_id, '', 'The default value widget was displayed when field is hidden.');
    }

}
*/