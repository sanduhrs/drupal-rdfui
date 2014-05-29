<?php
/**
 * Extracts details of RDF resources from an RDFa document
 */
require 'vendor/autoload.php';


class EasyRdfConverter {

    /*
     * @var EasyRdf_Graph
     */
    private $graph;

    /*
     * list of Properties specified in Schema.org
     * @var array()
     */
    private $arrayProperties;


    /*
    * list of Types specified in Schema.org
    * @var array()
    */
    private $arrayTypes;

    /*
     * Php Array
     */
    private $output;

    function __construct(){
        $this->arrayProperties=array();
        $this->arrayTypes=array();
    }

    public function createGraph($uri, $type ){
        /*
         * Initialize an EasyRdf_Graph object using
         *  _construct(string $uri = null, string $data = null, string $format = null)
         * eg: $graph = new EasyRdf_Graph("http://schema.org/docs/schema_org_rdfa.html",null,'rdfa');
         * */

        if (preg_match('#^http#i', $uri) === 1)
        {
            $this->graph = new EasyRdf_Graph($uri,null,$type);
            $this->graph->load();
            echo "Web Resource";
        }
        else
        {
            echo "Local file";
            $this->graph=new EasyRdf_Graph(null);
            $this->graph->parseFile($uri);
        }

        $this->output=$this->graph->toRdfPhp();
    }

    public function serializeGraph(){
      //  file_put_contents("rdfaMappings.html",$this->graph->dump());
        $printContent=print_r($this->graph->serialise(EasyRdf_Format::getFormat("turtle")),true);
        file_put_contents("rdfa.txt",$printContent);
        file_put_contents("rdfPhp.txt",print_r($this->output,true));
    }

    /*
     * Add Property label to list
     * Type is identified by the uppercase letter at the beginning
     */
    public function addType($type){
        if($type!=null)
        {
            array_push($this->arrayTypes,$type);
        }
    }

    /*
     * Add Property label to list
     * Property is identified by the lowercase letter at the beginning
     * string $property - label of property
     */
    private function arrayProperties($key)
    {
        if($key!=null){
            array_push($this->arrayProperties,$key);
        }
    }

    /*
     * Get all the resources in the graph of a certain type
     * Not working
     */
    function getTypes(){
        $typeList=$this->graph->allOfType("schema:Class");
        var_dump($typeList);
    }

    function iterate(){
        foreach($this->output as $key=>$value){
            $word=$value['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
            if(ctype_upper( $word[0] )){
                $this->arrayProperties($word);
            }else{
                $this->addType($word);
            }
        }
    }

    function printTypes(){
        print_r("List of Types");
        var_dump($this->arrayTypes);
    }


    function printProperties(){
        print_r("List of Properties");
        var_dump($this->arrayProperties);
    }

}




