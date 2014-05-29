<?php
/*testing functionality of EasyRdfConverter class*/

include 'EasyRdfConverter.php';

    $uri="/home/sachini/workspace/rdfui/RDFaLiteReflection.html";
    $type="rdfa";

    /*
     * Start timestamp
     */
    $start = microtime(true);;

    $converter=new EasyRdfConverter();
    $converter->createGraph($uri,$type);
    /*$converter->serializeGraph();*/
    $converter->iterate();
    $converter->printProperties();
    $converter->printTypes();

    $time_taken = microtime(true) - $start;
    print_r("total time : $time_taken");