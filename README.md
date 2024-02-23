Extensions module
==============

Provides information about third party extensions (kexts) and system extensions (macOS 10.15+). Special thanks to frogor for helping with the data gathering script.

Table Schema
-----
* name - varchar(255) - Name of the extension
* bundle_id - varchar(255) - Bundle ID of the extension
* version - varchar(255) - Version of the extension
* path - text - Directory of extension
* developer - varchar(255) - Code signer's developer name
* teamid - varchar(255) - Developer's Team ID
* executable - text - Location of executable within extension
* boot_uuid - varchar(255) - Boot UUID of the current system extension configuration
* developer_mode - boolean - Is developer mode enabled for system extensions
* extension_policies - text - JSON of the current MDM provided system extension policies
* state - varchar(255) - State of the system extension
* categories - varchar(255) - Category of system extension


