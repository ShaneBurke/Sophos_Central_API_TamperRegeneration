# Sophos_Central_API_TamperRegeneration

1. Copy all files to a location that account running scheduled task will have read, write, and execute permissions.
2. Create API credentials in central.sophos.com and update settings in config.ini
3. Create scheduled task to run C:/pathtofolder/TamperRegeneration/php/php.exe -f C:/pathtofolder/TamperRegeneration/run.php

Logs will be stored under C:/<pathtofolder>/TamperRegeneration/logs/ which should suffice as evidence for audit requirements.
  
config.ini includes the following options:
client_id     = Gathered from central.sophos.com
client_secret = Gathered from central.sophos.com
enabletamper  = 'true' //string true or false value when true tamper code will be regenerated for all devices in tenant
regentamper   = 'true' //string true or false value when true tamper protection will be reenabled if someone manually disabled it from sophos central dashboard
timezone      = America/New_York //see php doumentation for timezone options https://www.php.net/manual/en/timezones.php
