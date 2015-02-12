<?php

/* So far this is a very limited set of functions geared towards the operation of CKAN via PHP */

class CKAN
{

    function CKAN($api_key, $ckan_base_url)
    {

        //there needs to be some error checking here to make sure that inputs are as required.

        //set our class wide keys
        $this->api_key = $api_key;
        $this->ckan_base_url = $ckan_base_url;

    }

    function safeTitle($str)
    { //strips out all bad chars from a string

        $str = str_ireplace(' - ', ' ', $str);
        $str = str_ireplace('%26', 'and', $str);
        $str = preg_replace("/[^A-Za-z0-9\-\s]/", "", $str); //remove any non alpha characters from the str
        $str = preg_replace("/\s+/", "-", $str); //replace any spaces (even multiple spaces with a das;
        $str = strtolower($str); //finally convert to lowercase

        return $str;

    }

    function safeFile($str)
    { //strips out all bad chars from a string for a filename

        $str = preg_replace("/[^A-Za-z0-9\.\-\s]/", "", $str); //remove any non alpha characters from the str
        $str = preg_replace("/\s+/", "-", $str); //replace any spaces (even multiple spaces with a das;
        $str = strtolower($str); //finally convert to lowercase

        $str = str_ireplace("---", "-", $str);
        $str = str_ireplace("--", "-", $str);

        return $str;

    }

    function combineForCKAN($required, $optional)
    {//combines two arrays and makes them ready for sending via POST

        $final_array = array();

        foreach ($required as $key => $value) $final_array[$key] = $value; //encode all the required variables for array
        foreach ($optional as $key => $value) $final_array[$key] = $value; //encode all the options for combined array

        return $final_array;
    }


    function createDataset($dataset_required, $dataset_optional = array())
    {

        /* This function creates an empty dataset on a CKAN platform
            explanation of what is expected by this function
            $dataset_required array: these are the bare minimum things required to create your dataset you must include all these

                'title' -- what ckan will display to users of the site
                'owner_org' -- the CKAN name of the organisation
                'jurisdiction' -- level of Government this data covers {Federal, State, Local}
                'spatial coverage' -- the area the data covers
                'temporal_coverage' -- timeframe does this dataset covers
                'contact point' -- who to contact regarding the data
                'granularity' -- the scale or level of detail included in the data
                'update_freq' -- how often the data is updated
                'data_state' -- the state of the data {active, deleted}

                expected delivery to function is array ('name' => var)

            $dataset_optional array: these are optional yet advisable things to include in your dataset you can include all or none of these

                'name' -- the name by which CKAN identifies the dataset, if this is not provided but the title has been provided the function will attempt to use the the title
                'author' -- the author of this dataset
                'author_email -- their email
                'maintainer' -- the mainter of the dataset
                'maintainer_email' -- the maintainer's email
                'license_id' -- the license_id of the dataset, can be accessed by using api/action/license_list
                'notes' -- the description of the dataset
                'url' -- the source of the dataset or a location where more info can be accessed
                'state' -- the state of the data {active, deleted}
                'tags' -- add tags to a dataset, expected as a comma delimitted list.
                'type' -- the type of package this is {dataset}
                'private' -- set privacy {TRUE, FALSE}

                expected delivery to function is array ('name' => var)

            If successful the function will return the id of the newely created dataset. However, we are going to let CKAN do the heavy lifting on errors though and will return errors directly from CKAN.

        */

        //check to ensure that both arrays are infact arrays.

        if (!is_array($dataset_required) || !is_array($dataset_optional)) {
            return "<strong>Error:</strong> \$dataset_required & \$dataset_optional are expected as arrays.";
        }

        $dataset_create_vars = $this->combineForCKAN($dataset_required, $dataset_optional);

        if (!isset($dataset_create_vars['name'])) $dataset_create_vars['name'] = $this->safeTitle($dataset_required['title']); //name is required and we can build it from title if it doesn't already exist

        if (isset($dataset_create_vars['tags'])) {//give CKAN the tags in a format it'll understand

            $tags = explode(',', $dataset_create_vars['tags']);
            $tags_for_ckan = array();

            foreach ($tags as $tag) array_push($tags_for_ckan, array('name' => rawurlencode($tag)));

            $dataset_create_vars['tags'] = $tags_for_ckan;
        }

        $ch = curl_init();

        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Expect: ', 'Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Updater",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/package_create",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($dataset_create_vars),
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);
        curl_close($ch);

        //echo $response."<br />";

        $return_object = json_decode($response);

        //print_r($dataset_create_vars);

        //Australian Reinsurance Pool Corporation FOI Disclosure Log

        //print_r($return_object);

        if (empty($return_object->result->id)) {
            echo "<strong>Fatal Error:</strong> Failed to create dataset.";
            exit();
        }

