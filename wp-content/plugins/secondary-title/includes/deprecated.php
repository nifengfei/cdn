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
    * Stop script when the file is called directly.
    *
    * @since 0.1.0
    */
   if(!function_exists("add_action")) {
      return false;
   }

   /**
    * @param int    $post_id
    * @param string $prefix
    * @param string $suffix
    * @param bool   $use_settings
    * @param array  $options
    *
    * @return string
    *
    * @deprecated
    */
   function get_secondary_title_link($post_id = 0, $prefix = "", $suffix = "", $use_settings = false, array $options = array()) {
      _deprecated_function(
         "get_secondary_title_link",
         "1.9.5"
      );

      /** If $post_id not set, use current post ID */
      if(!$post_id) {
         $post_id = (int)get_the_ID();
      }

      $secondary_title = get_secondary_title(
         $post_id,
         $prefix,
         $suffix,
         $use_settings
      );

      /** Return false if post does not have a secondary title */
      if(!$secondary_title) {
         return "";
      }

      /** Define default options */
      $merged_options  = array();
      $default_options = array(
         "id"          => "",
         "class"       => "secondary-title secondary-title-link",
         "target"      => "",
         "before"      => "",
         "after"       => "",
         "before_link" => "",
         "after_link"  => ""
      );

      /** Use default option value if option is not set */
      foreach($default_options as $default_option => $default_value) {
         $value = $default_value;

         if(isset($options[$default_option])) {
            $value = $options[$default_option];
         }

         $merged_options[$default_option] = $value;
      }

      $options = $merged_options;

      /** Define attributes */
      $link_attributes = "";
      $attributes      = array(
         "id",
         "class",
         "target"
      );

      /** Build attributes if set */
      foreach($attributes as $attribute) {
         if(!empty($options[$attribute])) {
            $link_attributes .= " " . $attribute . '="' . $options[$attribute] . '"';
         }
      }

      /** Build the final link */
      $link = $options["before_link"] . '<a href="' . get_permalink(
            $post_id
         ) . '" ' . $link_attributes . '>' . $options["before"] . $secondary_title . $options["after"] . '</a>' . $options["after_link"];

      return $link;
   }

   /**
    * Displays the secondary title link.
    *
    * @param int    $post_id
    * @param string $prefix
    * @param string $suffix
    * @param bool   $use_settings
    * @param array  $options
    *
    * @deprecated
    *
    * @since 0.5.0
    */
   function the_secondary_title_link($post_id = 0, $prefix = "", $suffix = "", $use_settings = false, array $options = array()) {
      _deprecated_function(
         "the_secondary_title_link",
         "1.9.5"
      );

      echo get_secondary_title_link(
         $post_id,
         $prefix,
         $suffix,
         $use_settings,
         $options
      );
   }

   /**
    * Returns an error or success message on top of the screen.
    *
    * @param        $message
    * @param string $type
    *
    * @return string
    *
    * @deprecated
    *
    * @since 1.6.0
    */
   function secondary_title_get_message($message, $type = "success") {
      _deprecated_function(
         "secondary_title_get_message",
         "1.9.5"
      );

      if($type !== "success" || !$type === "error") {
         return "";
      }

      $class = "updated";
      if($type === "error") {
         $class = "error";
      }

      $output = "";
      $output .= "<div class=\"secondary-title-message $class\">";
      $output .= "<p>$message</p>";
      $output .= "</div>";

      return $output;
   }

   /**
    * Displays an error or success message on top of the screen.
    *
    * @param        $message
    * @param string $type
    *
    * @since 1.6.0
    *
    * @deprecated
    */
   function secondary_title_the_message($message, $type = "success") {
      _deprecated_function(
         "secondary_title_the_message",
         "1.9.5"
      );

      echo secondary_title_get_message($message, $type);
   }

   /**
    * Returns a single default setting and its value.
    *
    * @param string $setting
    *
    * @return mixed
    *
    * @since 0.1.0
    *
    * @deprecated
    */
   function secondary_title_get_default_setting($setting) {
      _deprecated_function(
         "secondary_title_get_default_setting",
         "1.9.7",
         "secondary_title_get_default_settings()[\"secondary_title_\$setting\"]"
      );

      $secondary_title_default_settings = secondary_title_get_default_settings();
      $setting                          = $secondary_title_default_settings[$setting];

      return $setting;
   }