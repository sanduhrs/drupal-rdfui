<?php
/**
 * Extracts details of RDF resources from an RDFa document
 */
require 'vendor/autoload.php';


class EasyRdfConverter
{

    /*
     * @var EasyRdf_Graph
     */
    private $graph;

    /*
     * list of Properties specified in Schema.org
     * @var array()
     */
    private $arrayProperties;

    /**
     * @return array
     */
    public function getArrayProperties()
    {
        return $this->arrayProperties;
    }


    /*
    * list of Types specified in Schema.org
    * @var array()
    */
    private $arrayTypes;

    /**
     * @return array
     */
    public function getArrayTypes()
    {
        return $this->arrayTypes;
    }

    /*
     * Php Array
     */
    private $output;

    function __construct()
    {
        $this->arrayProperties = array();
        $this->arrayTypes = array();
    }

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

        if (preg_match('#^http#i', $uri) === 1) {
            $this->graph = new EasyRdf_Graph($uri, null, $type);
            $this->graph->load();
            echo "Web Resource";
        } else {
            echo "Local file";
            $this->graph = new EasyRdf_Graph(null);
            $this->graph->parseFile($uri);
        }

        $this->output = $this->graph->toRdfPhp();
    }

    public function serializeGraph()
    {
        //  file_put_contents("rdfaMappings.html",$this->graph->dump());
        $printContent = print_r($this->graph->serialise(EasyRdf_Format::getFormat("turtle")), true);
        file_put_contents("rdfa.txt", $printContent);
        file_put_contents("rdfPhp.txt", print_r($this->output, true));
    }

    /*
     * Add Property label to list
     * Type is identified by the uppercase letter at the beginning
     */
    public function addType($type)
    {
        if ($type != null) {
            array_push($this->arrayTypes, $type);
        }
    }

    /*
     * Add Property label to list
     * Property is identified by the lowercase letter at the beginning
     * string $property - label of property
     */
    private function addProperties($key)
    {
        if ($key != null) {
            array_push($this->arrayProperties, $key);
        }
    }

    /*
     * Identify all types and properties of the graph separately
     */
    function IterateGraph()
    {
        $typeList = $this->graph->resources();
        print_r("Printing Resources".'/n');
        print_r(sizeof($typeList));

        foreach($typeList as $key=>$value)
        {
            if($value->isA("rdf:Property")){
                $this->addProperties($value);
            }else{
                $this->addType($value);
            }
        }

    }

    /*
     * Iterate Php Array
     */

    function iterate()
    {
        foreach ($this->output as $key => $value) {
            $word = $value['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
            if (ctype_upper($word[0])) {
                $this->addProperties($word);
            } else {
                $this->addType($word);
            }
        }
    }

    function printTypes()
    {
        print_r(" Types : " . sizeof($this->arrayTypes)."\t");
     /*   foreach($this->arrayTypes as $key=>$value){
            print_r($value->label()."\t");
        }*/
    }


    function printProperties()
    {
        print_r(" Properties" . sizeof($this->arrayProperties)."\t");
        /*foreach($this->arrayProperties as $key=>$value){
            print_r($value->label()."\t");
        }*/
    }

}




