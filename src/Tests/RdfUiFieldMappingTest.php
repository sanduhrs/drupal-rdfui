<?php

/**
 * @file
 * Contains \Drupal\rdfui\Tests\RdfUiFieldMappingTest.
 */

namespace Drupal\rdfui\Tests;

use Drupal\field_ui\Tests\FieldUiTestBase;

/**
 * Tests the functionality of the RdfUI Field Mapping form.
 */
class RdfUiFieldMappingTest extends FieldUiTestBase {

    /**
     * Modules to enable.
     */
    public static $modules = array('rdfui', 'rdf', 'field');

    /**
     * {@inheritdoc}
     */
    public static function getInfo() {
        return array(
            'name' => 'RdfUI Field Mapping',
            'description' => 'Tests the functionality of the FieldMapping Form.',
            'group' => 'RdfUI',
        );
    }

    /**
     * {@inheritdoc}
     */
    function setUp() {
        parent::setUp();
    }

    function testFormatterUI() {
        $manage_fields = 'admin/structure/types/manage/' . $this->type;
        $rdf_mappings=$manage_fields.'/fields-rdf';

        // Create a field, and a node with some data for the field.
        $initial_edit = array(
            'fields[_add_new_field][label]' => 'Test field',
            'fields[_add_new_field][field_name]' => 'test',
        );
        $this->fieldUIAddNewField($manage_fields, $initial_edit);

        // Display the "Manage fields RDF" screen and check that the expected fields are displayed
        $this->drupalGet($rdf_mappings);
        $label = $initial_edit['fields[_add_new_field][label]'];
        $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr/td[1]', $label, 'Field is displayed in manage field RDF page.');
        $this->assertFieldByName('fields[field_test][rdf-predicate]', '', 'Empty option displayed when field is unmapped.');
        $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr[@id="field-test"]/td[4]', 'Unmapped', 'Status displayed correctly when field is unmapped.');

        //Add rdf-predicate and save
        $mapped_value='schema:name';
        $edit = array('fields[field_test][rdf-predicate]' => $mapped_value);
        $this->drupalPostForm($rdf_mappings,  $edit, t('Save'));
        $this->assertRaw(t('Your settings have been saved.'), 'Manage Field RDF page reloaded.');
        $this->assertFieldByName('fields[field_test][rdf-predicate]', $mapped_value, 'Default option displayed when field is mapped.');
        $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr[@id="field-test"]/td[4]', 'Mapped', 'Status displayed correctly when field is mapped.');

        $mapping=rdf_get_mapping('node',$this->type);
        $this->assertEqual($mapping->getFieldMapping('field_test')['properties'][0],$mapped_value,"Selected Rdf mappings saved.");
    }
}