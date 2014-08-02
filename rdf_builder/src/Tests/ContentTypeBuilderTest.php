<?php

/**
 * @file
 * Contains \Drupal\rdf_builder\Tests\ContentTypeBuilderTest.
 */

namespace Drupal\rdf_builder\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Content Type Builder.
 *
 * @group Rdf Builder
 */
class ContentTypeBuilderTest extends WebTestBase {

  /**
   * Modules to enable.
   */
  public static $modules = array(
    'rdf_builder',
    'rdfui',
    'rdf',
    'field',
    'node',
    'entity'
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Schema.Org driven Content Type Builder',
      'description' => 'Tests the functionality of the ContentBuilder Form.',
      'group' => 'RDF Builder',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create test user.
    $this->admin_user = $this->drupalCreateUser(array('administer content types'));
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Tests submission of Content Type Builder and creation of content type
   */
  protected function testContentTypeCreate() {
    $this->edit_form_one();

    foreach (array('email', 'name') as $element) {
      $this->assertText($element, format_string('property "@element" of "@type" was found.', array(
        '@element' => $element,
        '@type' => $this->rdf_type
      )));
    }

    $this->assertFieldByName('fields[schema:email][enable]', NULL, 'Checkbox for property found');
    $this->assertFieldByName('fields[schema:email][type]', NULL, 'Dropdown list for data type found.');

    $edit = array(
      'fields[schema:email][enable]' => '1',
      'fields[schema:email][type]' => 'email',
      'fields[schema:name][enable]' => '1',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText("Create field: you need to provide a data type for name", 'Form validated and errors displayed.');
    $this->assertUrl($this->uri, array(), 'Stayed on same page after incorrect submission.');

    $edit += array(
      'fields[schema:name][type]' => 'text',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertUrl('admin/structure/types', array(), 'Redirected to correct url upon correct submission.');
    $this->assertText('Content Type Person created', 'Successful content type creation message displayed');

    $type_exists = entity_load('node_type', 'person');
    $this->assertTrue(!empty($type_exists), "Content type created with correct machine name.");
    $rdf_mapping = rdf_get_mapping('node', 'person');
    $this->assertEqual($rdf_mapping->getBundleMapping()['types'][0], $this->rdf_type, "Bundle Mapping stored correctly.");

    debug($type_exists);

    $storage = (bool) entity_load('field_storage_config', 'node.name');
    $this->assertTrue($storage, "Field storage created");

    $instance = (bool) entity_load('field_instance_config', 'node.person.name');
    $this->assertTrue($instance, "Field instance created");
    debug($rdf_mapping);
    $this->assertEqual($rdf_mapping->getFieldMapping('name')['properties'][0], 'schema:name', "Field Mapping stored correctly.");
  }

  /**
   * Tests first form of Content Type Builder and its submission
   */
  protected function edit_form_one() {
    $this->uri = 'admin/structure/types/rdf';
    $this->drupalGet($this->uri);
    $this->assertRaw('Create a content type by importing Schema.Org entity type.', "Form one displayed correctly.");

    $this->rdf_type = "schema:Person";

    $edit = array(
      'rdf-type' => $this->rdf_type,
    );

    $this->drupalPostForm(NULL, $edit, t('Next >>'));
    $this->assertRaw('Choose fields to start with.', 'Navigated to page two of the form.');
  }

  /**
   * Tests back button of second form in Content Type Builder
   */
  protected function testNavigateBack() {
    $this->edit_form_one();
    $this->drupalPostForm(NULL, array(), t('<< Back'));
    $this->assertRaw("Create a content type by importing Schema.Org entity type.", "Navigated back to form one.");
    //test default option
  }

}