        return $return_object->result->id;

    }


    function updateDataset($dataset_id, $dataset_update = array())
    {

        /* This function updates an existing dataset on a CKAN platform
            explanation of what is expected by this function
            $dataset_update array: these are the bare minimum things required to create your dataset you must include all these

                'title' -- what ckan will display to users of the site
                'owner_org' -- the CKAN name of the organisation
                'jurisdiction' -- level of Government this data covers {Federal, State, Local}
                'spatial coverage' -- the area the data covers
                'temporal_coverage' -- timeframe does this dataset covers
                'contact point' -- who to contact regarding the data
                'granularity' -- the scale or level of detail included in the data
                'update_freq' -- how often the data is updated
                'data_state' -- the state of the data {active, deleted}
                'name' -- the name by which CKAN identifies the dataset
                'author' -- the author of this dataset
                'author_email -- their email
                'maintainer' -- the mainter of the dataset
                'maintainer_email' -- the maintainer's email
                'license_id' -- the license_id of the dataset, can be accessed by using api/action/license_list
                'notes' -- the description of the dataset
                'url' -- the source of the dataset or a location where more info can be accessed
                'state' -- the state of the data {active, deleted}
                'tags' -- add tags to a dataset, expected as a comma delimitted list.
                'type' -- the type of package this is {dataset}
                'private' -- set privacy {TRUE, FALSE}

                expected delivery to function is array ('name' => var)

            If successful the function will return the id of the updated dataset.

        */

        //check to ensure that both arrays are infact arrays.

        if (!is_array($dataset_update)) {
            return "<strong>Error:</strong> \$dataset_update is expected as an arrays.";
        }

        $dataset_update_vars = $this->combineForCKAN(array('id' => $dataset_id), $dataset_update);

        if (isset($dataset_update_vars['tags'])) {//give CKAN the tags in a format it'll understand

            $tags = explode(',', $dataset_update_vars['tags']);
            $tags_for_ckan = array();

            foreach ($tags as $tag) array_push($tags_for_ckan, array('name' => rawurlencode($tag)));

            $dataset_update_vars['tags'] = $tags_for_ckan;
        }

        $ch = curl_init();

        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Expect: ', 'Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Updater",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/package_update",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($dataset_update_vars),
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);
        curl_close($ch);

        //echo $response."<br />";

        $return_object = json_decode($response);

        print_r($return_object);

        if (empty($return_object->result->id)) {
            echo "<strong>Fatal Error:</strong> Failed to update dataset.";
            exit();
        }

        return $return_object->result->id;

    }


    function checkDatasetExists($dataset_id, $return_object = FALSE)
    {

        /* This function checks to see if a dataset exists.

        Returns a TRUE or FALSE

        Can also return the full object if $return_object is set to TRUE */

        $dataset_id = $this->safeTitle($dataset_id); //we're going to run anything passed here through our safeTitle to strip out anything potentially malicious.

        $dataset_id_json = json_encode(array('id' => rawurlencode($dataset_id))); //make it so CKAN can understand our request.

        $ch = curl_init();

        //set our CURL option and provide auth as we may be dealing with a private dataset.
        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Updater",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/package_show",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $dataset_id_json,
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response);

        if ($return_object == FALSE) {
            if ($response->success) return TRUE; else return FALSE;
        } else {
            return $response;
        }

    }

    function checkResourceExists($resource_id, $return_object = FALSE)
    {

        /* This function checks to see if a dataset exists.

        Returns a TRUE or FALSE

        Can also return the full object if $return_object is set to TRUE */

        $resource_id = $this->safeTitle($resource_id); //we're going to run anything passed here through our safeTitle to strip out anything potentially malicious.

        $resource_id_json = json_encode(array('id' => $resource_id)); //make it so CKAN can understand our request.

        $ch = curl_init();

        //set our CURL option and provide auth as we may be dealing with a private dataset.
        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Updater",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/resource_show",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $resource_id_json,
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response);

        if ($return_object == FALSE) {
            if ($response->success) return TRUE; else return FALSE;
        } else {
            return $response;
        }

    }

    function addResource($add_resource_required, $add_resource_optional = array())
    {

        /*

        This function allows you to add an individual resource within a dataset via the CKAN API.

        At the bare minimum the function requires $add_resource_required array with the following values

        $add_resource_required is required it should have the following:
            'package_id' -- the id of the dataset we are adding the resource to.
            'upload' => -- the location of the file
            'name' => 'the name of the resource we are uploading'
        );

        there is an additional variable that can be set via the $add_resource_optional:

            'description' -- a short description of the resource

        */

        if (!is_array($add_resource_required) || !is_array($add_resource_optional)) {
            return "<strong>Error:</strong> \$add_resource_required & \$add_resource_optional are expected as arrays.";
        }

        if (!$this->checkDatasetExists($add_resource_required['package_id'])) {
            echo 'Dataset doesn\'t exist';
            return false;
        }

        $add_resource_vars = $this->combineForCKAN($add_resource_required, $add_resource_optional);

        //ok we need to know what the filetype is but we are going to assume it is the file extension.

        ///$add_resource_vars['format'] = pathinfo($add_resource_vars['upload'], PATHINFO_EXTENSION);

        if (function_exists('curl_file_create')) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $add_resource_vars['upload']);

            $add_resource_vars['upload'] = curl_file_create($add_resource_vars['upload'], $mimetype, $add_resource_vars['upload']);

        } else {

            $add_resource_vars['upload'] = '@' . $add_resource_vars['upload'] . ';filename=' . $this->safeFile(basename($add_resource_vars['upload']));

        }

        $ch = curl_init();

        curl_setopt_array($ch,
            array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
                CURLOPT_HEADER => FALSE,
                CURLOPT_USERAGENT => "DoF: CKAN PHP Updater",
                CURLOPT_URL => $this->ckan_base_url . "/api/action/resource_create",
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $add_resource_vars,
                CURLOPT_RETURNTRANSFER => TRUE)
        );

        $response = curl_exec($ch);

        print_r($response);

        curl_close($ch);

        $return_object = json_decode($response);

    }

    function updateResource($resource_id, $resource_optional = array())
    {

        /*
        This function allows you to update an individual resource within a dataset via the CKAN API.

        It returns a message on success or failure.

        $resource_id: this is the only required variable for this function, however simply setting this will not update anything.

        $resource_optional array setting any of the below will overwrite their values on the current resource

            'url' -- the location of the file
            'description' -- a short description of the resource
            'format' -- the filetype of the particular resource (CSV, XLS, DOC, PDF)

        */

        //check to see if user has set options to update, make sure they're set as an array.
        if (!is_array($resource_optional)) {
            return "<strong>Error:</strong> \$resource_optional is expected as an arrays.";
        }


        //check to see if the resource exists, if not fail and return error.
        $resource_object = $this->checkResourceExists($resource_id, TRUE);
        if (!$resource_object->success) {
            return "<strong>Fatal Error: Resource not found</strong>.";
        }

        //check to see if we're updating the url if not we need to set it from the previous resource as CKAN requires it to be set.
        if (!isset($resource_optional['url'])) {
            $resource_optional['url'] = $resource_object->result->url;
        }

        //only required in the array is the id
        $resource_required = array('id' => $resource_id);

        //combine our arrays.
        $resource_vars = $this->combineForCKAN($resource_required, $resource_optional);

        //ok we need to know what the filetype is but we are going to assume people aren't being naughty and set it by extension.
        if (isset($resource_vars['url'])) {
            $resource_vars['format'] = pathinfo($resource_vars['url'], PATHINFO_EXTENSION);
        }

        $ch = curl_init();

        curl_setopt_array($ch,
            array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
                CURLOPT_HEADER => FALSE,
                CURLOPT_USERAGENT => "DoF: CKAN PHP Updater",
                CURLOPT_URL => $this->ckan_base_url . "/api/action/resource_update",
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => json_encode($resource_vars),
                CURLOPT_RETURNTRANSFER => TRUE)
        );

        $response = curl_exec($ch);

        echo $response;
        exit();

        curl_close($ch);

        $return_object = json_decode($response);

        if ($return_object->success) {
            return "Changes to " . $return_object->result->name . " have been saved.";
        } else {
            return "<strong>Error:</strong> Changes could not be saved.";
        }

    }


    function searchDatasetSQL($dataset_id, $sql)
    {

        $search_resource = array("id" => $dataset_id, "sql" => $sql);

        $ch = curl_init();

        //set our CURL option and provide auth as we may be dealing with a private dataset.
        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Class",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/datastore_search_sql",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($search_resource),
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response)->result->records;


    }

    function searchDataset($dataset_id, $string, $limit = 10)
    {

        $search_resource = array("id" => $dataset_id, "q" => $string, "limit" => $limit);

        $ch = curl_init();

        //set our CURL option and provide auth as we may be dealing with a private dataset.
        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Class",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/datastore_search",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($search_resource),
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);

        //print_r($response);
        curl_close($ch);

        return json_decode($response)->result->records;


    }

    function searchDatasetFilter($dataset_id, $filter, $limit = 10)
    {

        $search_resource = array("id" => $dataset_id, "filters" => $filter, "limit" => $limit);

        $ch = curl_init();

        //set our CURL option and provide auth as we may be dealing with a private dataset.
        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Class",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/datastore_search",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($search_resource),
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);

        //print_r($response);
        curl_close($ch);

        return json_decode($response)->result->records;


    }

    function searchForDataset($query, $limit = 100)
    {

        $search_resource = array('q' => $query, 'rows' => $limit);

        $ch = curl_init();

        //set our CURL option and provide auth as we may be dealing with a private dataset.
        curl_setopt_array($ch, array(CURLOPT_HTTPHEADER => array('Authorization: ' . rawurlencode($this->api_key), 'X-CKAN-API-Key: ' . rawurlencode($this->api_key)),
            CURLOPT_HEADER => FALSE,
            CURLOPT_USERAGENT => "DoF: CKAN PHP Class",
            CURLOPT_URL => $this->ckan_base_url . "/api/action/package_search",
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($search_resource),
            CURLOPT_RETURNTRANSFER => TRUE));

        $response = curl_exec($ch);

        //print_r($response);
        curl_close($ch);

        return json_decode($response)->result->results;

    }


}

?>
