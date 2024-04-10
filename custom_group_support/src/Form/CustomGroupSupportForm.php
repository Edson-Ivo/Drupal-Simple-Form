<?php

/*
 * This code gerenates a Form in drupal 10 to create a Support page Redirect.
 * The objective of this form is create a redirect link in a group(Group module), and if any group member has any questions, 
   he can click the link and go to the support page. 
 * This form is accessible for Group admin, so only the group admin can create a redirect link to
   support page.
 * When this form is submitted, the group admin will be creating a site support link that will be saved in the database,
   after that, the group main page will show an <a> tag with this link and when the group member click on it,
   it'll be redirected to group site support.
 * The form has 3 options for creating the site support link:
   Support page: Creates a redirect link to a Drupal Content with the "Support Page" type.
   Zendesk: Creates a redirect link to a another page that is a zendesk.
   None: Don't creates any link and won't create a <a> tag in main page of the group.
 * This form will reuse the data, it will not save all updates.
*/

namespace Drupal\custom_group_support\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom Group support.
 *
 * @package Drupal\custom_group_support\Form
 */
class CustomGroupSupportForm extends FormBase {

  // Here I'm using Dependecy Injection to do good practice in code.

  /**
   * The Current User.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

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
   * Constructs a new GroupSupportForm.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection dependency.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    AccountInterface $account,
    Request $request,
    Connection $database,
    MessengerInterface $messenger) {
    $this->account = $account; 
    // I need the user who is making the submission;
    $this->request = $request; 
    // I need to get the group that is a parent of this form, to create the group's support page;
    $this->database = $database;
    // I need the database to get datas about support page and save.
    $this->messenger = $messenger;
    // I need send a message to user using the default drupal message.
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_group_support_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $group = $this->request->get('group');
    // getting the root group

    $query = $this->database->select('custom_group_support', 'cgs');
    $query = $query->fields('cgs', ['url', 'method']);
    $query = $query->condition('cgs.gid', $group->id());
    $support_data = $query->execute()->fetch();
    // getting the url and the method field;

    $supports_pages = $group->getRelationships("group_node:support_page");
    $options = [];
    foreach ($supports_pages as $support_page) {
      $node = $support_page->getEntity();
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
      $options[$url] = $node->label();
    }
    // Will get the Support Page contents related to this group and save their url;

    $form['support_method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Action when a user clicks on Support.'),
      '#default_value' => $support_data ? $support_data->method : "none",
      '#options' => [
        'support_page' => $this->t('Open a Support Page.'),
        'zendesk' => $this->t('Redirect to Zendesk.'),
        'none' => $this->t('None - Hide the Support Link.'),
        /* If the group admin select the:
          Support page: Creates a field to select the Support Pages content related with group.
          Zendesk: Creates a textfield to type the link of zendesk page.
          None: Doesn't creates anything and if you submit it, will remove the last saved link.
        */
      ],
      '#ajax' => [
        'callback' => '::showFields',
        'wrapper' => 'option-wrapper',
        'effect' => 'fade',
        // ajax to reload the rest form according to selected method;
      ],
    ];

    $form['option_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'option-wrapper'],
    ];
    // Add an Id to the rest form (here have the changes)

    $method_selected = '';
    if ($form_state->getValue('support_method')) {
      $method_selected = $form_state->getValue('support_method');
    }
    elseif ($support_data) {
      $method_selected = $support_data->method;
    }
    // Getting the method and doing the changes in rest form;

    switch ($method_selected) {
      case 'support_page':
        $italic_text = '<em>' . $this->t('new') . '</em>';
        $url_here = URL::fromRoute('entity.group_relationship.create_form',
          [
            'group' => $group->id(),
            'plugin_id' => 'group_node:support_page',
          ]);
        // link to create a new Support Page;
        $link = Link::fromTextAndUrl('here', $url_here)->toString();
        $form['option_wrapper']['redirect_link'] = [
          '#type' => 'select',
          '#prefix' => $this->t('Opens the specified Group Support Page.'),
          '#title' => $this->t('Select your Group Support page.'),
          '#suffix' => $this->t('Click @here to create a @new Support Page.',
            [
              '@here' => $link,
              '@new' => Markup::create($italic_text),
            ]),
          '#default_value' => $support_data ? $support_data->url : "",
          '#options' => $options,
        ];
        break;

      case 'zendesk':
        $form['option_wrapper']['redirect_link_zendesk'] = [
          '#type' => 'textfield',
          '#prefix' => $this->t("Redirects the user to the Group configured Zendesk Support page."),
          '#title' => $this->t("Enter the URL of your Group Zendesk Support page."),
          '#default_value' => $support_data ? $support_data->url : "",
        ];
        break;

      case 'none':
        break;
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $redirect_link_zendesk = $form_state->getValue('redirect_link_zendesk');
    $support_method = $form_state->getValue('support_method');
    if ($support_method == 'zendesk') {
      if (filter_var($redirect_link_zendesk, FILTER_VALIDATE_URL)) {
        return;
      }
      $form_state->setErrorByName('redirect_link', $this->t('Url invalid.'));
    }
  }
  //Creating the validate to verify if zendesk link is valid;

  /**
   * Ajax redirect.
   */
  public function showFields(array &$form, FormStateInterface $form_state) {
    return $form['option_wrapper'];
  }

  /**
   * Save the link redirect.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $redirect_link = $form_state->getValue('redirect_link');
    $redirect_link_zendesk = $form_state->getValue('redirect_link_zendesk');
    $support_method = $form_state->getValue('support_method');
    $group = $this->request->get('group');
    $current_user_id = $this->account->id();
    $timestamp = time();
    $link = '';

    switch ($support_method) {
      case 'zendesk':
        $link = $redirect_link_zendesk;
        break;

      case 'support_page':
        $link = $redirect_link;
        break;

    }

    $result = $this->database->merge('custom_group_support')
      ->key('gid', $group->id())
      ->fields([
        'uid' => $current_user_id,
        'gid' => $group->id(),
        'method' => $support_method,
        'url' => $link,
        'created' => $timestamp,
      ]);
    if ($result->execute()) {
      $form_state->setRedirect('entity.group.canonical', ['group' => $group->id()]);
      $this->messenger->addMessage("Support Link has been saved.");
    }
  }

}
