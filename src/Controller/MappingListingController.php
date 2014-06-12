<?php

namespace Drupal\rdfui\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class MappingListingController implements  ContainerInjectionInterface{

    public static function create(ContainerInterface $container){
        return new static($container->get('module _handler'));
    }

    /**
     * Return the 'Mapping Listing PAge' page.
     *
     * @return string
     *   A render array containing our page content.
     */
    public function mappingListingPage(){
        return array(
            '#markup'=>t('Hello! This is rdfui module.')
        );

    }
}

