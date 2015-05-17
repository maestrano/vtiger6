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

    $this->event_status_mapping = array('DRAFT' => 'Draft', 'LIVE' => 'Live', 'COMPLETED' => 'Closed');
    $this->event_status_mapping_reverse = array('Draft' => 'DRAFT', 'Live' => 'LIVE', 'Closed' => 'COMPLETED');
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
    if($this->is_set($event_hash['code'])) { $event->column_fields['event_number'] = $event_hash['code']; }
    if($this->is_set($event_hash['name'])) { $event->column_fields['event_name'] = $event_hash['name']; }
    if($this->is_set($event_hash['description'])) { $event->column_fields['description'] = $event_hash['description']; }
    if($this->is_set($event_hash['url'])) { $event->column_fields['url'] = $event_hash['url']; }
    if($this->is_set($event_hash['start_date'])) { $event->column_fields['start_date'] = $this->format_date_to_php($event_hash['start_date']); }
    if($this->is_set($event_hash['end_date'])) { $event->column_fields['end_date'] = $this->format_date_to_php($event_hash['end_date']); }
    if($this->is_set($event_hash['capacity'])) { $event->column_fields['capacity'] = $event_hash['capacity']; }
    if($this->is_set($event_hash['currency'])) { $event->column_fields['currency'] = $event_hash['currency']; }
    
    if($this->is_set($event_hash['status'])) { $event->column_fields['event_status'] = $this->event_status_mapping[$event_hash['status']]; }
  }

  // Map the vTiger Event to a Connec resource hash
  protected function mapModelToConnecResource($event) {
    $event_hash = array();

    // Map attributes
    if($this->is_set($event->column_fields['event_number'])) { $event_hash['code'] = $event->column_fields['event_number']; }
    if($this->is_set($event->column_fields['event_name'])) { $event_hash['name'] = $event->column_fields['event_name']; }
    if($this->is_set($event->column_fields['description'])) { $event_hash['description'] = $event->column_fields['description']; }
    if($this->is_set($event->column_fields['event_status'])) { $event_hash['status'] = $event->column_fields['event_status']; }
    if($this->is_set($event->column_fields['url'])) { $event_hash['url'] = $event->column_fields['url']; }
    if($this->is_set($event->column_fields['start_date'])) { $event_hash['start_date'] = $this->format_date_to_connec($event->column_fields['start_date']); }
    if($this->is_set($event->column_fields['end_date'])) { $event_hash['end_date'] = $this->format_date_to_connec($event->column_fields['end_date']); }
    if($this->is_set($event->column_fields['capacity'])) { $event_hash['capacity'] = $event->column_fields['capacity']; }
    if($this->is_set($event->column_fields['currency'])) { $event_hash['currency'] = $event->column_fields['currency']; }

    if($this->is_set($event->column_fields['event_status'])) { $event_hash['status'] = $this->event_status_mapping_reverse[$event->column_fields['event_status']]; }

    return $event_hash;
  }

  // Persist the vTiger Event
  protected function persistLocalModel($event, $event_hash) {
    $event->save("EventManagement", $event->id, false);

    // Persist Event Tickets
    foreach ($event_hash['ticket_classes'] as $ticket_hash) {
      $ticketMapper = new TicketMapper($event);
      $ticketMapper->saveConnecResource($ticket_hash);
    }
  }
}