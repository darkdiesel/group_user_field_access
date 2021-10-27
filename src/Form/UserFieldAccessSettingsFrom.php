<?php

namespace Drupal\group_user_field_access\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure api settings for sending request to laravel.
 */
class UserFieldAccessSettingsFrom extends ConfigFormBase
{
  /**
   * Config react settings.
   *
   * @var string
   */
  const SETTINGS_KEY = 'group_user_field_access.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'group_user_field_access_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      static::SETTINGS_KEY,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // load module settings
    $settings = $this->config(static::SETTINGS_KEY);

    // Get list of user fields from /admin/config/people/accounts/fields page
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user'),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof \Drupal\field\FieldConfigInterface;
      }
    );

    $_fields_options = [];

    foreach ($fields as $field_name => $field_class) {
      $_fields_options[$field_name] = $field_name;
    }

    $form['editable_user_fields'] = [
      '#type' => 'checkboxes',
      '#title' => t("Editable user fields"),
      //'#description' => t(''),
      '#options' => $_fields_options,
      '#required' => FALSE,
      '#default_value' => $settings->get('editable_user_fields') ?? [],
    ];

    // load group types
    $group_types = \Drupal::entityTypeManager()
      ->getStorage('group_type')->loadMultiple();

    $form['team_coordinator_group_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Team coordinator group role'),
    ];

    $team_coordinator_group_roles_settings = $settings->get('team_coordinator_group_roles');

    foreach ($group_types as $group_type) {

      // load group roles for each group type and create select element
      $group_roles = \Drupal::entityTypeManager()
        ->getStorage('group_role')->loadByProperties([
          'group_type' => $group_type->id(),
        ]);

      $_group_roles = [];

      foreach ($group_roles as $group_role) {
        $_group_roles[$group_role->id()] = $group_role->label();
      }

      $form['team_coordinator_group_types']['team_coordinator_role_group_'.$group_type->id()] = [
        '#type' => 'select',
        '#title' => t("@group", ['@group' => $group_type->label()]),
        '#description' => t('Select team coordinator role for @group group type', ['@group' => $group_type->label()]),
        '#empty_option' => t('- Select team coordinator role -'),
        '#options' => $_group_roles,
        '#required' => FALSE,
        '#multiple' => FALSE,
        '#default_value' => $team_coordinator_group_roles_settings[$group_type->id()] ?? '',
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $team_coordinator_group_roles = [];

    $group_types = \Drupal::entityTypeManager()
      ->getStorage('group_type')->loadMultiple();

    foreach ($group_types as $group_type) {
      $form_state->getValue('team_coordinator_role_group_'.$group_type->id());

      $team_coordinator_group_roles[$group_type->id()] = $form_state->getValue('team_coordinator_role_group_'.$group_type->id());
    }

    $this->configFactory->getEditable(static::SETTINGS_KEY)
      ->set('editable_user_fields', $form_state->getValue('editable_user_fields'))
      ->set('team_coordinator_group_roles', $team_coordinator_group_roles)
      ->save();

    parent::submitForm($form, $form_state);
  }

  public static function getSettings(){

  }
}