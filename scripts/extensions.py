#!/usr/local/munkireport/munkireport-python3

# Written by Tuxudo
# With much help from frogor

import objc
from Foundation import NSBundle
from Foundation import NSURL

import os
import sys
import json
import subprocess
from subprocess import Popen, PIPE

sys.path.insert(0, '/usr/local/munkireport')

from munkilib import FoundationPlist

def get_kext_info():
    # Get kexts info
    IOKit = NSBundle.bundleWithIdentifier_('com.apple.framework.IOKit')
    functions = [('KextManagerCopyLoadedKextInfo', b'@@@'),('KextManagerCreateURLForBundleIdentifier', b'@@@'),]
    objc.loadBundleFunctions(IOKit, globals(), functions)
    kernel_dict = KextManagerCopyLoadedKextInfo(None, None)

    out = []

    for kernelname in kernel_dict:
        if kernelname != '__kernel__' and not kernelname.startswith('com.apple.'):
            kext = {}
            kext['bundle_id'] = kernelname
            kext['path'] = kernel_dict[kernelname]['OSBundlePath']
            kext['version'] = kernel_dict[kernelname]['CFBundleVersion']
            kext['executable'] = kernel_dict[kernelname]['OSBundleExecutablePath']
            kext['bundle_codesign'] = ''
            kext['developer'] = ''
            kext['teamid'] = ''

            stdout = Popen("/usr/bin/codesign -dv --verbose=4 '"+kext['path']+"'", shell=True, stderr=PIPE).stderr
            output = stdout.read()

            for line in output.decode().splitlines():
                if "Authority=Developer ID Application: " in line:
                    kext['bundle_codesign'] = line.replace("Authority=Developer ID Application: ", "")
                    kext['developer'] = " ".join(kext['bundle_codesign'].split()[:-1])
                    kext['teamid'] = kext['bundle_codesign'].split()[-1].strip("()")

            out.append(kext)

    return out

def get_systemextensions_info():

    systemextensions_db="/Library/SystemExtensions/db.plist"

    if os.path.isfile(systemextensions_db):

        pl = FoundationPlist.readPlist(systemextensions_db)

        out = []
        i = 0
        for extension in pl['extensions']:
            sysext = {}
            if i == 0:
                sysext['boot_uuid'] = pl['bootUUID']
                sysext['developer_mode'] = to_bool(pl['developerMode'])

                # Process MDM system extension policies
                extensionPolicies = pl["extensionPolicies"]
                extension_policies = []
                for policy in extensionPolicies:
                    extension_policy = {}
                    for item in policy:
                        if item == "allowUserOverrides":
                            extension_policy["allow_user_overrides"] = to_bool(policy[item])
                        elif item == "allowedExtensionTypes" and len(policy[item]) > 0:
                            extension_policy['allowed_extension_types'] = ""
                            for allowed_type in policy[item]:
                                extension_policy['allowed_extension_types'] = policy[item][allowed_type] + ", " + extension_policy['allowed_extension_types']
                            try:
                                extension_policy['allowed_extension_types'] = policy['allowed_extension_types'][:-2]
                            except:
                                pass
                        elif item == "allowedExtensions" and len(policy[item]) > 0:
                            extension_policy['allowed_extensions'] = policy[item]
                        elif item == "allowedTeamIDs" and len(policy[item]) > 0:
                            extension_policy['allowed_team_IDs'] = policy[item]
                        elif item == "removableExtensions" and len(policy[item]) > 0:
                            extension_policy['removable_extensions'] = policy[item]
                        elif item == "uniqueID":
                            extension_policy['profile_payload_id'] = policy[item]
                    extension_policies.append(extension_policy)
                try:
                    sysext['extension_policies'] = json.dumps(extension_policies,indent=2,default=str).replace(r"\n", "\n")
                except:
                    sysext['extension_policies'] = 'Error Saving System Extension Policy Data'
                i = 1
            for item in extension:
                if item == 'originPath':
                    sysext['path'] = extension[item]
                elif item == 'bundleVersion':
                    if 'CFBundleShortVersionString' in extension[item] and "CFBundleVersion" in extension[item]:
                        if extension[item]["CFBundleShortVersionString"] != extension[item]["CFBundleVersion"]:
                            sysext['version'] = extension[item]["CFBundleShortVersionString"] +" ("+ extension[item]["CFBundleVersion"]+")"
                        else:
                            sysext['version'] = extension[item]["CFBundleShortVersionString"]
                    elif 'CFBundleShortVersionString' in extension[item]:
                        sysext['version'] = extension[item]["CFBundleShortVersionString"]
                    elif "CFBundleVersion" in extension[item]:
                        sysext['version'] = extension[item]["CFBundleVersion"]+")"
                elif item == "container":
                    if 'bundlePath' in extension[item]:
                        sysext['executable'] = extension[item]['bundlePath']

                elif item == "stagedBundleURL":
                    if "relative" in extension[item]:

                        bundle_path_relative = extension[item]['relative'].replace("file://", "")
                        stdout = Popen("/usr/bin/codesign -dv --verbose=4 '"+bundle_path_relative+"'", shell=True, stderr=PIPE).stderr
                        output = stdout.read()

                        for line in output.decode().splitlines():
                            if "Authority=Developer ID Application: " in line:
                                sysext['bundle_codesign'] = line.replace("Authority=Developer ID Application: ", "")
                                sysext['developer'] = " ".join(sysext['bundle_codesign'].split()[:-1])
                                sysext['teamid'] = sysext['bundle_codesign'].split()[-1].strip("()")

                elif item == "teamID" and "teamID" not in sysext:
                    sysext['teamid'] = extension[item]
                elif item == "identifier":
                    sysext['bundle_id'] = extension[item]
                elif item == "state":
                    sysext['state'] = extension[item]

                elif item == "categories":
                    sysext['categories'] = ""
                    for category in extension[item]:
                        sysext['categories'] = category + ", " + sysext['categories']
                    try:
                        sysext['categories'] = sysext['categories'][:-2]
                    except:
                        pass

            out.append(sysext)
        return out
    else:
        return []

def to_bool(s):
    if s == True:
        return 1
    else:
        return 0

def main():
    """Main"""
    try:
        kext_info = get_kext_info()
    except:
        kext_info = []

    result = kext_info + get_systemextensions_info()

    # Write results to cache file
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'extensions.plist')
    FoundationPlist.writePlist(result, output_plist)

if __name__ == "__main__":
    main()