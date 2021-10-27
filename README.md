# Group user field access drupal 9 module

## Settings page
A separated settings at Administration / Groups / Group Settings / User field access
(/admin/group/settings/user-field-access)

With options 
 - Team coordinator editable user fields: a set of checkboxes, one for each user
account custom field (/admin/config/people/accounts/fields), default
unchecked for all; applicable for all group types
- Team coordinator group role: a list of all group types names + standard select
  for each with appropriate group roles, default no selected

##Functionality
- The module extends the functionality of the Group module
  (https://www.drupal.org/project/group), adjusts permissions for group users only and
  does not have any influence or dependency on other modules, components or global
  permissions (if not specified here otherwise).
- The "team coordinator" means a user who is a member in a controlled group, and is
  assigned a team coordinator group role in this group, specified for this group type (as
  module settings for each group type above).
- The team coordinator can access/save user profile dialog (/user/[uid]/edit) of all users
  assigned to the controlled group in any group role or without a group role, ie.
  members only. (No global permission for user administration required.)
- In the user dialog, the team coordinator:
- can see only (as disabled): Username, Roles
- can edit: Email address, all user custom fields specified (checked) as editable
  for the team coordinator in the module settings
- cannot see/edit: all other user custom fields (not specified as editable), and all
  other standard user profile components (Password block, Status, Roles,
  Picture, Language settings, Contact settings, Locale settings)
- The components and fields with fixed behaviour (Username, Roles, Email, Password,
  Status, Picture, Language settings, Contact settings, Local settings, etc.) must be
  adjustable in the code as a constant.

##Testing:

 - Test saving options on page /admin/group/settings/user-field-access
 - Testing with creating groups and sets to they team coordinator role.
 - Add users to group and test with team coordinator role edit allowed fields. Then Update settings and test again.
 - Test editing user with administartor role