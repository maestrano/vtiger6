<?php

/**
* Map Connec Ticket representation to/from vTiger Ticket
*/
class TicketMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Ticket';
    $this->local_entity_name = 'Tickets';
    $this->connec_resource_name = 'events/tickets';
    $this->connec_resource_endpoint = 'events/tickets';
  }

  // Return the Ticket local id
  protected function getId($ticket) {
    return $ticket->id;
  }

  // Return a local Ticket by id
  protected function loadModelById($local_id) {
    $ticket = CRMEntity::getInstance("Tickets");
    $ticket->retrieve_entity_info($local_id, "Tickets");
    vtlib_setup_modulevars("Tickets", $ticket);
    $ticket->id = $local_id;
    $ticket->mode = 'edit';
    return $ticket;
  }

  // Map the Connec resource attributes onto the vTiger Ticket
  protected function mapConnecResourceToModel($ticket_hash, $ticket) {
    // Map hash attributes to Ticket
    if($this->is_set($ticket_hash['name'])) { $ticket->column_fields['name'] = $ticket_hash['name']; }
    if($this->is_set($ticket_hash['description'])) { $ticket->column_fields['description'] = $ticket_hash['description']; }
    if($this->is_set($ticket_hash['quantity_total'])) { $ticket->column_fields['quantity_total'] = $ticket_hash['quantity_total']; }
    if($this->is_set($ticket_hash['quantity_sold'])) { $ticket->column_fields['quantity_sold'] = $ticket_hash['quantity_sold']; }
    if($this->is_set($ticket_hash['minimum_quantity'])) { $ticket->column_fields['minimum_quantity'] = $ticket_hash['minimum_quantity']; }
    if($this->is_set($ticket_hash['maximum_quantity'])) { $ticket->column_fields['maximum_quantity'] = $ticket_hash['maximum_quantity']; }
    if($this->is_set($ticket_hash['sales_start'])) { $ticket->column_fields['sales_start'] = $ticket_hash['sales_start']; }
    if($this->is_set($ticket_hash['sales_end'])) { $ticket->column_fields['sales_end'] = $ticket_hash['sales_end']; }
    
    if($this->is_set($ticket_hash['cost'])) {
      $ticket->column_fields['total_amount'] = $ticket_hash['cost']['total_amount'];
      $ticket->column_fields['net_amount'] = $ticket_hash['cost']['net_amount'];
      $ticket->column_fields['tax_amount'] = $ticket_hash['cost']['tax_amount'];
      $ticket->column_fields['tax_rate'] = $ticket_hash['cost']['tax_rate'];
    }
  }

  // Map the vTiger Ticket to a Connec resource hash
  protected function mapModelToConnecResource($ticket) {
    $ticket_hash = array();

    // Map attributes
    if($this->is_set($ticket->column_fields['name'])) { $ticket_hash['name'] = $ticket->column_fields['name']; }
    if($this->is_set($ticket->column_fields['description'])) { $ticket_hash['description'] = $ticket->column_fields['description']; }
    if($this->is_set($ticket->column_fields['quantity_total'])) { $ticket_hash['quantity_total'] = $ticket->column_fields['quantity_total']; }
    if($this->is_set($ticket->column_fields['quantity_sold'])) { $ticket_hash['quantity_sold'] = $ticket->column_fields['quantity_sold']; }
    if($this->is_set($ticket->column_fields['minimum_quantity'])) { $ticket_hash['minimum_quantity'] = $ticket->column_fields['minimum_quantity']; }
    if($this->is_set($ticket->column_fields['maximum_quantity'])) { $ticket_hash['maximum_quantity'] = $ticket->column_fields['maximum_quantity']; }
    if($this->is_set($ticket->column_fields['sales_start'])) { $ticket_hash['sales_start'] = $ticket->column_fields['sales_start']; }
    if($this->is_set($ticket->column_fields['sales_end'])) { $ticket_hash['sales_end'] = $ticket->column_fields['sales_end']; }

    $ticket_hash['cost'] = array();
    if($this->is_set($ticket->column_fields['total_amount'])) { $ticket_hash['cost']['total_amount'] = $ticket->column_fields['total_amount']; }
    if($this->is_set($ticket->column_fields['net_amount'])) { $ticket_hash['cost']['net_amount'] = $ticket->column_fields['net_amount']; }
    if($this->is_set($ticket->column_fields['tax_amount'])) { $ticket_hash['cost']['tax_amount'] = $ticket->column_fields['tax_amount']; }
    if($this->is_set($ticket->column_fields['tax_rate'])) { $ticket_hash['cost']['tax_rate'] = $ticket->column_fields['tax_rate']; }

    return $ticket_hash;
  }

  // Persist the vTiger Ticket
  protected function persistLocalModel($ticket, $ticket_hash) {
    $ticket->save("Tickets", $ticket->id, false);
  }
}