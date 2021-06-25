# Sophos_Central_API_TamperRegeneration

This script will run through all devices in your Sophos cloud tenant and enable tamper protection if disabled as well as regenerate the tamper protection password for each device. For auditing purposes it will also provide logs which should suffice as evidence for any external auditors. <br><br>

1.) Place files in C:/TamperRegeneration/ (if you wish to put in a different location you must modify the php.ini to reflect the new location) <br><br>
2.) Modify config.ini to include API credentials obtained from cloud.sophos.com <br><br>
3.) Create scheduled task in windows event scheduler to run script at desired interval <br><br><br>

<b>Configuration Options</b><br><br>
<b>enabletamper</b> - setting this to true will cause tamper to be enabled when the script is run if someone disabled it manually from the device screen. This will not change the global tamper setting in Sophos Central.<br><br>
<b>regentamper</b> - setting this to true will cause every device to have their tamper protection code regenerated when this script is run.<br><br>
<b>timezone</b> - Set this to a php supported timezone for proper logging of events (see https://www.php.net/manual/en/timezones.php)<br><br>

