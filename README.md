# Stale Calls
See calls that are identified as being stale or orphaned are candidates for ending/purging. Purged stale calls are logged to the database, and the purged records can be logged on the Stale Call Logs page. On the Stale Call Logs page you can see who purged the records and there is a link to the CDR details page.




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
| `call_flow_map_view` | superadmin, admin |
 	 	

Access can be adjusted per group in **Advanced → Group Manager**.


## Usage

1. Navigate to **Reports → Call Flow Diagram**
2. Select a **Starting Type** from the dropdown (e.g. Inbound Routes)
3. Select the specific **Destination** you want to trace
4. Click **Generate Diagram**

Once the diagram renders:

- **Drag** any node to reposition it
- **Scroll / pinch** to zoom in and out
- **Double-click** a node to open its edit page in a new tab
- Use the **Fit View** button to re-center the diagram
- Use the **Download PNG** button to export the diagram as an image
