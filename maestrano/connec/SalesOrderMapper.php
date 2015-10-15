<?php

/**
* Map Connec SalesOrder representation to/from vTiger SalesOrder
*/
class SalesOrderMapper extends TransactionMapper {
  private $sales_order_status_mapping = null;
  private $sales_order_status_mapping_reverse = null;

  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'SalesOrder';
    $this->local_entity_name = 'SalesOrder';
    $this->connec_resource_name = 'sales_orders';
    $this->connec_resource_endpoint = 'sales_orders';

    $this->sales_order_status_mapping = array('DRAFT' => 'Created', 'SUBMITTED' => 'Approved', 'AUTHORISED' => 'Approved', 'DELIVERED' => 'Delivered', 'CANCELLED' => 'Cancelled');
    $this->sales_order_status_mapping_reverse = array('Created' => 'DRAFT', 'Approved' => 'AUTHORISED', 'Delivered' => 'DELIVERED', 'Cancelled' => 'CANCELLED');
  }

  // Map the Connec resource attributes onto the vTiger SalesOrder
  protected function mapConnecResourceToModel($sales_order_hash, $sales_order) {
    parent::mapConnecResourceToModel($sales_order_hash, $sales_order);

    if($this->is_set($sales_order_hash['transaction_number'])) { $sales_order->column_fields['salesorder_no'] = $sales_order_hash['transaction_number']; }
    if($this->is_set($sales_order_hash['due_date'])) { $sales_order->column_fields['duedate'] = $this->format_date_to_php($sales_order_hash['due_date']); }

    // Map status
    $sales_order->column_fields['sostatus'] = $this->sales_order_status_mapping[$sales_order_hash['status']];
  }

  // Map the vTiger SalesOrder to a Connec resource hash
  protected function mapModelToConnecResource($sales_order) {
    $sales_order_hash = parent::mapModelToConnecResource($sales_order);

    // Map attributes
    $sales_order_hash['transaction_number'] = $sales_order->column_fields['salesorder_no'];
    if($this->is_set($sales_order->column_fields['duedate'])) { $sales_order_hash['due_date'] = $this->format_date_to_connec($sales_order->column_fields['duedate']); }
    $sales_order_hash['status'] = $this->sales_order_status_mapping_reverse[$sales_order->column_fields['sostatus']];

    return $sales_order_hash;
  }
}