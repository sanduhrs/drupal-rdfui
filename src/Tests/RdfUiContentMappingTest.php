<?php

/**
 * @file
 * Definition of Drupal/rdfui/Tests/RdfUiContentMappingTest
 */

namespace Drupal\rdfui\Tests;

use Drupal\field\Entity\FieldInstanceConfig;
use Drupal\node\Tests\NodeTestBase;

/**
 * Tests related to node types.
 */
class RdfUiContentMappingTest extends NodeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('rdfui','rdf','field_ui');

  public static function getInfo() {
    return array(
      'name' => 'RdfUI Content Mapping',
      'description' => 'Ensures that Rdf mappings for Content type works correctly.',
      'group' => 'RdfUI',
    );
  }


  /**
   * Tests creating a content type programmatically and via a form.
   */
  function testNodeTypeCreation() {
    // Create a content type via the user interface.
    $web_user = $this->drupalCreateUser(array('bypass node access', 'administer content types'));
    $this->drupalLogin($web_user);
    $edit = array(
      'name' => 'foo',
      'title_label' => 'title for foo',
      'type' => 'foo',
      'types'=>'schema:Person',
    );
    $this->drupalPostForm('admin/structure/types/add', $edit, t('Save and manage fields'));
    $type_exists = (bool) entity_load('node_type', 'foo');
    $this->assertTrue($type_exists, 'The new content type has been created in the database.');
  }

  /**
   * Tests editing a node type using the UI.
   */
  function testNodeTypeEditing() {
    $web_user = $this->drupalCreateUser(array('bypass node access', 'administer content types', 'administer node fields'));
    $this->drupalLogin($web_user);

    $instance = FieldInstanceConfig::loadByName('node', 'page', 'body');
    $edit_type='admin/structure/types/manage/page';
      // Verify that title and body fields are displayed.
    $this->drupalGet($edit_type);
    $this->assertRaw('Schema.org Mappings', 'Schema.Org tab found.');
    $this->assertRaw('Schema.org Type', 'Schema.Org tab content found.');
    $this->assertFieldByName('types','','Unmapped content type displayed correctly.');


  // Change the rdf mapping.
    $edit = array(
      'types' => 'schema:Person',
    );
    $this->drupalPostForm($edit_type, $edit, t('Save content type'));
    $mapping=rdf_get_mapping('node','page');
    $type=$mapping->getBundleMapping()['types'][0];
    $this->assertEqual($type,$edit['types'],'Content mapping saved correctly.');
/*
    $this->drupalGet('node/add');
    $this->assertRaw('Bar', 'New name was displayed.');
    $this->assertRaw('Lorem ipsum', 'New description was displayed.');
    $this->clickLink('Bar');
    $this->assertEqual(url('node/add/bar', array('absolute' => TRUE)), $this->getUrl(), 'New machine name was used in URL.');
    $this->assertRaw('Foo', 'Title field was found.');
    $this->assertRaw('Body', 'Body field was found.');

    // Remove the body field.
    $this->drupalPostForm('admin/structure/types/manage/bar/fields/node.bar.body/delete', array(), t('Delete'));
    // Resave the settings for this type.
    $this->drupalPostForm('admin/structure/types/manage/bar', array(), t('Save content type'));
    // Check that the body field doesn't exist.
    $this->drupalGet('node/add/bar');
    $this->assertNoRaw('Body', 'Body field was not found.');*/
  }

  /**
   * Tests deleting a content type that still has content.
   */
/*  function testNodeTypeDeletion() {
    // Create a content type programmatically.
    $type = $this->drupalCreateContentType();

    // Log in a test user.
    $web_user = $this->drupalCreateUser(array(
      'bypass node access',
      'administer content types',
    ));
    $this->drupalLogin($web_user);

    // Add a new node of this type.
    $node = $this->drupalCreateNode(array('type' => $type->type));
    // Attempt to delete the content type, which should not be allowed.
    $this->drupalGet('admin/structure/types/manage/' . $type->name . '/delete');
    $this->assertRaw(
      t('%type is used by 1 piece of content on your site. You can not remove this content type until you have removed all of the %type content.', array('%type' => $type->name)),
      'The content type will not be deleted until all nodes of that type are removed.'
    );
    $this->assertNoText(t('This action cannot be undone.'), 'The node type deletion confirmation form is not available.');

    // Delete the node.
    $node->delete();
    // Attempt to delete the content type, which should now be allowed.
    $this->drupalGet('admin/structure/types/manage/' . $type->name . '/delete');
    $this->assertRaw(
      t('Are you sure you want to delete the content type %type?', array('%type' => $type->name)),
      'The content type is available for deletion.'
    );
    $this->assertText(t('This action cannot be undone.'), 'The node type deletion confirmation form is available.');
    // Test that forum node type could not be deleted while forum active.
    $this->container->get('module_handler')->install(array('forum'));
    $this->drupalGet('admin/structure/types/manage/forum');
    $this->assertNoLink(t('Delete'));
    $this->drupalGet('admin/structure/types/manage/forum/delete');
    $this->assertResponse(403);
    $this->container->get('module_handler')->uninstall(array('forum'));
    $this->drupalGet('admin/structure/types/manage/forum');
    $this->assertLink(t('Delete'));
    $this->drupalGet('admin/structure/types/manage/forum/delete');
    $this->assertResponse(200);
  }*/



}
