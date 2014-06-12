<?php
/*testing functionality of EasyRdfConverter class*/

include 'EasyRdfConverter.php';

    $uri="/home/sachini/workspace/rdfui/RDFaLiteReflection.html";
    $type="rdfa";

    /*
     * Start timestamp
     */
    $iterations=1000;
    $start = microtime(true);;

    for($x=0;$x<$iterations;$x++){
        print_r("\nIteration ".$x);
        $converter=new EasyRdfConverter();
        $converter->createGraph($uri,$type);

        //$converter->serializeGraph();

       /*Iteration using Php array*/
       /* $converter->iterate();
        $converter->printProperties();
        $converter->printTypes();*/

        $converter->IterateGraph();
        $converter->printProperties();
        $converter->printTypes();
    }

    $time_taken = microtime(true) - $start;
    print_r("total time (micro seconds) : $time_taken"."\n");
    $avg=$time_taken/$iterations;
    print_r("average time (micro seconds): ".$avg);