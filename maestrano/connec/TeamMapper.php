<?php

/**
* Map Connec Opportunity representation to/from vTiger Group
*/
class TeamMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Team';
    $this->local_entity_name = 'Groups';
    $this->connec_resource_name = 'teams';
    $this->connec_resource_endpoint = 'teams';
  }

  // Return the Group local id
  protected function getId($group) {
    return $group->getId();
  }

  // Return a local Group by id
  // TODO: TO BE TESTED
  protected function loadModelById($local_id) {
    $group = CRMEntity::getInstance("Groups");
    $group->retrieve_entity_info($local_id, "Groups");
    vtlib_setup_modulevars("Groups", $group);
    $group->id = $local_id;
    $group->mode = 'edit';
    return $group;
  }

  // Map the Connec resource attributes onto the vTiger Group
  protected function mapConnecResourceToModel($team_hash, $group) {
    $group->column_fields['name'] = $team_hash['name'];
    $group->column_fields['description'] = $team_hash['description'];

    // Map members
    // Retrieve the list of users in vTiger team
    $users_from_connec = $team_hash['members'];

    $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($team_hash['id'], 'Groups');
    if($mno_id_map) {
      error_log("----------------");
      error_log(json_encode($mno_id_map));
      error_log("----------------");
      $local_group = $this->loadModelById($mno_id_map['local_id']);
    }

  }

  // Map the vTiger User to a Connec User hash
  protected function mapModelToConnecResource($group) {
    $team_hash = array();

    // Map attributes
    $team_hash['name'] = $group->column_fields['name'];
    $team_hash['description'] = $group->column_fields['description'];

    // Find mno id if team already exists in vTiger
    $team_mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($this->getId($group), 'Groups');
    if($team_mno_id_map) {
      $mno_team_id =  $team_mno_id_map['mno_entity_guid'];
      $team_hash['id'] = $mno_team_id;
    }

    // Map Members (users) by Id
    if($this->is_set($group->column_fields['member_users_ids'])) {

      $team_hash['members'] = array();
      $user_local_ids = $group->column_fields['member_users_ids'];

      for($j=0;$j<count($user_local_ids);$j++) {
        $user_id = $user_local_ids[$j];
        $mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($user_id, 'USERS');

        if($mno_id_map) {
          $user_hash = array();
          $user_hash['id'] = $mno_id_map['mno_entity_guid'];
          array_push($team_hash['members'], $user_hash);
        }
      }

    }

    return $team_hash;
  }

  // Persist the vTiger User
  // TODO TO BE TESTED
  protected function persistLocalModel($group, $resource_hash) {
    $group->save("Groups", $group->id, false);
  }
}