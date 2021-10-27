<?php


namespace Drupal\group_user_field_access\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides group membership route controllers.
 *
 * This only controls the routes that are not supported out of the box by the
 * plugin base \Drupal\group\Plugin\GroupContentEnablerBase.
 */
class UserFieldAccessController extends ControllerBase {

  // field names that will be hidden om user edit form
  const hidden_account_fields = [
    'pass',
    'notify',
    'roles',
    'status',
  ];

  const hidden_fields = [
    'contact',
    'timezone',
  ];

  const read_only_account_fields = [
    'mail',
  ];

  const read_only_fields = [

  ];

  /**
   * Return array of user account fields
   *
   * @return array
   */
  public static function getUserAccountFileds() {
    $fields = array_filter(
      \Drupal::service('entity_field.manager')
        ->getFieldDefinitions('user', 'user'),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof \Drupal\field\FieldConfigInterface;
      }
    );

    $_fields_options = [];

    foreach ($fields as $field_name => $field_class) {
      $_fields_options[$field_name] = $field_name;
    }

    return $_fields_options;
  }

}