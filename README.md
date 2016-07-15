# Facebook Plugin #

This plugin is an extension to the existing plugin Facebook Login for work with user groups.

## 1. Getting Started ##
###1.1. Installing requirements###

* PHP 5.5.9
* MySQL 5.6 +
* WordPress 4.5 +
* Facebook Login plugin 1.1.2 + (https://wordpress.org/plugins/wp-facebook-login/)
* Composer

### 1.2. Project structure###

    |- admin            : classes for Admin page of WordPress plugin
        |- css          : styles for Admin pages
        |- js           : js files for Admin pages
        |- template     : static HTML contents for Admin pages
    |- config           : plugin configs files
        |-admin         : plugin configs for admin part
        |-public        : plugin configs for public part
    |- includes         : base classes of plugins
        |- modules      : classes for modules of plugin
    |- public           : classes for Public pages of WordPress plugin
        |- css          : styles for Public pages
        |- js           : js files for Public pages
        |- template     : static HTML contents for Public pages
     |- vendor          : dependence included via composer
    fb-groups.php    : bootstrap file for all classes of plugin

### 1.3. Project Installing###

###1.3.1. Automatic installation of plugin###
For using plugin, user can just upload plugin to the Wordpress and activate in the Plugins List page. This page you can find /wp-admin/plugins.php.
**Note:** for work plugin needed **Facebook Login plugin 1.1.2** or latest version. Before activate this plugin, you need install, activate and setup Facebook Login plugin.

### 1.3.2. Installing for developers###
For using this plugin you need to create virtual host for website, and install last version of Wordpress CMS. Wordpress you can download from https://wordpress.org/download/
After this, you need to clone this repository to **/wp-content/plugins/** folder and do next steps:


    1. cd /wp-content/plugins/fb-groups
    2. composer install
    
## 2. Plugin config system ##
### 2.1. Post type configuration###
As you know, Wordpress give opportunity to create many different custom post type for work with entities. For more simple using this feature 
has been create file, which help user to create and edit post types. This file located in the **/config/postTypeConfig.json**. The structure of this file is shown below:

    {
        "name_of_custom_post_type": {
            "labels": {},
            "public": true,
            "has_archive": false,
            "adminMenu": false,
            "supports": [ "title" ]
         },
        ...     
    }

Field values can be seen on this link: https://codex.wordpress.org/Function_Reference/register_post_type

### 2.2. Actions and filter configuration###
With this configuration file, you can use a variety of events, which runs the Wordpress system. You can associate events with public methods 
in your classes. Actions and filters method need to be located in the main classes of your plugin (in the **admin/class-fb-groups-admin.php** or if 
it public part - in the **public/class-fb-groups-public.php**) This files located in the **/config/part_of_plugin/actionsConfig(filtersConfig).json**. 
The structure of this file is shown below:

    {
      "0": {
        "action": "wordpress_event",
        "scope": null,
        "method": "your_public_method",
        "priority": 10,
        "accepted_args": 2
      },
      ...
    }
    
### 2.3. Admin menus and scripts###
The configuration data are configured similarly to the above.

* Menus in the **/config/part_of_plugin/menusConfig.json**
* Menus in the **/config/part_of_plugin/scriptsConfig.json**

## 3. Statics files ##
### 3.1. Styles files###
For work with css files, you need to run sass preprocessor. You need to edit .scss files and need run commands:

    sass public/css/fb-groups.scss:public/css/fb-groups.css
    sass admin/css/fb-groups.scss:admin/css/fb-groups.css

for to see result in the Front-end part of the plugin.