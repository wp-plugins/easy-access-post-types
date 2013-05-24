<?php
/*
Plugin Name: Easy Access Post Types
Plugin URI: http://www.matgargano.com
Description: Provides easy access to "View Posts" for different post types in the admin bar and on the Dashboard in the admin area.
Version: 1.0
Author: Mat Gargano
Author Email: mgargano@gmail.com
License:

  Copyright 2012 Mat Gargano (matgargano.com)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/*
 * Instantiate the class, enabling the plugin
 *
 */

new post_type_dashboard_widget;

class post_type_dashboard_widget {
    
        protected $plugin_version         = "1.1";
        protected $plugin_name            = "post_type_dashboard_widget";
        protected $plugin_prefix          = "ptdw";
        protected $transient_name         = "ptdb_transient";
        protected $refresh_rate           = 600;
        
        protected $plugin_location;
        
        function __construct(){
            $this->plugin_location      = plugin_dir_url(__FILE__);
            add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueues') );
            add_action('wp_dashboard_setup', array($this, 'dashboard_widget_setup'));
            add_action( 'wp_before_admin_bar_render', array($this, 'admin_bar') );
            $this->option_template          = '<option class="%s">%s</option>';
            $this->select_template          = '<select id="%s">%s</select>';
            $this->label_template           = '<label for="%s">%s</label>';
            $this->ignore_post_types        = array("attachment", "revision", "nav_menu_item");
        } 
        function dashboard_widget_setup() {
                global $wp_meta_boxes;
                wp_add_dashboard_widget('custom_help_widget', 'Post Types', array($this, 'dashboard_widget'));
        }
        
        function dashboard_widget() {
                if ( current_user_can('manage_options') ) { 
                        $post_types = $this->get_post_types();
                        $options = '<option>--- Select a Post Type---</option>';
                        foreach($post_types as $post_type){
                                $options .= sprintf($this->option_template, $post_type['slug'], $post_type['name']);
                                $db_widget = '<div id="' . $this->plugin_name . '">';
                                $db_widget .= sprintf($this->label_template, $this->plugin_prefix . "-new", "Create New") . sprintf($this->select_template, $this->plugin_prefix . "-new", $options);
                                $db_widget .= "<br /><br />";
                                $db_widget .= sprintf($this->label_template, $this->plugin_prefix . "-viewposts", "View Posts") . sprintf($this->select_template, $this->plugin_prefix . "-viewposts", $options);
                                $db_widget .= '</div>';
                        
                        }
                        echo $db_widget;
                }
        }
        
        function get_post_types(){
                $post_types = get_transient($this->transient_name);
                if ($post_types === false){
                        $pts = get_post_types();
                        foreach($pts as $pt){
                                if (!in_array($pt, $this->ignore_post_types)){
                                        $post_type_object       = get_post_type_object($pt);
                                        $pt_name                = $post_type_object->labels->name;
                                        $post_types[] = array(  'slug'  => $pt, 
                                                                'name'  => $pt_name
                                                                );
                                }
                        }
                        set_transient($this->transient_name. $post_types, $this->refresh_rate);
                }
                return $post_types;
        }
        
        
        function admin_enqueues (){
                global $post_type;
                wp_register_script( "admin-" . $this->plugin_name, $this->plugin_location . "js/admin.js", array('jquery'), $this->plugin_version);
                wp_enqueue_script("jquery");
                wp_enqueue_script("admin-" . $this->plugin_name);
                wp_localize_script( $this->plugin_name, $this->plugin_name, array( 'plugin_name', $this->plugin_name ));
                wp_register_style( "admin-" . $this->plugin_name, $this->plugin_location . "css/admin.css", $this->plugin_version);
                wp_enqueue_style("admin-" . $this->plugin_name);
        }
        
        function admin_bar() {
                global $wp_admin_bar;
                $wp_admin_bar->add_menu( array(
                        'parent' => false,
                        'id' => 'view_posts',
                        'title' => __('View Posts'),
                        'href' => "#"
                ));
                
                if ( current_user_can('manage_options') ) { 
                        $post_types = $this->get_post_types();
                        $options = "";
                        foreach($post_types as $post_type){
                                $options .= sprintf($this->option_template, $post_type['slug'], $post_type['name']);
                                $wp_admin_bar->add_menu(array(
                                        'parent' => 'view_posts',
                                        'id' => $post_type['slug'],
                                        'title' => $post_type['name'],
                                        'href' => "/wp-admin/edit.php?post_type=" . $post_type['slug']
                                ));        
                                
                        }
                        
                        
                }
                
                

                
        }

}

