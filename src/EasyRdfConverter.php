<?php

/**
 * @file
 * Contains \Drupal\rdfui\EasyRdfConverter.
 */

namespace Drupal\rdfui;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

/**
 * Extracts details of RDF resources from an RDFa document.
 */
class EasyRdfConverter {

  /**
   * EasyRdf Graph of the loaded resource.
   *
   * @var \EasyRdf_Graph
   */
  private $graph;

  /**
   * List of Types specified in Schema.org as string.
   *
   * @var array()
   */
  private $listTypes;

  /**
   * List of Properties specified in Schema.org as string.
   *
   * @var array()
   */
  private $listProperties;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->listProperties = array();
    $this->listTypes = array();
  }

  /**
   * Creates an EasyRdf_Graph object from the given uri.
   *
   * @param string $uri
   *     Uri of a web resource or path of the cached file.
   * @param string $type
   *    Format of the document.
   *
   * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
   *    If incorrect type or url is passed as parameters.
   */
  public function createGraph($uri = "http://schema.org/docs/schema_org_rdfa.html", $type = "rdfa") {
    /*
     * Initialize an EasyRdf_Graph object using
     * _construct(string $uri = null, string $data = null,string $format = null)
     */
    if (!is_string($type) or $type == NULL or $type == '') {
      throw new InvalidArgumentException("\$type should be a string and cannot be null or empty");
    }
    if (!is_string($uri) or $uri == NULL or $uri == '') {
      throw new InvalidArgumentException("\$uri should be a string and cannot be null or empty");
    }

    try {
      if (preg_match('#^http#i', $uri) === 1) {
        $this->graph = new \EasyRdf_Graph($uri, NULL, $type);
        $this->graph->load();
      }
      else {
        $this->graph = new \EasyRdf_Graph(NULL);
        $this->graph->parseFile($uri);
      }
      $this->iterateGraph();
    }
    catch (\Exception $e) {
      throw new InvalidArgumentException("Invalid uri + $e");
    }

  }

  /**
   * Identify all types and properties of the graph separately.
   */
  private function iterateGraph() {
    $resource_list = $this->graph->resources();

    foreach ($resource_list as $value) {
      if ($value->prefix() !== "schema") {
        continue;
      }
      if ($value->isA("rdf:Property") || $value->isA("rdfs:Property")) {
        $this->addProperties($value);
      }
      else {
        $this->addType($value);
      }
    }
  }

  /**
   * Add Property label to list.
   *
   * @param \EasyRdf_Resource $value
   *   An EasyRdf_Resource which is a property.
   */
  private function addProperties(\EasyRdf_Resource $value) {
    if ($value != NULL) {
      $this->listProperties[$value->shorten()] = $value->label();
    }
  }

  /**
   * Add Type label to list.
   *
   * @param \EasyRdf_Resource $type
   *   An EasyRdf_Resource which is a type.
   */
  private function addType(\EasyRdf_Resource $type) {
    if ($type != NULL) {
      $this->listTypes[$type->shorten()] = $type->label();
    }
  }

  /**
   * Return list of Schema.org properties.
   *
   * @return array
   */
  public function getListProperties() {
    return $this->listProperties;
  }

  /**
   * Return list of Schema.org types.
   *
   * @return array
   */
  public function getListTypes() {
    return $this->listTypes;
  }

  /**
   * Extract properties of a given type
   *
   * @param $type string
   *   Schema.Org type of which the properties should be listed.
   *   (eg. "schema:Person")
   *
   * @return array options
   *   List of properties.
   */
  public function getTypeProperties($type) {
    $tokens = explode(":", $type);
    $prefixes = rdf_get_namespaces();
    $uri = $prefixes[$tokens[0]] . $tokens[1];

    $options = array();
    $options += $this->getProperties($uri);
    asort($options);
    return $options;
  }

  private function getProperties($uri) {
    $resource = array("type" => "uri", "value" => $uri);
    $props = $this->graph->resourcesMatching("http://schema.org/domainIncludes", $resource);
    $options = array();

    foreach ($props as $key => $value) {
      $options[$value->shorten()] = $value->get("rdfs:label")->getValue();
    }

    $parents = $this->graph->all($uri, "rdfs:subClassOf");
    foreach ($parents as $key => $value) {
      $options += $this->getProperties($value->getUri());
    }
    return $options;
  }

  /**
   * Returns description of the resource.
   *
   * @param string $uri
   * @return mixed
   *   Description of the resource or null.
   */
  public function description($uri) {
    if (empty($uri)) {
      drupal_set_message($this->t("Invalid uri"));
      return NULL;
    }

    $comment = $this->graph->get($uri, "rdfs:comment");
    if (!empty($comment)) {
      return $comment->getValue();
    }
    return NULL;
  }

  /**
   * Returns label of the resource.
   *
   * @param  $uri string.
   * @return string label of the resource, if not shortened name.
   */
  public function label($uri) {
    if (empty($uri)) {
      drupal_set_message($this->t("Invalid uri"));
      return NULL;
    }
    $label = $this->graph->label($uri);
    if (!empty($label)) {
      return $label;
    }

    $names = explode(":", $uri);
    return $names[1];
  }
}
