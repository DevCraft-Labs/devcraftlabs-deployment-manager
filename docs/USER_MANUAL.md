# User Manual

## Login
1. Open login page.
2. Enter username and password.

## Create Deployment Script
1. Go to Deployment Scripts.
2. Click New Script.
3. Fill metadata, working directory, and shell script content.
4. Optionally attach Redis/SMTP/Telegram profiles.
5. Save.

## Run Script
1. From script list click Run.
2. Monitor execution logs from script detail page.
3. When complete, the selected script Telegram connection is notified. If no connection is selected for the script, the active default Telegram notification channel in Settings is used.

## Configure Deployment Notifications
1. Create and test a Telegram connection in Telegram Manager. Its chat ID can be a Telegram channel ID.
2. In Settings, select it as the Default Telegram Notification Channel.
3. Optionally assign a different Telegram connection to an individual deployment script to override the default.

## Configure Cron
1. Open Cron Manager.
2. Set cron expression and enable.
3. Save.

## Browse Database Provisioning Metadata
1. Open **DB Provisioning**.
2. Create a MySQL connection with a cPanel MySQL username/password.
3. Use **Test** to confirm access, then choose **Open Explorer**.
4. Select a database and table to view columns, types, nullable flags, default values, primary keys, and indexes.
5. This explorer is strictly read-only and does not expose any data modification controls.

## Export Report
1. Open Settings page.
2. Click Download XLSX Report.
