<?php 

/**
 * extensions module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Extensions_controller extends Module_controller
{
    /*** Protect methods with auth! ****/
    function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }

    /**
     * Default method
     *
     **/
    function index()
    {
        echo "You've loaded the extensions module!";
    }

    /**
     * Get data for scroll widget
     *
     * @author tuxudo
     **/
    public function get_scroll_widget($column)
    {
        // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $sql = "SELECT COUNT(CASE WHEN ".$column." <> '' AND ".$column." IS NOT NULL THEN 1 END) AS count, ".$column." 
                FROM extensions
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                AND ".$column." <> '' AND ".$column." IS NOT NULL 
                GROUP BY ".$column."
                ORDER BY count DESC";

        $queryobj = new Extensions_model;
        jsonView($queryobj->query($sql));
    }

    /**
    * Get data for button widget
    *
    * @author tuxudo
    **/
    public function get_button_widget($column)
    {
         // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $sql = "SELECT COUNT(CASE WHEN ".$column." = 'activated_enabled' THEN 1 END) AS 'activated_enabled',
                    COUNT(CASE WHEN ".$column." = 'activated_disabled' THEN 1 END) AS 'activated_disabled',
                    COUNT(CASE WHEN ".$column." = 'terminated_waiting_to_uninstall_on_reboot' THEN 1 END) AS 'terminated_waiting_to_uninstall_on_reboot',
                    COUNT(CASE WHEN ".$column." = 'activated_waiting_for_user' THEN 1 END) AS 'activated_waiting_for_user',
                    COUNT(CASE WHEN ".$column." = 'waiting_for_approval' THEN 1 END) AS 'waiting_for_approval',
                    COUNT(CASE WHEN ".$column." = 'blocked' THEN 1 END) AS 'blocked'
                    FROM extensions
                    LEFT JOIN reportdata USING (serial_number)
                    WHERE ".get_machine_group_filter('');

        $out = [];
        $queryobj = new Extensions_model();
        foreach($queryobj->query($sql)[0] as $label => $value){
                $out[] = ['label' => $label, 'count' => $value];
        }

        jsonView($out);
    }

    /**
    * Retrieve data in json format
    *
    **/
    public function get_data($serial_number = '')
    {
        $obj = new View();
        
        if (! $this->authorized()) {
            $obj->view('json', array('msg' => 'Not authorized'));
            return;
        }

        // Remove non-serial number characters
        $serial_number = preg_replace("/[^A-Za-z0-9_\-]]/", '', $serial_number);

        $sql = "SELECT `name`, `bundle_id`, `version`, `path`, `developer`, `teamid`, `executable`, `boot_uuid`, `developer_mode`, `extension_policies`, `state`, `categories`
                FROM `extensions`
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                AND `serial_number` = '$serial_number'";

        $queryobj = new Extensions_model();
        $extensions_tab = $queryobj->query($sql);
        $obj->view('json', array('msg' => current(array('msg' => $extensions_tab)))); 
    }
} // End class Extensions_controller
