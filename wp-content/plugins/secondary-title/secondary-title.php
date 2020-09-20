<?php
   /**
    * (C) 2019 by Kolja Nolte
    * kolja.nolte@gmail.com
    * https://www.koljanolte.com
    *
    * This program is free software; you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 2 of the License, or
    * (at your option) any later version.
    *
    * @project Secondary Title
    */

   /**
    * Plugin Name:   Secondary Title
    * Plugin URI:    https://www.koljanolte.com/wordpress/plugins/secondary-title/
    * Description:   Adds a secondary title to posts, pages and custom post types.
    * Version:       2.0.2
    * Author:        Kolja Nolte
    * Author URI:    http://www.koljanolte.com
    * License:       GPLv2 or later
    * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
    * Text Domain:   secondary-title
    * Domain Path:   /languages
    */

   /**
    * Stop script when the file is called directly.
    */
   if(!function_exists("add_action")) {
      return false;
   }

   define("SECONDARY_TITLE_PATH", plugin_dir_path(__FILE__));
   define("SECONDARY_TITLE_URL", plugin_dir_url(__FILE__));
   define("SECONDARY_TITLE_VERSION", "2.0.2");

   /** Install default settings (if not set yet) */
   register_activation_hook(__FILE__, "secondary_title_install");

   /** Handles the donation notification display settings */
   register_deactivation_hook(__FILE__, "secondary_title_reset_donation_notice");

   /** Calls function which adds a link to the settings page on "Plugins" section in the admin area */
   add_action("plugin_action_links_" . plugin_basename(__FILE__), "secondary_title_add_settings_link");

   function secondary_title_load_translations() {
      load_plugin_textdomain(
         "secondary-title",
         false,
         plugin_basename(
            plugin_dir_path(__FILE__)
         ) . "/languages"
      );
   }

   /** Include plugin files */
   $include_directories = array(
      "includes"
   );

   /** Loop through the set directories */
   foreach($include_directories as $include_directory) {
      $include_directory = plugin_dir_path(__FILE__) . $include_directory;

      /** Skip directory if it's not a valid directory */
      if(!is_dir($include_directory)) {
         continue;
      }

      /** Gather all .php files within the current directory */
      $include_files = glob($include_directory . "/*.php");
      foreach($include_files as $include_file) {
         /** Skip file if file is not valid */
         if(!is_file($include_file)) {
            continue;
         }

         /** Include current file */
         require_once $include_file;
      }
   }