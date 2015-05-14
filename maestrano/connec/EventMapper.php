<?php

/**
* Map Connec Event representation to/from vTiger Event
*/
class EventMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Event';
    $this->local_entity_name = 'EventManagement';
    $this->connec_resource_name = 'events';
    $this->connec_resource_endpoint = 'events';
  }

  // Return the Event local id
  protected function getId($event) {
    return $event->id;
  }

  // Return a local Event by id
  protected function loadModelById($local_id) {
    $event = CRMEntity::getInstance("EventManagement");
    $event->retrieve_entity_info($local_id, "EventManagement");
    vtlib_setup_modulevars("EventManagement", $event);
    $event->id = $local_id;
    $event->mode = 'edit';
    return $event;
  }

  // Map the Connec resource attributes onto the vTiger Event
  protected function mapConnecResourceToModel($event_hash, $event) {
    // Map hash attributes to Event
    if($this->is_set($event_hash['code'])) { $event->column_fields['code'] = $event_hash['code']; }
    if($this->is_set($event_hash['name'])) { $event->column_fields['name'] = $event_hash['name']; }
    if($this->is_set($event_hash['description'])) { $event->column_fields['description'] = $event_hash['description']; }
    if($this->is_set($event_hash['status'])) { $event->column_fields['status'] = $event_hash['status']; }
    if($this->is_set($event_hash['url'])) { $event->column_fields['url'] = $event_hash['url']; }
    if($this->is_set($event_hash['start_date'])) { $event->column_fields['start_date'] = $event_hash['start_date']; }
    if($this->is_set($event_hash['end_date'])) { $event->column_fields['end_date'] = $event_hash['end_date']; }
    if($this->is_set($event_hash['capacity'])) { $event->column_fields['capacity'] = $event_hash['capacity']; }
    if($this->is_set($event_hash['currency'])) { $event->column_fields['currency'] = $event_hash['currency']; }
  }

  // Map the vTiger Event to a Connec resource hash
  protected function mapModelToConnecResource($event) {
    $event_hash = array();

    // Map attributes
    if($this->is_set($event->column_fields['code'])) { $event_hash['code'] = $event->column_fields['code']; }
    if($this->is_set($event->column_fields['name'])) { $event_hash['name'] = $event->column_fields['name']; }
    if($this->is_set($event->column_fields['description'])) { $event_hash['description'] = $event->column_fields['description']; }
    if($this->is_set($event->column_fields['status'])) { $event_hash['status'] = $event->column_fields['status']; }
    if($this->is_set($event->column_fields['url'])) { $event_hash['url'] = $event->column_fields['url']; }
    if($this->is_set($event->column_fields['start_date'])) { $event_hash['start_date'] = $event->column_fields['start_date']; }
    if($this->is_set($event->column_fields['end_date'])) { $event_hash['end_date'] = $event->column_fields['end_date']; }
    if($this->is_set($event->column_fields['capacity'])) { $event_hash['capacity'] = $event->column_fields['capacity']; }
    if($this->is_set($event->column_fields['currency'])) { $event_hash['currency'] = $event->column_fields['currency']; }

    return $event_hash;
  }

  // Persist the vTiger Event
  protected function persistLocalModel($event, $event_hash) {
    $event->save("Events", $event->id, false);
  }
}