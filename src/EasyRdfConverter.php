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
        //$uri="http://schema.org/docs/schema_org_rdfa.html";
        $uri="//home/sachini/workspace/RDFaLiteReflection.html";
        $type="rdfa";
        $this->createGraph($uri,$type);
    }

    /**
     * Creates an EasyRdf_Graph object from the given uri
     *
     * @param uri string
     * uri of a web resource or path of the cached file
     *
     * @param type string
     * format of the document
     */
    public function createGraph($uri, $type)
    {
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


        try{
            if (preg_match('#^http#i', $uri) === 1) {
                $this->graph = new \EasyRdf_Graph($uri, null, $type);
                $this->graph->load();
            } else {
                $this->graph = new \EasyRdf_Graph(null);
                $this->graph->parseFile($uri);
            }
            $this->iterateGraph();
        }catch (\Exception $e){
            throw new InvalidArgumentException("Invalid uri + $e");
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
            $this->listTypes[$type->shorten()]=$type->label();
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
            $this->listProperties[$value->shorten()]=$value->label();
        }
    }

    /**
     * Identify all types and properties of the graph separately
     */
    function iterateGraph()
    {
        $typeList = $this->graph->resources();

        foreach($typeList as $key=>$value)
        {
            if($value->isA("rdf:Property")){
                $this->addProperties($value);
            }else{
                $this->addType($value);
            }
        }
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
     * return list of Schema.org properties
     * @return array
     */
    function getListProperties()
    {
        return $this->listProperties;
    }

}




