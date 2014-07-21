<?php

/**
 * @file
 * Contains \Drupal\rdfui\Tests\EasyRdfConverterTest.
 */

namespace Drupal\rdfui\Tests;

use Drupal\rdfui\EasyRdfConverter;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests the RDFa markup of Nodes.
 */
class EasyRdfConverterTest extends DrupalUnitTestBase
{

    /**
     * Modules to enable.
     *
     * @var array
     */
    public static $modules = array('rdf', 'rdfui');

    public static function getInfo()
    {
        return array(
            'name' => 'Schema.Org mapping',
            'description' => 'Tests the EasyRdfConverter Class.',
            'group' => 'RdfUI',
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->graph = new EasyRdfConverter();
        $this->graph->createGraph();
    }

    function testSchemaTypes()
    {
        $types = $this->graph->getListTypes();
        $this->assertTrue(in_array("Person", $types), 'Schema.Org types loaded correctly');
        $this->assertTrue(in_array("Event", $types), 'Schema.Org types loaded correctly');
        $this->assertTrue(in_array("Recipe", $types), 'Schema.Org types loaded correctly');
        $this->assertFalse(in_array("name", $types), 'Properties are not in the list of Types');
    }

    function testSchemaProperty()
    {
        $properties = $this->graph->getListProperties();
        $this->assertTrue(in_array("name", $properties), 'Schema.Org properties loaded correctly');
        $this->assertTrue(in_array("url", $properties), 'Schema.Org properties loaded correctly');
        $this->assertTrue(in_array("image", $properties), 'Schema.Org properties loaded correctly');
        $this->assertFalse(in_array("Person", $properties), 'Types are not in the list of Properties');
    }

    function testPropertiesOfType()
    {
        $properties = $this->graph->getTypeProperties("schema:Article");
        $this->assertTrue(in_array("wordCount", $properties), 'Properties of Type(Article) loaded.');
        $this->assertTrue(in_array("author", $properties), 'Properties of parent Type(CreativeWork)loaded.');
        $this->assertTrue(in_array("name", $properties), 'Properties of base Type(Thing) loaded.');
        $this->assertFalse(in_array("birthDate", $properties), 'Properties not in the Type are not loaded.');
    }


}
