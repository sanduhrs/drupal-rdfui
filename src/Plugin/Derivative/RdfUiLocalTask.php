<?php

/**
 * @file
 * Contains \Drupal\rdfui\Plugin\Derivative\RdfUiLocalTask.
 */
 
 namespace Drupal\rdfui\Plugin\Derivative;
 
 use Drupal\Core\Entity\EntityManagerInterface;
 use Drupal\Component\Plugin\Derivative\DerivativeBase;
 use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
 use Drupal\Core\Routing\RouteProviderInterface;
 use Drupal\Core\StringTranslation\StringTranslationTrait;
 use Drupal\Core\StringTranslation\TranslationInterface;
 use Symfony\Component\DependencyInjection\ContainerInterface;
 
 /**
  * Provides local task definitions for all entity bundles.
  */
 class RdfUiLocalTask extends DerivativeBase implements ContainerDerivativeInterface {
   use StringTranslationTrait;
 
   /**
    * The route provider.
    *
    * @var \Drupal\Core\Routing\RouteProviderInterface
    */
   protected $routeProvider;
 
   /**
    * The entity manager
    *
    * @var \Drupal\Core\Entity\EntityManagerInterface
    */
   protected $entityManager;
 
   /**
    * Creates an RdfUiLocalTask object.
    *
    * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
    *   The route provider.
    * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
    *   The entity manager.
    * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
    *   The translation manager.
    */
   public function __construct(RouteProviderInterface $route_provider, EntityManagerInterface $entity_manager, TranslationInterface $string_translation) {
     $this->routeProvider = $route_provider;
     $this->entityManager = $entity_manager;
     $this->stringTranslation = $string_translation;
   }
 
   /**
    * {@inheritdoc}
    */
   public static function create(ContainerInterface $container, $base_plugin_id) {
     return new static(
       $container->get('router.route_provider'),
       $container->get('entity.manager'),
       $container->get('string_translation')
     );
   }
 
   /**
    * {@inheritdoc}
    */
   public function getDerivativeDefinitions($base_plugin_definition) {
     $this->derivatives = array();
 
     foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
       if ($entity_type->isFieldable() && $entity_type->hasLinkTemplate('admin-form')) {
 
         $this->derivatives['rdf_' . $entity_type_id] = array(
           'title' => 'Rdf Mapping',
           'route_name' => "rdfui.rdf_$entity_type_id",
           'parent_id' => "field_ui.fields:form_display_overview_$entity_type_id",
           'weight' => -1,
         );
       }
     }
 
     foreach ($this->derivatives as &$entry) {
       $entry += $base_plugin_definition;
     }
 
     return $this->derivatives;
   }
 
   /**
    * Alters the base_route definition for field_ui local tasks.
    *
    * @param array $local_tasks
    *   An array of local tasks plugin definitions, keyed by plugin ID.
    */
     public function alterLocalTasks(&$local_tasks) {
         foreach ($this->entityManager->getDefinitions() as $entity_type => $entity_info) {
             if ($entity_info->isFieldable() && $entity_info->hasLinkTemplate('admin-form')) {
                 $admin_form = $entity_info->getLinkTemplate('admin-form');
                 $local_tasks["rdfui.rdf_$entity_type_id"]['base_route'] = $admin_form;

             }
         }
     }
 }
