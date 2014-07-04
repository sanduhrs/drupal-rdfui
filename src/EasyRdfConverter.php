<?php

/**
 * @file
 * Contains \Drupal\rdfui\EasyRdfConverter.
 */

namespace Drupal\rdfui;

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
     * list of Properties specified in Schema.org as EasyRdf_Resource
     * @var array()
     */
    private $arrayProperties;

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

    /**
     * list of Types specified in Schema.org as EasyRdf_Resource
     * @var array()
     */
    private $arrayTypes;

    /*constructor*/
    function __construct()
    {
        $this->arrayProperties = array();
        $this->arrayTypes = array();
        $this->listProperties = array();
        $this->listTypes = array();
        $uri="/home/sachini/workspace/rdfui/RDFaLiteReflection.html";
        $type="rdfa";
        $this->createGraph($uri,$type);
    }

    /**
     * Returns an array of properties as resources
     * @return array
     */
    public function getArrayProperties()
    {
        return $this->arrayProperties;
    }

    /**
     * Returns an array of types as resources
     * @return array
     */
    public function getArrayTypes()
    {
        return $this->arrayTypes;
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

        /*insert try-catch*/
        if (preg_match('#^http#i', $uri) === 1) {
            $this->graph = new \EasyRdf_Graph($uri, null, $type);
            $this->graph->load();
        } else {
            $this->graph = new \EasyRdf_Graph(null);
            $this->graph->parseFile($uri);
        }

        $this->iterateGraph();
      //  $this->output = $this->graph->toRdfPhp();
    }

    /**
     * Serialize the graph as a text file
     */
    public function serializeGraph()
    {
        //  file_put_contents("rdfaMappings.html",$this->graph->dump());
        $printContent = print_r($this->graph->serialise(\EasyRdf_Format::getFormat("turtle")), true);
        file_put_contents("rdfa.txt", $printContent);
        file_put_contents("rdfPhp.txt", print_r($this->output, true));
    }

    /**
     * Add Property label to list
     * Type is identified by the uppercase letter at the beginning
     */
    private function addType(\EasyRdf_Resource $type, $key)
    {
        if ($type != null) {
            array_push($this->arrayTypes, $type);
            $this->listTypes[$key]=$type->shorten();
        }
    }

    /**
     * Add Property label to list
     *
     * @param EasyRdf_Resource
     *   value
     */
    private function addProperties(\EasyRdf_Resource $value, $key)
    {
        if ($key != null) {
            array_push($this->arrayProperties, $value);
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
                $this->addProperties($value, $key);
            }else{
                $this->addType($value,$key);
            }
        }
    }

    /**
     * return list of Schema.org types
     */
    function printTypes()
    {
        print_r(" Types : " . sizeof($this->arrayTypes)."\t");
     /*   foreach($this->arrayTypes as $key=>$value){
            print_r($value->label()."\t");
        }*/
    }

    /**
     * return list of Schema.org properties
     */
    function printProperties()
    {
        print_r(" Properties" . sizeof($this->arrayProperties)."\t");
        /*foreach($this->arrayProperties as $key=>$value){
            print_r($value->label()."\t");
        }*/
    }

    /**
     * return list of Schema.org types
     */
    function getListTypes()
    {
        return $this->listTypes;
    }

    /**
     * return list of Schema.org properties
     */
    function getListProperties()
    {
        return $this->listProperties;
    }

}




