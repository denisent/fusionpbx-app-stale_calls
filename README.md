# Stale Calls
Calls that are identified as being stale or orphaned are candidates for ending/purging. Purged stale calls are logged to the database, and the purged records can be logged on the Stale Call Logs page. On the Stale Call Logs page you can see who purged the records and there is a link to the CDR details page.

Stale Calls page
<img width="1443" height="600" alt="2026-07-22_11-00" src="https://github.com/user-attachments/assets/729238c7-6a10-4721-b169-0ad119a193a1" />


Stale Call Logs page
<img width="1443" height="381" alt="2026-07-22_11-27" src="https://github.com/user-attachments/assets/0b000ba6-47a0-4365-a67d-bfb69e3be4b2" />


## Installation

### 1. Clone the repository

SSH into your FusionPBX server and clone the app into the FusionPBX apps directory:

```
cd /var/www/fusionpbx/app
git clone https://github.com/fusionpbx/fusionpbx-app-stale_calls.git stale_calls
```

### 2. Set ownership

Make sure the web server user owns the new directory:

```
chown -R www-data:www-data /var/www/fusionpbx/app/stale_calls
```

> If your server uses a different web user (e.g. `nginx` or `apache`), replace `www-data` accordingly.

### 3. Register the app with FusionPBX

Log into the FusionPBX web interface as a superadmin and go to:

**Advanced → Upgrade**

Click **App Defaults** to register the application's menu entry and permissions. This step adds the *Stale Calls* menu entry to the **Advanced** menu and grants access to the appropriate groups.

### 4. Reload the menu

If the menu entry does not appear immediately, go to:

**Advanced → Menu Manager** → select your menu → click **Defaults**

Then log out and back in for the menu to refresh.

## Permissions

The following permission is registered automatically during the App Defaults step:

| Permission | Default Groups |
|---|---|
| `stale_call_log_view` | superadmin, admin |
| `stale_call_purge` | superadmin, admin |
| `stale_call_view ` | superadmin, admin |
 	 	

## Default Settings

The following default settings variable(s) are registered automatically during the App Defaults step:
| Stale Calls section | Default Value |
|---|---|
| `minimum_age_minutes` | 10 minutes |
