<?php
///*
///**
// * @file
// * Contains \Drupal\rdfui\Routing\RouteSubscriber.
// */
//
//namespace Drupal\rdfui\Routing;
//
//use Drupal\Core\Entity\EntityManagerInterface;
//use Drupal\Core\Routing\RouteSubscriberBase;
//use Drupal\Core\Routing\RoutingEvents;
//use Symfony\Component\Routing\Route;
//use Symfony\Component\Routing\RouteCollection;
//
///**
// * Subscriber for RDF UI routes.
// */
//class RouteSubscriber extends RouteSubscriberBase {
//
//  /**
//   * The entity type manager
//   *
//   * @var \Drupal\Core\Entity\EntityManagerInterface
//   */
//  protected $manager;
//
//  /**
//   * Constructs a RouteSubscriber object.
//   *
//   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
//   *   The entity type manager.
//   */
//  public function __construct(EntityManagerInterface $manager) {
//    $this->manager = $manager;
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  protected function alterRoutes(RouteCollection $collection) {
//    foreach ($this->manager->getDefinitions() as $entity_type_id => $entity_type) {
//      $defaults = array();
//      if ($entity_type->isFieldable() && $entity_type->hasLinkTemplate('admin-form')) {
//        // Try to get the route from the current collection.
//        if (!$entity_route = $collection->get($entity_type->getLinkTemplate('admin-form'))) {
//          continue;
//        }
//        if($entity_type->get('label')!='node'){
//            continue;
//        }
//        $path = $entity_route->getPath();
//
//        // If the entity type has no bundles, use the entity type.
//        $defaults['entity_type_id'] = $entity_type_id;
//        if (!$entity_type->hasKey('bundle')) {
//          $defaults['bundle'] = $entity_type_id;
//        }
//
//
//        $route = new Route(
//          "$path/rdfa",
//          array(
//            '_form' => '\Drupal\rdfui\FieldRdf',
//            '_title' => 'Rdf Mapping',
//          ) + $defaults,
//          array('_permission' => 'administer ' . $entity_type_id . ' fields')
//        );
//        $collection->add("rdfui.rdf_$entity_type_id", $route);
//      }
//    }
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function getSubscribedEvents() {
//    $events = parent::getSubscribedEvents();
//    $events[RoutingEvents::ALTER] = array('onAlterRoutes', -100);
//    return $events;
//  }
//
//}*/
