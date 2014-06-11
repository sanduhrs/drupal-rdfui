<?php

namespace Drupal\rdfui\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class MappingListingController implements  ContainerInjectionInterface{

    public static function create(ContainerInterface $container){
        return new static($container->get('module _handler'));

    }

    public function mappingListingPage(){
        return array('$markup'=>t('Hello! This is rdfui module.'));
    }
}

?>