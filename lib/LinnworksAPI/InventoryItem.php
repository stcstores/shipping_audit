<?php
namespace LinnworksAPI;

class InventoryItem {

    public function __construct($api, $stock_id = null)
    {
        $this -> api = $api;
        if ($stock_id != null) {
            $this -> stock_id = $stock_id;
        } else {
            $this -> get_stock_id();
        }
        $this -> json = null;
        $this -> inventory = null;
        $this -> sku = '';
        $this -> title = '';
        $this -> purchase_price = 0;
        $this -> retail_price = 0;
        $this -> barcode = '';
        $this -> category_id = '';
        $this -> category = '';
        $this -> depth = '';
        $this -> height = '';
        $this -> package_group_id = '';
        $this -> package_group = '';
        $this -> postage_service_id = '';
        $this -> postage_service = '';
        $this -> tax_rate = 0;
        $this -> variation_group_name = '';
        $this -> weight = 0;
        $this -> width = 0;
        $this -> quantity = 0;
        $this -> meta_data = '';
        $this -> extended_properties = new _ExtendedProperties($this);
    }

    private function __toString()
    {
        return $this -> sku . ': ' . $this -> title;
    }

    public function get_stock_id()
    {
        $this -> stock_id = createGUID();
    }

    public function create_sku()
    {
        $this -> sku = $this -> api -> get_new_sku();
    }

    public function get_create_inventoryItem_dict()
    {
        $inventoryItem = array();
        $inventoryItem['ItemNumber'] = $this -> sku;
        $inventoryItem['ItemTitle'] = $this -> title;
        $inventoryItem['BarcodeNumber'] = $this -> barcode;
        $inventoryItem['PurchasePrice'] = $this -> purchase_price;
        $inventoryItem['RetailPrice'] = $this -> retail_price;
        $inventoryItem['Quantity'] = $this -> quantity;
        $inventoryItem['TaxRate'] = $this -> tax_rate;
        $inventoryItem['StockItemId'] = $this -> stock_id;
        return $inventoryItem;
    }

    public function get_inventoryItem_dict()
    {
        $inventoryItem = array();
        $inventoryItem['ItemNumber'] = $this -> sku;
        $inventoryItem['ItemTitle'] = $this -> title;
        $inventoryItem['BarcodeNumber'] = $this -> barcode;
        $inventoryItem['PurchasePrice'] = $this -> purchase_price;
        $inventoryItem['RetailPrice'] = $this -> retail_price;
        $inventoryItem['Quantity'] = $this -> quantity;
        $inventoryItem['TaxRate'] = $this -> tax_rate;
        $inventoryItem['StockItemId'] = $this -> stock_id;
        $inventoryItem['VariationGroupName'] = $this -> variation_group_name;
        $inventoryItem['MetaData'] = $this -> meta_data;
        $inventoryItem['CategoryId'] = $this -> category_id;
        $inventoryItem['PackageGroupId'] = $this -> package_group_id;
        $inventoryItem['PostalServiceId'] = $this -> postage_service_id;
        $inventoryItem['Weight'] = $this -> weight;
        $inventoryItem['Width'] = $this -> width;
        $inventoryItem['Depth'] = $this -> depth;
        $inventoryItem['Height'] = $this -> height;
        return $inventoryItem;
    }

    public function create_item()
    {
        $inventoryItem = $this -> get_create_inventoryItem_dict();
        $request_url = $this -> api -> server . '/api/Inventory/AddInventoryItem';
        $data = array();
        $data['inventoryItem'] = json_encode($inventoryItem);

        return $this -> api -> request($request_url, $data);
    }

    public function update_item()
    {
        $inventoryItem = $this -> get_inventoryItem_dict();
        $request_url = $this -> api -> server . '/api/Inventory/UpdateInventoryItem';
        $data = array();
        $data['inventoryItem'] = json_encode($inventoryItem);
        return $this -> api -> request($request_url, $data);
    }

    public function update_all()
    {
        $this -> update_item();
        $this -> extended_properties -> update();
    }

    function load_extended_properties() {
        $this -> extended_properties -> load();
    }

    public function get_extended_properties_dict()
    {
        $properties = array();
        foreach ($this -> extended_properties as $prop) {
            if ($prop -> delete == false) {
                $properties[$prop -> name] = $prop -> value;
            }
        }
        return $properties;
    }

    public function get_extended_properties_list()
    {
        $properties = array();
        foreach ($this -> extended_properties as $prop) {
            if ($prop -> delete == false) {
                $new_prop = array();
                $new_prop['name'] = $prop -> name;
                $new_prop['value'] = $prop -> value;
                $new_prop['type'] = $prop -> type;
                $new_prop['guid'] = $prop -> guid;
            }
            $properties[] = $new_prop;
        }
        return $properties;
    }

    public function create_extended_property($name = '', $value = '', $property_type = 'Attribute')
    {
        $prop = _ExtendedProperty();
        $prop -> name = $name;
        $prop -> value = $value;
        $prop -> type = $property_type;
        $this -> extended_properties[] = prop;
    }

    public function add_imagefilepath($filepath)
    {
        $upload_response = $this -> api -> upload_image($filepath);
        $image_guid = $upload_response[0]['FileId'];
        $add_url = $this -> api -> server . '/api/Inventory/UploadImagesToInventoryItem';
        $add_data = array();
        $add_data['inventoryItemId'] = $this -> stock_id;
        $add_data['imageIds'] = json_encode([$image_guid]);
        $add_response = $this -> api -> request($add_url, $add_data);
        return $add_response;
    }
}
