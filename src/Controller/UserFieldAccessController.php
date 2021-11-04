<?php


namespace Drupal\group_user_field_access\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group_user_field_access\Form\UserFieldAccessSettingsFrom;

/**
 * Helper controller
 *
 * Class UserFieldAccessController
 *
 * @package Drupal\group_user_field_access\Controller
 */
class UserFieldAccessController extends ControllerBase {

  // field names that will be hidden om user edit form
  const hidden_account_fields = [
    'name',
    'pass',
    'notify',
    'roles',
    'status',
  ];

  const hidden_fields = [
    'contact',
    'timezone',
    'language',
  ];

  const read_only_account_fields = [
    'mail',
  ];

  const read_only_fields = [

  ];

  /**
   * Check if team coordinator can edit user (that user is)
   *
   * @param $teamCoordinator
   * @param $user
   *
   * @return bool
   */
  public static function teamCoordinatorCanEditUser($teamCoordinator = NULL, $user) {
    if (!isset($teamCoordinator)) {
      $teamCoordinator = \Drupal::currentUser();
    }

    $grp_membership_service = \Drupal::service('group.membership_loader');
    $grps = $grp_membership_service->loadByUser($teamCoordinator);

    $allow_to_edit_user = FALSE;

    if ($grps) {
      // get module settings for field access
      $field_access_settings = UserFieldAccessSettingsFrom::getSettings();
      $team_coordinator_group_roles = $field_access_settings->get('team_coordinator_group_roles');

      foreach ($grps as $grp) {
        $groups[] = $grp->getGroup();

        $group = $grp->getGroup();

        $group_type = $group->getGroupType()->id();

        $team_coordinator_group = FALSE;

        if (isset($team_coordinator_group_roles[$group_type]) && $team_coordinator_group_roles[$group_type]) {
          $group_team_coordinator_role = $team_coordinator_group_roles[$group_type];

          // get group member and they roles
          $group_member = $group->getMember($teamCoordinator);
          $group_member_roles = $group_member->getRoles();

          // check if team coordinator assigned to team coordinator role
          if (in_array($group_team_coordinator_role,
            array_keys($group_member_roles))) {
            $team_coordinator_group = TRUE;
          }
        }

        // if user is team coordinator in this group
        if ($team_coordinator_group) {
          // check if requested user is member of team coordinator group
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
      $_fields_options[$field_name] = $field_class->label();
    }

    return $_fields_options;
  }

}