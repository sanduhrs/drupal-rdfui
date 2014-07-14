<?php

/**
 * @file
 * Contains \Drupal\rdfui\Tests\EasyRdfConverterTest.
 */

namespace Drupal\rdfui\Tests;

use Drupal\simpletest\DrupalUnitTestBase;
use Drupal\rdfui\EasyRdfConverter;

/**
 * Tests the RDFa markup of Nodes.
 */
class EasyRdfConverterTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('rdf','rdfui');

  public static function getInfo() {
    return array(
      'name' => 'Schema.Org mapping',
      'description' => 'Tests the EasyRdfConverter Class.',
      'group' => 'RdfUI',
    );
  }

  public function setUp() {
    parent::setUp();
     $this->graph = new EasyRdfConverter();
  }

  function testSchemaTypes() {
      $types=$this->graph->getListTypes();
      $this->assertTrue(in_array("Person",$types), 'Schema.Org types loaded correctly');
      $this->assertTrue(in_array("Event",$types), 'Schema.Org types loaded correctly');
      $this->assertTrue(in_array("Recipe",$types), 'Schema.Org types loaded correctly');
      $this->assertFalse(in_array("name" ,$types), 'Schema.Org types loaded correctly');
  }

  function testSchemaProperty() {
    $properties=$this->graph->getListProperties();
    $this->assertTrue(in_array("name",$properties), 'Schema.Org properties loaded correctly');
    $this->assertTrue(in_array("url",$properties), 'Schema.Org properties loaded correctly');
    $this->assertTrue(in_array("image",$properties), 'Schema.Org properties loaded correctly');
    $this->assertFalse(in_array("Person",$properties), 'Schema.Org properties loaded correctly');
  }



}
