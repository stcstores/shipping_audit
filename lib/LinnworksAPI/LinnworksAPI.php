<?php
namespace LinnworksAPI;

set_time_limit(15000);

class LinnworksAPI
{


    public function __construct($username, $password)
    {
        $this -> username = $username;
        $this -> password = $password;
        $this -> userID = null;
        $this -> server = null;

        $this -> curl = $this -> curlSetup();

        if (isset($_SESSION['token'])) {
            $this -> token = $_SESSION['token'];
            $this -> server = $_SESSION['server'];
        } else {
            $this -> get_token();
        }
    }

    public function getCurl()
    {
        if (!(is_resource($this->curl))) {
            $this->curl = $this->curlSetup();
        }
        return $this->curl;
    }

    public function curlSetup()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt(
            $curl,
            CURLOPT_CAINFO,
            dirname(__FILE__) . '/thawtePrimaryRootCA.crt'
        );
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        return $curl;
    }

    private function rawRequest($requestURL, $data)
    {
        $curl = $this->getCurl();
        $datastring = http_build_query($data);
        curl_setopt($curl, CURLOPT_URL, $requestURL);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);
        $information = curl_getinfo($curl);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $responseJson = json_decode($body, true);
        return $responseJson;
    }

    public function make_request($url, $data)
    {
        $requestURL = $url . '?token=' . $this->token;
        return $this -> rawRequest($requestURL, $data);
    }

    public function request($url, $data = null)
    {
        if ($data == null) {
            $data = array();
        }
        //$data['token'] = $this -> token;
        return $this -> make_request($url, $data);
    }

    public function get_token()
    {
        $loginURL = 'https://api.linnworks.net/api/Auth/Multilogin';
        $authURL = 'https://api.linnworks.net/api/Auth/Authorize';
        $data = array('userName' => $this -> username, 'password' => $this -> password);
        $multiLogin = $this -> rawRequest($loginURL, $data);
        $this -> userID = $multiLogin[0]['Id'];
        $data['userId'] = $this -> userID;
        $authorise = $this -> rawRequest($authURL, $data);
        $_SESSION['token'] = $authorise['Token'];
        $this -> token = $authorise['Token'];
        $_SESSION['server'] = $authorise['Server'];
        $this -> server = $authorise['Server'];
        return true;
    }

    public function get_category_info()
    {
        $url = $this -> server . '/api/Inventory/GetCategories';
        $response = $this -> request($url);
        $categories = array();
        foreach ($response as $category) {
            $newCategory = array();
            $newCategory['name'] = $category['CategoryName'];
            $newCategory['id'] = $category['CategoryId'];
            $categories[] = $newCategory;
        }

        return $categories;
    }

    public function get_category_names()
    {
        $cateogryNames = array();
        $categoryInfo = $this -> get_category_info();
        foreach ($categoryInfo as $category) {
            $cateogryNames[] = $category['name'];
        }
        return $cateogryNames;
    }

    public function get_category_ids()
    {
        $cateogryIDs = array();
        $categoryInfo = $this -> get_category_info();
        foreach ($categoryInfo as $category) {
            $cateogryIDs[] = $category['id'];
        }
        return $cateogryIDs;
    }

    public function get_packaging_group_info()
    {
        $url = $this -> server . '/api/Inventory/GetPackageGroups';
        $response = $this -> request($url);
        $packagingGroups = array();
        foreach ($response as $group) {
            $newGroup = array();
            $newGroup['name'] = $group['Key'];
            $newGroup['id'] = $group['Value'];
            $packagingGroups[] = $newGroup;
        }

        return $packagingGroups;
    }

    public function get_packaging_group_names()
    {
        $packaging_group_names = array();
        foreach ($this -> get_packaging_group_info() as $group) {
            $packaging_group_names[] = $group['name'];
        }
        return $packaging_group_names;
    }

    public function get_shipping_method_info()
    {
        $url = $this -> server . '/api/Orders/GetShippingMethods';
        $response = $this -> request($url);
        $shippingMethods = array();
        foreach ($response as $service) {
            foreach ($service['PostalServices'] as $method) {
                $newMethod = array();
                $newMethod['vendor'] = $method['Vendor'];
                $newMethod['id'] = $method['pkPostalServiceId'];
                $newMethod['trackingRequired'] = $method['TrackingNumberRequired'];
                $newMethod['name'] = $method['PostalServiceName'];
                $shippingMethods[] = $newMethod;
            }
        }

        return $shippingMethods;
    }

    public function get_shipping_method_names()
    {
        $shipping_group_names = array();
        foreach ($this -> get_shipping_method_info() as $group) {
            $shipping_group_names[] = $group['name'];
        }
        return $shipping_group_names;
    }

    public function get_location_info()
    {
        $url = $this -> server . '/api/Inventory/GetStockLocations';
        $response = $this -> request($url);
        $locations = array();
        foreach ($response as $location) {
            $newLocation = array();
            $newLocation['name'] = $location['LocationName'];
            $newLocation['id'] = $location['StockLocationId'];
            $locations[] = $newLocation;
        }
        return $locations;
    }

    public function get_location_names()
    {
        $locations = array();
        foreach ($this -> get_location_info() as $location) {
            $locations[] = $location['name'];
        }
        return $locations;
    }

    public function get_location_ids()
    {
        $locations = array();
        foreach ($this -> get_location_info() as $location) {
            $locations[] = $location['id'];
        }
        return $locations;
    }

    public function get_postage_service_info()
    {
        $url = $this -> server . '/api/PostalServices/GetPostalServices';
        $response = $this -> request($url);
        $locations = array();
        foreach ($response as $postage_service) {
            $newLocation = array();
            $newLocation['name'] = $postage_service['PostalServiceName'];
            $newLocation['id'] = $postage_service['pkPostalServiceId'];
            $locations[] = $newLocation;
        }
        return $locations;
    }

    public function get_postage_service_names()
    {
        $postage_services = array();
        foreach ($this -> get_postage_service_info() as $postage_service) {
            $postage_services[] = $postage_service['name'];
        }
        sort($postage_services);
        return $postage_services;
    }

    public function get_postage_service_ids()
    {
        $postage_services = array();
        foreach ($this -> get_postage_service_info() as $postage_service) {
            $postage_services[] = $postage_service['id'];
        }
        return $postage_services;
    }

    public function get_channels()
    {
        $url = $this -> server . '/api/Inventory/GetChannels';
        $response = $this -> request($url);
        $channels = array();
        foreach ($response as $channel) {
            $channels[] = $channel['Source'] . ' ' . $channel['SubSource'];
        }
        return $channels;
    }

    public function get_inventory_views()
    {
        $url = $this -> server . '/api/Inventory/GetInventoryViews';
        $response = $this -> request($url);
        $response_json = json_decode($response);
        return $response_json;
    }

    public function get_new_inventory_view()
    {
        $url = $this -> server . '/api/Inventory/GetNewInventoryView';
        $response = $this -> request($url);
        return $response;
    }

    public function get_inventory_column_types()
    {
        $url = $this -> server . '/api/Inventory/GetInventoryColumnTypes';
        $response = $this -> request($url);
        return $response;
    }

    public function get_inventory_items($start = 0, $count = 1, $view = null)
    {
        if ($view == null) {
            $view = $this -> get_new_inventory_view();
        }

        $url = $this -> server . '/api/Inventory/GetInventoryItems';
        $view_json = json_encode($view);
        $locations = json_encode($this -> get_location_ids());
        $data = array();
        $data['view'] = $view_json;
        $data['stockLocationIds'] = $locations;
        $data['startIndex'] = $start;
        $data['itemsCount'] = $count;
        //echo "<br /><br />";
        //print_r($data);
        echo "<br /><br />";
        $response = $this -> request($url, $data);
        return $response;
    }

    public function get_item_count()
    {
        $request = $this -> get_inventory_items($start = 0, $count = 1, $view = null);
        $item_count = $request['TotalItems'];
        return $item_count;
    }

    public function get_inventory_item_by_id($stock_id, $inventory_item = true)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemById';
        $data = array();
        $data['id'] = $stock_id;
        $response = $this -> request($url, $data);
        if ($inventory_item != true) {
            return $response;
        } else {
            $item = new InventoryItem($this, $stock_id);
            $item -> sku = $response['ItemNumber'];
            $item -> title = $response['ItemTitle'];
            $item -> purchase_price = $response['PurchasePrice'];
            $item -> retail_price = $response['RetailPrice'];
            $item -> barcode = $response['BarcodeNumber'];
            $item -> category_id = $response['CategoryId'];
            $item -> depth = $response['Depth'];
            $item -> height = $response['Height'];
            $item -> package_group_id = $response['PackageGroupId'];
            $item -> postage_service_id = $response['PostalServiceId'];
            $item -> tax_rate = $response['TaxRate'];
            $item -> variation_group_name = $response['VariationGroupName'];
            $item -> weight = $response['Weight'];
            $item -> width = $response['Width'];
            $item -> meta_data = $response['MetaData'];
            $item -> quantity = $this -> get_stock_level_by_id($stock_id);

            foreach ($this -> get_category_info() as $category) {
                if ($category['id'] == $item -> category_id) {
                    $item -> category = $category['name'];
                }
            }

            foreach ($this -> get_packaging_group_info() as $package_group) {
                if ($package_group['id'] == $item -> package_group_id) {
                    $item -> package_group = $package_group['name'];
                }
            }

            foreach ($this -> get_shipping_method_info() as $postage_service) {
                if ($postage_service['id'] == $item -> postage_service) {
                    $item -> postage_service = $postage_service['name'];
                }
            }
            return $item;
        }
    }

    public function get_extended_property_names()
    {
        $url = $this -> server . '/api/Inventory/GetExtendedPropertyNames';
        $response = $this -> request($url);
        return $response;
    }

    public function get_inventory_item_extended_properties($stock_id)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemExtendedProperties';
        $data = array();
        $data['inventoryItemId'] = $stock_id;
        $response = $this -> request($url, $data);
        return $response;
    }

    public function get_new_sku()
    {
        $url = $this -> server . '/api/Stock/GetNewSKU';
        $response = $this -> request($url);
        return $response;
    }

    public function sku_exists($sku)
    {
        $url = $this -> server . '/api/Stock/SKUExists';
        $data = array();
        $data['SKU'] = $sku;
        $response = $this->request($url, $data);
        return $response;
    }

    public function upload_image($data)
    {
        $url = $this -> server . '/api/Uploader/UploadFile?type=Image&expiredInHours=24&token=' . $this -> token;
        $curl = $this->curl;
        $headers = array(
            'Content-Type: multipart/form-data',
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        //print_r($header);
        //echo "<br />";
        //echo "<br />";
        //print_r($body);
        //echo "<br />";
        //echo "<br />";
        //echo "<br />";
        //echo "<br />";
        $responseJson = json_decode($body, true);
        return $responseJson;
    }

    public function assign_images($productGuid, $imageGuidArray)
    {
        $url = $this->server . '/api/Inventory/UploadImagesToInventoryItem';
        $data = array();
        $data['inventoryItemId'] = $productGuid;
        $data['imageIds'] = json_encode($imageGuidArray);
        echo "<br />";
        //print_r($data);
        echo "<br />";
        $response = $this->request($url, $data);
        return $response;
    }

    public function create_variation_group($parent_title, $variation_guids, $parent_guid = null, $parent_sku = null)
    {
        if ($parent_guid == null) {
            $parent_guid = createGUID();
        }
        if ($parent_sku == null) {
            $parent_sku = $this -> get_new_sku();
        }
        $url = $this -> server . '/api/Stock/CreateVariationGroup';
        $template = array();
        $template['ParentSKU'] = $parent_sku;
        $template['VariationGroupName'] = $parent_title;
        $template['ParentStockItemId'] = $parent_guid;
        $template['VariationItemIds'] = $variation_guids;
        $data = array();
        $data['template'] = json_encode($template);
        $response = $this -> request($url, $data);
        if ($response == '') {
            return true;
        } else {
            return $response;
        }
    }

    public function get_variation_group_id_by_SKU($sku)
    {
        $url = $this -> server . '/api/Stock/SearchVariationGroups';
        $data = array();
        $data['searchText'] = $sku;
        $data['searchType'] = 'ParentSKU';
        $data['entriesPerPage'] = '100';
        $data['pageNumber'] = 1;
        $response = $this -> request($url, $data);
        //print_r($response);
        if (isset($response['Data'][0])) {
            return $response['Data'][0]['pkVariationItemId'];
        } else {
            return null;
        }
    }

    public function get_variation_group_inventory_item_by_SKU($sku)
    {
        $guid = $this -> get_variation_group_id_by_SKU($sku);
        $item = $this -> get_inventory_item_by_id($guid);
        return $item;
    }

    public function get_inventory_item_id_by_SKU($sku)
    {
        $view = $this -> get_new_inventory_view();
        //print_r($view);
        $view['Columns'] = array();
        $filter = array();
        $filter['Value'] = $sku;
        $filter['Field'] = 'String';
        $filter['FilterName'] = 'SKU';
        $filter['FilterNameExact'] = '';
        $filter['Condition'] = 'Equals';
        $view['Filters'] = [$filter];
        echo "<br /><br />";
        //print_r($view);
        $response = $this -> get_inventory_items(0, 1, $view = $view);
        //print_r($response);
        if (array_key_exists(0, $response['Items'])) {
            $stock_id = $response['Items'][0]['Id'];
            return $stock_id;
        } else {
            return null;
        }
    }

    public function get_inventory_item_by_SKU($sku)
    {
        $guid = $this -> get_inventory_item_id_by_SKU($sku);
        $item = $this -> get_inventory_item_by_id($guid);
        return $item;
    }

    public function get_image_urls_by_item_id($item_id)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemImages';
        $data = array('inventoryItemId' => $item_id);
        $response = $this -> request($url, $data);
        $image_urls = array();
        foreach ($response as $image) {
            if ($image['IsMain'] == true) {
                $image_url = str_replace('tumbnail_', '', $image['Source']);
                $image_urls[] = $image_url;
            }
        }
        foreach ($response as $image) {
            if ($image['IsMain'] != true) {
                $image_url = str_replace('tumbnail_', '', $image['Source']);
                $image_urls[] = $image_url;
            }
        }
        return $image_urls;
    }

    public function get_image_thumbnail_urls_by_item_id($item_id)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemImages';
        $data = array('inventoryItemId' => $item_id);
        $response = $this -> request($url, $data);
        $image_urls = array();
        foreach ($response as $image) {
            if ($image['IsMain'] == true) {
                $image_url = array();
                $image_url['full'] = str_replace('tumbnail_', '', $image['Source']);
                $image_url['thumb'] = $image['Source'];
                $image_urls[] = $image_url;
            }
        }
        foreach ($response as $image) {
            if ($image['IsMain'] != true) {
                $image_url = array();
                $image_url['full'] = str_replace('tumbnail_', '', $image['Source']);
                $image_url['thumb'] = $image['Source'];
                $image_urls[] = $image_url;
            }
        }
        return $image_urls;
    }

    public function get_image_urls_by_SKU($sku)
    {
        $item_id = $this -> get_inventory_item_id_by_SKU($sku);
        $image_urls = $this -> get_image_urls_by_item_id($item_id);
        return $image_urls;
    }

    public function set_primary_image($productGuid, $imageGuid)
    {
        $url = $this->server . '/api/Inventory/SetInventoryItemImageAsMain';
        $data = array();
        $data['inventoryItemId'] = $productGuid;
        $data['mainImageId'] = $imageGuid;
        $response = $this->request($url, $data);
        return $response;
    }

    public function getVariationGroupIdBySKU($sku)
    {
        $url = $this -> server . '/api/Stock/SearchVariationGroups';
        $data = array();
        $data['searchText'] = $sku;
        $data['searchType'] = 'ParentSKU';
        $data['entriesPerPage'] = '100';
        $data['pageNumber'] = 1;
        $response = $this -> request($url, $data);
        return $response['Data'][0]['pkVariationItemId'];
    }

    public function get_stock_level_by_id($stock_id, $location = 'Default')
    {
        $url = $this -> server . '/api/Stock/GetStockLevel';
        $data = array();
        $data['stockItemId'] = $stock_id;
        $response = $this -> request($url, $data);
        foreach ($response as $loc) {
            if ($loc['Location']['LocationName'] == $location) {
                return $loc['Available'];
            }
        }
    }

    public function get_channel_titles($guid)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemTitles';
        $data = array('inventoryItemId' => $guid);
        $response = $this -> request($url, $data);
        $channels = [];
        foreach ($response as $channel) {
            if ($channel['Source'] == 'AMAZON') {
                if ($channel['SubSource'] == 'Stc Stores') {
                    $channels['amazon'] = $channel['Title'];
                }
            } elseif ($channel['Source'] == 'EBAY') {
                if ($channel['SubSource'] == 'EBAY0') {
                    $channels['ebay'] = $channel['Title'];
                }
            } elseif ($channel['Source'] == 'SHOPIFY') {
                if ($channel['SubSource'] == 'stcstores.co.uk (shopify)') {
                    $channels['shopify'] = $channel['Title'];
                }
            }
        }
        return $channels;
    }

    public function get_channel_prices($guid)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemPrices';
        $data = array('inventoryItemId' => $guid);
        $response = $this -> request($url, $data);
        $channels = [];
        foreach ($response as $channel) {
            if ($channel['Source'] == 'AMAZON') {
                if ($channel['SubSource'] == 'Stc Stores') {
                    $channels['amazon'] = $channel['Price'];
                }
            } elseif ($channel['Source'] == 'EBAY') {
                if ($channel['SubSource'] == 'EBAY0') {
                    $channels['ebay'] = $channel['Price'];
                }
            } elseif ($channel['Source'] == 'SHOPIFY') {
                if ($channel['SubSource'] == 'stcstores.co.uk (shopify)') {
                    $channels['shopify'] = $channel['Price'];
                }
            }
        }
        return $channels;
    }

    public function get_channel_descriptions($guid)
    {
        $url = $this -> server . '/api/Inventory/GetInventoryItemDescriptions';
        $data = array('inventoryItemId' => $guid);
        $response = $this -> request($url, $data);
        $channels = [];
        foreach ($response as $channel) {
            if ($channel['Source'] == 'AMAZON') {
                if ($channel['SubSource'] == 'Stc Stores') {
                    $channels['amazon'] = $channel['Description'];
                }
            } elseif ($channel['Source'] == 'EBAY') {
                if ($channel['SubSource'] == 'EBAY0') {
                    $channels['ebay'] = $channel['Description'];
                }
            } elseif ($channel['Source'] == 'SHOPIFY') {
                if ($channel['SubSource'] == 'stcstores.co.uk (shopify)') {
                    $channels['shopify'] = $channel['Description'];
                }
            }
        }
        return $channels;
    }

    public function get_variation_children($parent_guid)
    {
        $url = $this -> server . '/api/Stock/GetVariationItems';
        $data = array('pkVariationItemId' => $parent_guid);
        $response = $this -> request($url, $data);
        $variation_children = array();
        foreach ($response as $child) {
            $variation_children[] = $child['pkStockItemId'];
        }
        return $variation_children;
    }
}
