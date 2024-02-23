<?php

use CFPropertyList\CFPropertyList;

class Extensions_model extends \Model {

    function __construct($serial='')
    {
        parent::__construct('id', 'extensions'); // Primary key, tablename
        $this->rs['id'] = '';
        $this->rs['serial_number'] = $serial;
        $this->rs['name'] = '';
        $this->rs['bundle_id'] = '';
        $this->rs['version'] = '';
        $this->rs['path'] = '';
        $this->rs['developer'] = '';
        $this->rs['teamid'] = '';
        // $this->rs['codesign'] = ''; // This column is deprecated and not used
        $this->rs['executable'] = '';
        $this->rs['boot_uuid'] = null;
        $this->rs['developer_mode'] = null; // Boolean
        $this->rs['extension_policies'] = null;
        $this->rs['state'] = null;
        $this->rs['categories'] = null;

        $this->serial_number = $serial;
    }

    // ------------------------------------------------------------------------
    /**
     * Process data sent by postflight
     *
     * @param string data
     * @author tuxudo
     **/
    function process($plist)
    {
        if ( ! $plist){
            throw new Exception("Error Processing Request: No property list found", 1);
        }

        // Delete previous set        
        $this->deleteWhere('serial_number=?', $this->serial_number);

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        $plist = $parser->toArray();

        foreach ($plist as $kext) {
            // Check if we have a bundle ID
            if( ! array_key_exists("bundle_id", $kext)){
                continue;
            }
            
            // Get extension name
            $path_array = explode("/", $kext['path']);
            $kext['name'] = str_replace([".kext",".systemextension"],["",""],array_pop($path_array));
            
             // Add the serial mumber to each entry
            $kext['serial_number'] = $this->serial_number;

            foreach ($this->rs as $key => $value) {
                // If key does not exist in $kext, null it
                if ( ! array_key_exists($key, $kext) || $kext[$key] == '' && $kext[$key] != '0') {
                    // Only some of the keys get nulled
                    if ($key == 'boot_uuid' || $key == 'developer_mode' || $key == 'extension_policies' || $key == 'state' || $key == 'categories'){
                        $this->rs[$key] = null;
                    } else {
                        $this->rs[$key] = "";
                    }
                } else {
                    $this->rs[$key] = $kext[$key];
                }
            }

            // Save kext
            $this->id = '';
            $this->save();
        }
    }
}
