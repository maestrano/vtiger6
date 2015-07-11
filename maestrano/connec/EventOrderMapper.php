<?php

/**
* Map Connec EventOrder representation to a vTiger Contact-Ticket relationship
*/
class EventOrderMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'EventOrder';
    $this->local_entity_name = 'EventOrder';
    $this->connec_resource_name = 'event_orders';
    $this->connec_resource_endpoint = 'event_orders';
  }

  // No vTiger model
  protected function getId($event_order) {
    return null;
  }

  // Return a standard object
  protected function loadModelById($local_id) {
    return (object) array();
  }

  // Return a standard object
  public function matchLocalModel($organization_hash) {
    return (object) array();
  }

  // Map the Connec resource attributes onto the vTiger Contact/Ticket
  protected function mapConnecResourceToModel($event_order_hash, $event_order) {
    // Retrieve the Event
    if(!$this->is_set($event_order_hash['event_id'])) { return null; }
    $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($event_order_hash['event_id'], 'EVENT');
    $eventMapper = new EventMapper();
    $vtiger_event = $eventMapper->loadModelById($mno_id_map['app_entity_id']);
    if(is_null($vtiger_event)) { return null; }

    if(empty($event_order_hash['attendees'])) {
      // No attendees specified, use contact purchasing the ticket instead
      if(!$this->is_set($event_order_hash['person_id'])) { return null; }
      
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($event_order_hash['person_id'], 'PERSON');
      $contactMapper = new ContactMapper();
      $vtiger_contact = $contactMapper->loadModelById($mno_id_map['app_entity_id']);
      if(is_null($vtiger_contact)) { return null; }

      // Link Event / Contact
      $vtiger_contact->save_related_module('EventManagement', $vtiger_event->id, 'Contacts', $vtiger_contact->id);
      $vtiger_event->save_related_module('Contacts', $vtiger_contact->id, 'EventManagement', $vtiger_event->id);
    } else {
      // Map the list of attendees
      foreach ($event_order_hash['attendees'] as $attendee_hash) {
        // Contact attending the event
        if($this->is_set($attendee_hash['person_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($attendee_hash['person_id'], 'PERSON');
          $contactMapper = new ContactMapper();
          $vtiger_contact = $contactMapper->loadModelById($mno_id_map['app_entity_id']);
          if(is_null($vtiger_contact)) { continue; }
        }

        // Ticket type
        if($this->is_set($attendee_hash['event_ticket_id'])) {
          $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($attendee_hash['event_ticket_id'], 'TICKET');
          $ticketMapper = new TicketMapper();
          $vtiger_ticket = $ticketMapper->loadModelById($mno_id_map['app_entity_id']);
        }

        // Link Event / Ticket / Contact
        $vtiger_contact->save_related_module('EventManagement', $vtiger_event->id, 'Contacts', $vtiger_contact->id);
        if($vtiger_ticket) { $vtiger_contact->save_related_module('EventTicket', $vtiger_ticket->id, 'Contacts', $vtiger_contact->id); }
        $vtiger_event->save_related_module('Contacts', $vtiger_contact->id, 'EventManagement', $vtiger_event->id);
        if($vtiger_ticket) { $vtiger_event->save_related_module('Contacts', $vtiger_contact->id, 'EventTicket', $vtiger_ticket->id); }
      }
    }
  }

  // Do not push
  protected function mapModelToConnecResource($event_order) {
    return $array();
  }

  // Persisted when mapping
  protected function persistLocalModel($event_order, $event_order_hash) {

  }
}