<?php

namespace Drupal\group_user_field_access\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\group_user_field_access\Form\UserFieldAccessSettingsFrom;

/**
 * Helper controller.
 *
 * Class UserFieldAccessController.
 *
 * @package Drupal\group_user_field_access\Controller
 */
class UserFieldAccessController extends ControllerBase {

  // Field names for account that will be hidden om user edit form.
  const HIDDEN_ACCOUNT_FIELDS = [
    'pass',
    'notify',
    'status',
  ];

  // Field names that should be hidden.
  const HIDDEN_FIELDS = [
    'contact',
    'language',
    'timezone',
  ];

  // Field names from account that should displayed like disabled.
  const READ_ONLY_ACCOUNT_FIELDS = [
    'name',
    'roles',
  ];

  // Field names that should be displayed like disabled.
  const READ_ONLY_FIELDS = [];

  // Field names that user can edit.
  const CAN_EDIT_FIELDS = [];

  /**
   * Check if team coordinator can edit user (that user is)
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Editable user.
   * @param \Drupal\Core\Session\AccountInterface $teamCoordinator
   *   Team coordinator user.
   *
   * @return bool
   *   teamCoordinator can edit user.
   */
  public static function teamCoordinatorCanEditUser(AccountInterface $user, AccountInterface $teamCoordinator) {
    if (!isset($teamCoordinator)) {
      $teamCoordinator = \Drupal::currentUser();
    }

    $group_membership_service = \Drupal::service('group.membership_loader');
    $groups = $group_membership_service->loadByUser($teamCoordinator);

    $allow_to_edit_user = FALSE;

    if ($groups) {
      // Get module settings for field access.
      $field_access_settings = UserFieldAccessSettingsFrom::getSettings();
      $team_coordinator_group_roles = $field_access_settings->get('team_coordinator_group_roles');

      foreach ($groups as $grp) {
        $groups[] = $grp->getGroup();

        $group = $grp->getGroup();

        $group_type = $group->getGroupType()->id();

        $team_coordinator_group = FALSE;

        if (isset($team_coordinator_group_roles[$group_type]) && $team_coordinator_group_roles[$group_type]) {
          $group_team_coordinator_role = $team_coordinator_group_roles[$group_type];

          // Get group member and their roles.
          $group_member = $group->getMember($teamCoordinator);
          $group_member_roles = $group_member->getRoles();

          // Check if team coordinator assigned to team coordinator role.
          if (in_array($group_team_coordinator_role,
            array_keys($group_member_roles))) {
            $team_coordinator_group = TRUE;
          }
        }

        // If user is team coordinator in this group.
        if ($team_coordinator_group) {
          // Check if requested user is member of team coordinator group.
          if ($group->getMember($user)) {
            $allow_to_edit_user = TRUE;

            break;
          }
        }
      }
    }

    return $allow_to_edit_user;
  }

  /**
   * Return array of user account fields.
   *
   * @return array
   *   array with fields.
   */
  public static function getUserAccountFields() {
    $fields = array_filter(
      \Drupal::service('entity_field.manager')
        ->getFieldDefinitions('user', 'user'),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof FieldConfigInterface;
      }
    );

    $_fields_options = [];

    foreach ($fields as $field_name => $field_class) {
      $_fields_options[$field_name] = $field_class->label();
    }

    return $_fields_options;
  }

}
