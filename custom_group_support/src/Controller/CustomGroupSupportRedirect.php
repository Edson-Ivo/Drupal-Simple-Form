<?php

/*
 * This code is a just redirect page.
*/


namespace Drupal\custom_group_support\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom group controller class for redirect.
 *
 * @package Drupal\custom_group_support\Controller
 */
class CustomGroupSupportRedirect extends ControllerBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The database connection dependency.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new CustomGroupSupportRedirect.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection dependency.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    Request $request,
    Connection $database,
    MessengerInterface $messenger) {
    $this->request = $request;
    $this->database = $database;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('database'),
      $container->get('messenger'),
    );
  }

  /**
   * Redirect to the support link.
   */
  public function redirectSupportLink() {

    $group = $this->request->get('group');

    $group_url = Url::fromRoute('entity.group.canonical', ['group' => $group->id()]);

    $query = $this->database->select('custom_group_support', 'cgs');
    $query = $query->fields('cgs', ['url']);
    $query = $query->condition('cgs.gid', $group->id());
    $support_url = $query->execute()->fetch();

    if ($support_url) {
      if (UrlHelper::isExternal($support_url->url)) {
        return new TrustedRedirectResponse($support_url->url);
      }
      return new RedirectResponse($support_url->url);
    }

    $this->messenger->addMessage("There is a problem with the redirect url.", $this->messenger::TYPE_WARNING);
    return new RedirectResponse($group_url->toString());

  }

}
