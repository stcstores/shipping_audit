<?php
namespace LinnworksAPI;


class ExtendedProperties {

    public function __construct($item, $load = true)
    {
        $this -> item = $item;
        $this -> extended_properties = array();
        if ($load == true) {
            $this -> load();
        }
    }

    //def __getitem__key):
    //    if type(key) == int:
    //        return $this -> extended_properties[key]
    //    elif type(key) == str:
    //        for prop in $this -> extended_properties:
    //            if prop.name == key:
    //                return prop
    //
    //def __iter__():
    //    for prop in $this -> extended_properties:
    //        yield prop
    //
    //def __len__():
    //    return len($this -> extended_properties)

    public function get_property_by_name($name)
    {
        foreach ($this->extended_properties as $property) {
            if ($property -> name == $name) {
                return $property;
            }
        }
        return null;
    }

    public function append($extended_property)
    {
        $this -> extended_properties[$extended_property -> value] = $extended_property;
    }

    public function load()
    {
        $response = $this -> item -> api -> get_inventory_item_extended_properties($this -> item -> stock_id);
        foreach ($response as $property) {
            $this -> add($json = $property);
        }
    }

    public function add($json)
    {
        $this -> extended_properties[] = new _ExtendedProperty($this -> item, $json = $json);
    }

    public function create($name = '', $value = '', $property_type = 'Attribute')
    {
        $prop = $_ExtendedProperty($this -> item);
        $prop -> name = $name;
        $prop -> value = $value;
        $prop -> type = $property_type;
        $this -> extended_properties[] = $prop;
        return $prop;
    }

    public function create_on_server($name = '', $value = '', $property_type = 'Attribute')
    {
        $prop = $this -> create($name = $name, $value = $value, $property_type = $property_type);
        $prop -> create();
    }

    public function upload_new()
    {
        $new_properties = array();
        foreach ($extended_properteis as $prop) {
            if ($prop -> on_server == false) {
                $new_properties[] = $prop;
            }
        }
        if (count($new_properties) > 0) {
            $item_arrays = array();
            foreach ($new_properties as $prop) {
                $item_arrays[] = $prop -> get_json();
            }
            $data = array();
            $data['inventoryItemExtendedProperties'] = json_encode($item_arrays);
            $api = $this -> item -> api;
            $url = $api -> server . '/api/Inventory/CreateInventoryItemExtendedProperties';
            $response = $api -> request($url, $data);
            return $response;
        }
    }

    public function update_existing()
    {
        $new_properties = array();
        foreach ($extended_properties as $prop) {
            if ($prop -> on_server == true) {
                $new_properties[] = $prop;
            }
        }
        if (count($new_properties) > 0) {
            $item_arrays = array();
            foreach ($new_properties as $prop) {
                $item_arrays[] = $prop -> get_json();
            }
            $data = array();
            $data['inventoryItemExtendedProperties'] = json_encode($item_arrays);

            $api = $this -> item -> api;
            $url = $api -> server . '/api/Inventory/UpdateInventoryItemExtendedProperties';
            $response = $api -> $request($url, $data);
            return $response;
        }
    }

    public function remove_deleted()
    {
        $api = $this -> item -> api;
        $items_to_delete = array();
        foreach ($this -> extended_properties as $prop) {
            if ($prop -> delete == true) {
                $items_to_delete[] = $prop -> guid;
            }
        }
        if (count($items_to_delete) > 0) {
            $data = array();
            $data['inventoryItemId'] = $this -> item -> stock_id;
            $data['inventoryItemExtendedPropertyIds'] = json_encode($items_to_delete);
            $url = $api -> server . '/api/Inventory/DeleteInventoryItemExtendedProperties';
            $response = $api -> request($url, $data);
            return $response;
        }
    }

    public function update()
    {
        $this -> upload_new();
        $this -> update_existing();
        $this -> remove_deleted();
    }
}
