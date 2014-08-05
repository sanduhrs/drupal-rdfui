<?php

/**
 * @file
 * Contains Drupal/rdfui/Tests/RdfUiContentMappingTest
 */

namespace Drupal\rdfui\Tests;

use Drupal\field\Entity\FieldInstanceConfig;
use Drupal\node\Tests\NodeTestBase;

/**
 * Tests related to adding and editing rdf mappings for node types.
 *
 * @group RdfUI
 */
class RdfUiContentMappingTest extends NodeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('rdfui', 'rdf', 'field_ui');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'RdfUI Content Mapping',
      'description' => 'Ensures that Rdf mappings for Content type works correctly.',
      'group' => 'RdfUI',
    );
  }

  /**
   * Tests creating a content type mapping via UI.
   */
  protected function testNodeTypeCreation() {
    // Create a content type via the user interface.
    $web_user = $this->drupalCreateUser(array(
      'bypass node access',
      'administer content types',
    ));
    $this->drupalLogin($web_user);
    $edit = array(
      'name' => 'foo',
      'title_label' => 'title for foo',
      'type' => 'foo',
      'types' => 'schema:Person',
    );
    $this->drupalPostForm('admin/structure/types/add', $edit, t('Save and manage fields'));
    $type_exists = (bool) entity_load('node_type', 'foo');
    $this->assertTrue($type_exists, 'The new content type has been created in the database.');
    $mapping = rdf_get_mapping('node', 'foo');
    $type = $mapping->getBundleMapping()['types'][0];
    $this->assertEqual($type, $edit['types'], 'Content mapping saved correctly.');
  }

  /**
   * Tests editing a node type using the UI.
   */
  protected function testNodeTypeEditing() {
    $web_user = $this->drupalCreateUser(array(
      'bypass node access',
      'administer content types',
      'administer node fields',
    ));
    $this->drupalLogin($web_user);

    $edit_type = 'admin/structure/types/manage/page';
    // Verify that title and body fields are displayed.
    $this->drupalGet($edit_type);
    $this->assertRaw('Schema.org Mappings', 'Schema.Org tab found.');
    $this->assertRaw('Schema.org Type', 'Schema.Org tab content found.');

    // Change the rdf mapping.
    $edit = array(
      'types' => 'schema:Person',
    );
    $this->drupalPostForm($edit_type, $edit, t('Save content type'));
    $mapping = rdf_get_mapping('node', 'page');
    $type = $mapping->getBundleMapping()['types'][0];
    $this->assertEqual($type, $edit['types'], 'Content mapping saved correctly.');
  }
}
