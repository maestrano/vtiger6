<?php

/**
* Map Connec Ticket representation to/from vTiger Ticket
*/
class TicketMapper extends BaseMapper {
  private $event = null;

  public function __construct($event=null) {
    parent::__construct();

    $this->connec_entity_name = 'Ticket';
    $this->local_entity_name = 'EventTicket';
    $this->connec_resource_name = 'events/tickets';
    $this->connec_resource_endpoint = 'events/tickets';

    $this->event = $event;
  }

  // Return the Ticket local id
  protected function getId($ticket) {
    return $ticket->id;
  }

  // Return a local Ticket by id
  public function loadModelById($local_id) {
    $ticket = CRMEntity::getInstance("EventTicket");
    $ticket->retrieve_entity_info($local_id, "EventTicket");
    vtlib_setup_modulevars("EventTicket", $ticket);
    $ticket->id = $local_id;
    $ticket->mode = 'edit';
    return $ticket;
  }

  // Map the Connec resource attributes onto the vTiger Ticket
  protected function mapConnecResourceToModel($ticket_hash, $ticket) {
    // Map hash attributes to Ticket
    if($this->is_set($ticket_hash['name'])) { $ticket->column_fields['ticket_name'] = $ticket_hash['name']; }
    if($this->is_set($ticket_hash['description'])) { $ticket->column_fields['description'] = $ticket_hash['description']; }
    if($this->is_set($ticket_hash['quantity_total'])) { $ticket->column_fields['quantity_total'] = $ticket_hash['quantity_total']; }
    if($this->is_set($ticket_hash['quantity_sold'])) { $ticket->column_fields['quantity_sold'] = $ticket_hash['quantity_sold']; }
    if($this->is_set($ticket_hash['quantity_sold']) && $this->is_set($ticket_hash['quantity_total'])) {
      $ticket->column_fields['quantity_available'] = $ticket_hash['quantity_total'] - $ticket_hash['quantity_sold'];
    }
    if($this->is_set($ticket_hash['minimum_quantity'])) { $ticket->column_fields['minimum_quantity'] = $ticket_hash['minimum_quantity']; }
    if($this->is_set($ticket_hash['maximum_quantity'])) { $ticket->column_fields['maximum_quantity'] = $ticket_hash['maximum_quantity']; }
    if($this->is_set($ticket_hash['sales_start'])) { $ticket->column_fields['sales_start'] = $ticket_hash['sales_start']; }
    if($this->is_set($ticket_hash['sales_end'])) { $ticket->column_fields['sales_end'] = $ticket_hash['sales_end']; }
    
    if($this->is_set($ticket_hash['cost']) && $this->is_set($ticket_hash['cost']['total_amount'])) {
      $ticket->column_fields['ticket_price'] = $ticket_hash['cost']['total_amount'];
    }

    // Parent Event
    $ticket->column_fields['event_management'] = $this->event->id;
  }

  // Map the vTiger Ticket to a Connec resource hash
  protected function mapModelToConnecResource($ticket) {
    $ticket_hash = array();

    // Map attributes
    $ticket_hash['name'] = $ticket->column_fields['ticket_name'];
    $ticket_hash['description'] = $ticket->column_fields['description'];
    $ticket_hash['quantity_total'] = $ticket->column_fields['quantity_total'];
    $ticket_hash['quantity_sold'] = $ticket->column_fields['quantity_sold'];
    $ticket_hash['minimum_quantity'] = $ticket->column_fields['minimum_quantity'];
    $ticket_hash['maximum_quantity'] = $ticket->column_fields['maximum_quantity'];
    $ticket_hash['sales_start'] = $ticket->column_fields['sales_start'];
    $ticket_hash['sales_end'] = $ticket->column_fields['sales_end'];
    $ticket_hash['cost'] = array('total_amount' => $ticket->column_fields['ticket_price']);

    return $ticket_hash;
  }

  // Persist the vTiger Ticket
  protected function persistLocalModel($ticket, $ticket_hash) {
    $ticket->save("EventTicket", $ticket->id, false);
  }
}