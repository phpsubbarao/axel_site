<?php

namespace Drupal\axel\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_api_key",
 *   label = @Translation("Get Site Key"),
 *   uri_paths = {
 *     "canonical" = "/page_json/{nid}/{key}"
 *   }
 * )
 */
class Getapikey extends ResourceBase {

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructs a Drupal\rest\Plugin\ResourceBase object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param array $serializer_formats
     *   The available serialization formats.
     * @param \Psr\Log\LoggerInterface $logger
     *   A logger instance.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   A current user instance.
     */
    public function __construct(
        array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration, $plugin_id, $plugin_definition, $container->getParameter('serializer.formats'), $container->get('logger.factory')->get('plus_group'), $container->get('current_user')
        );
    }

    /**
     * Responds to GET requests.
     *
     * Returns a list of bundles for specified entity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get($nid,$key) {

    $config = \Drupal::config('system.site');
    $site_key = $config->get('siteapikey');

    if($site_key != $key){
       throw new AccessDeniedHttpException();
    }

        $entities = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'page')
        ->condition('langcode', 'en')
        ->condition('nid', $nid)
        ->accessCheck(false)
        ->execute();

        if (!empty($entities)) {


            $result['response'] = ['status' => 'Valid URL'];
            $response = new ResourceResponse($result);
            $disable_cache = new CacheableMetadata();
            $disable_cache->setCacheMaxAge(0);
            $response->addCacheableDependency($disable_cache);
        } else {
            $result['response'] = ['status' => 'access deniesd'];
            $response = new ResourceResponse($result);
        }
        return $response;
    }

}
