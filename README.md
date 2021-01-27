# Sophos_Central_API_TamperRegeneration

1. Copy all files to a location that account running scheduled task will have read, write, and execute permissions.
2. Create API credentials in central.sophos.com and update settings in config.ini
3. Create scheduled task to run C:/<pathtofolder>/TamperRegeneration/php/php.exe -f C:/<pathtofolder>/TamperRegeneration/run.php

Logs will be stored under C:/<pathtofolder>/TamperRegeneration/logs/ which should suffice as evidence or tamper protection password regeneration for audit requirements.
