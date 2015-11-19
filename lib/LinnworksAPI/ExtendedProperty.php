<?php
namespace LinnworksAPI;

class ExtendedProperty {


    public function __construct($item, $json = null)
    {
        $this -> item = $item;
        $this -> type = '';
        $this -> value = '';
        $this -> name = '';
        $this -> on_server = false;
        $this -> delete = false;

        if ($json != null) {
            $this -> load_from_json($json);
            $this -> on_server = true;
        } else {
            $this -> guid = createGUID();
            $this -> on_server = false;
        }
    }

    public function load_from_json($json)
    {
        $this -> type = $json['PropertyType'];
        $this -> value = $json['PropertyValue'];
        $this -> name = $json['ProperyName'];
        $this -> guid = $json['pkRowId'];
    }

    public function get_json()
    {
        $data = array();
        $data['pkRowId'] = $this -> guid;
        $data['fkStockItemId'] = $this -> item -> stock_id;
        $data['ProperyName'] = $this -> name;
        $data['PropertyValue'] = $this -> value;
        $data['PropertyType'] = $this -> type;
        return $data;
    }

    public function update()
    {
        $api = $this -> item -> api;
        $url = $api -> server . '/api/Inventory/UpdateInventoryItemExtendedProperties';
        $data = array();
        $data['inventoryItemExtendedProperties'] = json_encode([$this -> get_json()]);
        $response = $api -> request($url, $data);
        return $response;
    }

    public function create()
    {
        $api = $this -> item -> api;
        $url = $api -> server . '/api/Inventory/CreateInventoryItemExtendedProperties';
        $data = array();
        $data['inventoryItemExtendedProperties'] -> json_encode([$this -> get_json()]);
        $response = $api -> request($url, $data);
        return $response;
    }

    public function remove()
    {
        $this -> delete = true;
    }

    public function delete_from_server()
    {
        $api = $this -> item -> api;
        $data = array();
        $data['inventoryItemId'] = $this -> item -> stock_id;
        $data['inventoryItemExtendedPropertyIds'] -> json_encode([$this -> guid]);

        $url = $api -> server . '/api/Inventory/DeleteInventoryItemExtendedProperties';
        $response = $api -> request($url, $data);
        return $response;
    }
}
