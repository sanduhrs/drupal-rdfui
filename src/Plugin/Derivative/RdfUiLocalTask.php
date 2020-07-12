<?php

namespace Drupal\rdfui\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class RdfUiLocalTask extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates an FieldUiLocalTask object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   */
  public function __construct(RouteProviderInterface $route_provider, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->routeProvider = $route_provider;
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $entity_type_ids = [
      'user',
      'node',
      'comment',
      'taxonomy_term',
    ];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->get('field_ui_base_route')
          && (in_array($entity_type_id, $entity_type_ids))) {
        $this->derivatives["overview_$entity_type_id"] = array(
          'route_name' => "entity.$entity_type_id.field_ui_fields",
          'title' => $this->t('Fields'),
          'parent_id' => "field_ui.fields:overview_$entity_type_id",
        );

        $this->derivatives["rdf_$entity_type_id"] = array(
          'route_name' => "entity.$entity_type_id.rdf_ui_fields",
          'weight' => 2,
          'title' => $this->t('RDF Mappings'),
          'parent_id' => "field_ui.fields:overview_$entity_type_id",
        );

      }
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $this->derivatives;
  }

  /**
   * Alters the base_route definition for RDF UI local tasks.
   *
   * @param array $local_tasks
   *   An array of local tasks plugin definitions, keyed by plugin ID.
   */
  public function alterLocalTasks(array &$local_tasks) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($route_name = $entity_type->get('field_ui_base_route')
        && ($entity_type_id === "node" || $entity_type_id === "user")) {

        $local_tasks["field_ui.fields:rdf_$entity_type_id"]['base_route'] = $route_name;
        $local_tasks["field_ui.fields:overview_$entity_type_id"]['base_route'] = $route_name;
      }
    }
  }
}
