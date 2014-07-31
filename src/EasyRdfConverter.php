<?php

/**
 * @file
 * Contains \Drupal\rdfui\EasyRdfConverter.
 */

namespace Drupal\rdfui;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

/**
 * Extracts details of RDF resources from an RDFa document
 */
class EasyRdfConverter
{

    /**
     * @var \EasyRdf_Graph
     */
    private $graph;

    /**
     * list of Types specified in Schema.org as string
     * @var array()
     */
    private $listTypes;

    /**
     * list of Properties specified in Schema.org as string
     * @var array()
     */
    private $listProperties;

    /*constructor*/
    function __construct()
    {
        $this->listProperties = array();
        $this->listTypes = array();
    }

    /**
     * return list of Schema.org types
     * @return array
     */
    function getListTypes()
    {
        return $this->listTypes;
    }

    /**
     * Fetch web resource of the specified type and extract properties
     *
     * @param String type
     *  Schema.Org type of which the properties should be listed (eg. "schema:Person")
     *
     * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
     * @return array options
     *  list of properties
     */
    function ofType($type)
    {
        $tokens = explode(":", $type);
        $prefixes = rdf_get_namespaces();
        $uri = $prefixes[$tokens[0]] . $tokens[1];
        try {
            $resource = new EasyRdfConverter();
            $resource->createGraph($uri, 'rdfa');
            return $resource->getListProperties();
        } catch (\Exception $e) {
            throw new InvalidArgumentException("\$type cannot be found");
        }
    }

    /**
     * Creates an EasyRdf_Graph object from the given uri
     *
     * @param string $uri
     *     uri of a web resource or path of the cached file
     *
     * @param  string $type
     *    format of the document
     *
     * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
     */
    public function createGraph($uri = "http://schema.org/docs/schema_org_rdfa.html", $type = "rdfa")
    {
        //$uri = "/home/sachini/workspace/RDFaLiteReflection.html";
        /*
         * Initialize an EasyRdf_Graph object using
         *  _construct(string $uri = null, string $data = null, string $format = null)
         * eg: $graph = new EasyRdf_Graph("http://schema.org/docs/schema_org_rdfa.html",null,'rdfa');
         * */
        if (!is_string($type) or $type == null or $type == '') {
            throw new InvalidArgumentException("\$type should be a string and cannot be null or empty");
        }
        if (!is_string($uri) or $uri == null or $uri == '') {
            throw new InvalidArgumentException("\$uri should be a string and cannot be null or empty");
        }


        try {
            if (preg_match('#^http#i', $uri) === 1) {
                $this->graph = new \EasyRdf_Graph($uri, null, $type);
                $this->graph->load();
            } else {
                $this->graph = new \EasyRdf_Graph(null);
                $this->graph->parseFile($uri);
            }
            $this->iterateGraph();
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid uri + $e");
        }


    }

    /**
     * Identify all types and properties of the graph separately
     */
    function iterateGraph()
    {
        $typeList = $this->graph->resources();

        foreach ($typeList as $key => $value) {
            if ($value->isA("rdf:Property") || $value->isA("rdfs:Property")) {
                $this->addProperties($value);
            } else {
                $this->addType($value);
            }
        }
    }

    /**
     * Add Property label to list
     *
     * @param \EasyRdf_Resource $value
     *  an EasyRdf_Resource which is a property
     */
    private function addProperties(\EasyRdf_Resource $value)
    {
        if ($value != null) {
            $this->listProperties[$value->shorten()] = $value->localName();
        }
    }

    /**
     * Add Type label to list
     *
     * @param \EasyRdf_Resource $type
     *  an EasyRdf_Resource which is a type
     */
    private function addType(\EasyRdf_Resource $type)
    {
        if ($type != null) {
            $this->listTypes[$type->shorten()] = $type->localName();
        }
    }

    /**
     * return list of Schema.org properties
     * @return array
     */
    function getListProperties()
    {
        return $this->listProperties;
    }

    /**
     * Extract properties of a given type
     * @param string type
     *  Schema.Org type of which the properties should be listed (eg. "schema:Person")
     *
     * @return array options
     *  list of properties
     */
    function getTypeProperties($type)
    {
        $tokens = explode(":", $type);
        $prefixes = rdf_get_namespaces();
        $uri = $prefixes[$tokens[0]] . $tokens[1];
        $options = array();
        $options += $this->getProperties($uri);
        asort($options);
        return $options;
    }

    private function getProperties($uri)
    {
        $resource = array("type" => "uri", "value" => $uri);
        $props = $this->graph->resourcesMatching("http://schema.org/domainIncludes", $resource);
        $options = array();

        foreach ($props as $key => $value) {
            $options[$value->shorten()] = $value->localname();
        }

        $parents = $this->graph->all($uri, "rdfs:subClassOf");
        foreach ($parents as $key => $value) {
            $options += $this->getProperties($value->getUri());
        }
        return $options;
    }

}




