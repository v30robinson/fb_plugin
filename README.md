# Healerslibrary Facebook Plugin #

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
    |- includes         : base classes of plugins
        |- modules      : classes for modules of plugin
    |- public           : classes for Public pages of WordPress plugin
        |- css          : styles for Public pages
        |- js           : js files for Public pages
        |- template     : static HTML contents for Admin pages
     |- vendor          : dependence included via composer
    hl-fb-groups.php    : bootstrap file for all classes of plugin

### 1.3. Project Installing###

###1.3.1 Automatic installation of plugin###
For using plugin, user can just upload plugin to the Wordpress and activate in the Plugins List page. This page you can find /wp-admin/plugins.php.
**Note:** for work plugin needed **Facebook Login plugin 1.1.2** or latest version. Before activate this plugin, you need install, activate and setup Facebook Login plugin.

### 1.3.2 Installing for developers###
For using this plugin you need to create virtual host for website, and install last version of Wordpress CMS. Wordpress you can download from https://wordpress.org/download/
After this, you need to clone this repository to **/wp-content/plugins/** folder and do next steps:


    1. cd /wp-content/plugins/hl-fb-groups
    2. composer install