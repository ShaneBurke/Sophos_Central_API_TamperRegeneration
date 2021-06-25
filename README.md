# Sophos_Central_API_TamperRegeneration

This script will run through all devices in your Sophos cloud tenant and enable tamper protection is disabled as well as regenerate the tamper protection password for each device. For auditing purposes it will also provide logs which should suffice as evidence for any external auditors.

1.) Place files in C:/TamperRegeneration/ (if you wish to put in a different location you must modify the php.ini to reflect the new location)
2.) Modify config.ini to include API credentials obtained from cloud.sophos.com
3.) Create scheduled task in windows event scheduler to run script at desired interval
