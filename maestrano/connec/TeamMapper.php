<?php

/**
* Map Connec Opportunity representation to/from vTiger Group
*/
class TeamMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Team';
    $this->local_entity_name = 'Settings_Groups_Record_Model';
    $this->connec_resource_name = 'teams';
    $this->connec_resource_endpoint = 'teams';
  }

  // Return the Group local id
  protected function getId($group) {
    return $group->getId();
  }

  // Return a local Group by id
  protected function loadModelById($local_id) {
    $group = Settings_Groups_Record_Model::getInstanceMnoHook($local_id);
    return $group;
  }

  // Map the Connec resource attributes onto the vTiger Group
  protected function mapConnecResourceToModel($team_hash, $group) {
    // These fields are always updated
    $group->set('groupname', $team_hash['name']);
    $group->set('description', $team_hash['description']);

    if(json_encode($group)=="{}") {
      // team does not exist locally : we update the users list
      $mno_members_ids = $team_hash['members'];
      $local_members_ids = array();
      for($j=0;$j<count($mno_members_ids);$j++) {
        $user_hash = $mno_members_ids[$j];
        $user_mapper = new UserMapper();
        $user_model = $user_mapper->fetchConnecResource($user_hash['id']);
        $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($user_hash['id'], 'APPUSER', 'USERS');
        $member = "Users:" . $mno_id_map['app_entity_id'];
        array_push($local_members_ids, $member);
      }
      $group->set('group_members', $local_members_ids);
    }
    else {
      // team exists locally : the users list is not changed
      $group->set('groupid', $group->data['groupid']);
    }
  }

  // Map the vTiger User to a Connec User hash
  protected function mapModelToConnecResource($group) {
    $team_hash = array();

    // Map attributes
    $team_hash['name'] = $group->column_fields['name'];
    $team_hash['description'] = $group->column_fields['description'];

    // Find mno id if team already exists in vTiger
    $team_mno_id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($this->getId($group), $this->local_entity_name);
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
  protected function persistLocalModel($group, $resource_hash) {
    $group->save(false);
  }
}