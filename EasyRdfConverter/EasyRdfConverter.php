<?php
/**
 * Extracts details of RDF resources from an RDFa document
 */

require 'vendor/autoload.php';


class EasyRdfConverter {

    /*
     *
     * @var EasyRdf_Graph
     */
    private $graph;

    public function createGraph($uri, $type ){

        /*
         * Initialize an EasyRdf_Graph object using
         *  _construct(string $uri = null, string $data = null, string $format = null)
         * eg: $graph = new EasyRdf_Graph("http://schema.org/docs/schema_org_rdfa.html",null,'rdfa');
         * */

        $this->graph = new EasyRdf_Graph($uri,null,$type);
        $this->graph->load();
    }

    public function serializeGraph(){
        $output=print_r($this->graph->serialise(EasyRdf_Format::getFormat("turtle")),true);
        file_put_contents("rdfa.txt",$output);
    }

    
}



