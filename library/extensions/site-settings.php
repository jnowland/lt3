<?php
/**
 * Site Settings
 * ------------------------------------------------------------------------
 * site-settings.php
 * @version 2.0 | April 1st 2013
 * @package lt3
 * @author  Beau Charman | @beaucharman | http://beaucharman.me
 * @link    https://github.com/beaucharman/lt3
 * @license MIT License
 *
 * To use and view the option:
 *   global $lt3_site_settings;
 *   echo $lt3_site_settings['setting_id'];
 * ------------------------------------------------------------------------ */
class LT3_Site_Settings_Page
{
  public $group;
  public $name;
  public $fields;
  public $menu_name;
  public $title;
  public $site_settings;

  /**
   * Class Constructor
   * ------------------------------------------------------------------------
   * __construct()
   * @param  $_site_settings | array
   * @return void
  * ------------------------------------------------------------------------ */
  public function __construct( $group, $name, $fields = array(), $menu_name = '', $title = '' )
  {
    $this->fields_group  = $this->uglify_words( $group );
    $this->fields_name   = $this->uglify_words( $name );
    $this->fields        = $fields;
    $this->menu_name     = ( $menu_name )
      ? $menu_name : $this->prettify_words( $this->fields_name );
    $this->title         = ( $title )
      ? $title : get_bloginfo( 'name' ) . ' ' . $this->prettify_words( $this->fields_name );
    $this->site_settings = get_option( $this->fields_name );

    /**
     * Initialise the settings page and
     * set the $lt3_site_settings global variable.
     * ------------------------------------------------------------------------ */
    add_action( 'admin_init', array( &$this, 'site_settings_init' ) );
    add_action( 'admin_menu', array( &$this, 'site_settings_add_page' ) );
  }

  /**
   * Site Settings Init
   * ------------------------------------------------------------------------
   * site_settings_init()
   * Register the LT3 site settings
   * ------------------------------------------------------------------------ */
  public function site_settings_init()
  {
    register_setting(
      $this->fields_group,
      $this->fields_name,
      array( &$this, 'site_settings_validate' )
    );
  }

  /**
   * Site Settings Add Page
   * ------------------------------------------------------------------------
   * site_settings_add_page()
   * Hook the options page with the required settings
   * ------------------------------------------------------------------------ */
  public function site_settings_add_page()
  {
    add_theme_page(
      $this->title,
      $this->menu_name,
      'manage_options',
      $this->fields_group,
      array( &$this, 'site_settings_render_page' )
    );
  }

  /**
   * Site Settings Render Page
   * ------------------------------------------------------------------------
   * site_settings_render_page()
   * Render the settings page
   * ------------------------------------------------------------------------ */
  function site_settings_render_page()
  {
    /* Check that the user is allowed to update options */
    if ( !current_user_can( 'manage_options' ) )
    {
      wp_die( 'You do not have sufficient permissions to access this page . ' );
    }

    echo '<div class="wrap">';

    if ( isset( $_GET['settings-updated'] ) )
    {
      echo '<div id="message" class="updated fade"><p>' . $this->title . ' Updated . </p></div>';
    }

    /* Show the page settings title */
    screen_icon( 'themes' ); echo '<h2>' . $this->title  . '</h2>';

    echo '<form method="post" action="options . php">';
    echo '<table class="form-table lt3-form-container">';

    /* Declare the settings field */
    settings_fields( $this->fields_group );

    foreach( $this->fields as $field )
    {
      /* Set the page's field name */
      $fields_name = $this->fields_name;

      /* Get the id */
      $id = ( isset( $field['id'] ) ) ? $field['id'] : '';

      /* Get the label for the current setting */
      $label = ( isset( $field['label'] ) ) ? $field['label'] : $this->prettify_words( $id );

      /* Get the value for the current setting */
      $value = ( isset( $this->site_settings[$id] ) ) ? $this->site_settings[$id ] : '';

      echo '<tr>';

      if ( $field['type'] == 'divider' )
      {
        /**
         * divider
         * ------------------------------------------------------------------------
         * @param $label | string
         * ------------------------------------------------------------------------ */
        echo '<td class="divider" colspan="2">' . $field['label'] . '</td>';
      }
      else
      {

        echo '<th>';
        echo '  <label for="' . $fields_name . '[' . $id . ']">' . $label . '</label>';
        echo '</th>';
        echo '<td>';

        switch ( $field['type'] )
        {

          /**
           * text
           * ------------------------------------------------------------------------
           * @param id          | string
           * @param label       | string
           * @param description | string
           * ------------------------------------------------------------------------ */
          case 'text':
            echo '<input id="' . $fields_name . '[' . $id . ']" name="' . $fields_name
              . '[' . $id . ']" type="text"  placeholder="'
              , isset( $field['placeholder'] ) ? $field['placeholder'] : ''
              , '" value="' . $value . '" size="50">';
            break;

          /**
           * textarea
           * ------------------------------------------------------------------------
           * @param id          | string
           * @param label       | string
           * @param description | string
           * ------------------------------------------------------------------------ */
          case 'textarea':
            echo '<textarea id="' . $fields_name . '[' . $id . ']" name="' . $fields_name
              . '[' . $id . ']" cols="52" rows="4">' . $value . '</textarea>';
            break;

          /**
           * checkbox
           * ------------------------------------------------------------------------
           * @param id          | string
           * @param label       | string
           * @param description | string
           * ------------------------------------------------------------------------ */
          case 'checkbox':
            echo '<input type="checkbox" value="' . $id . '" id="' . $fields_name
              . '[' . $id . ']" name="' . $fields_name . '[' . $id . ']"'
              , $value ? ' checked' : '','>';
            break;

          /**
           * post_type_select
           * ------------------------------------------------------------------------
           * @param id          | string
           * @param label       | string
           * @param post_type   | string || array
           * @param description | string
           * ------------------------------------------------------------------------ */
          case 'post_type_select':

            $items = get_posts(  array ( 'post_type' => $field['post_type']
              , 'posts_per_page' => -1 )  );
            echo '<select name="' . $fields_name . '[' . $id . ']" id="'
              . $fields_name . '[' . $id . ']">';
            echo '<option value="">Select&hellip;</option>';
            foreach (  $items as $item  )
            {
              $is_select = ( $item->ID == $value ) ? ' selected' : '';
              echo '  <option id="' . $fields_name . '[' . $id . ']" name="'
                . $fields_name . '[' . $id . ']" value="' . $item->ID . '"'
                .  $is_select . '>' . $item->post_title . '</option>';
            }
            echo '</select>';
            break;

          /* default */
          default:
            echo '<span style="color: red;">Sorry, the type allocated for this input is not valid.</span>';
            break;

        } // end switch

        /* Render the setting description if possible */
        if ( isset( $field['description'] ) )
        {
          echo '<p><span class="description">' . $field['description'] . '</span></p>';
        }
        echo '</td>';
      }
      echo '</tr>';
    } // end foreach

    echo '</table>';
    echo '<p class="submit">';
    echo '  <input type="submit" class="button-primary" value="Save Changes">';
    echo '  <a href="./" class="button">Cancel</a>';
    echo '</p>';
    echo '</form>';
    echo '</div>';
  }

  /**
   * Site Settings Validate
   * ------------------------------------------------------------------------
   * site_settings_validate()
   * @param  $input | array
   * @return $input | array
   *
   * Sanitize and validate input. Accepts an array, return a sanitized array.
   * ------------------------------------------------------------------------ */
  public function site_settings_validate( $input )
  {
    /* List the settings to be saved here: */
    foreach ( $this->fields as $field )
    {
      if ( isset( $field['id'] ) && $field['type'] != 'divider' )
      {
        $field['id'] = wp_filter_nohtml_kses( $field['id'] );
      }
    }
    return $input;
  }

  /**
   * Prettify words
   * ------------------------------------------------------------------------
   * prettify_words()
   * @param  $words | string
   * @return string
   *
   * Creates a pretty version of a string, like
   * a pug version of a dog.
   * ------------------------------------------------------------------------ */
  public function prettify_words( $words )
  {
    return ucwords( str_replace( '_', ' ', $words ) );
  }

  /**
   * Uglify words
   * ------------------------------------------------------------------------
   * uglify_words()
   * @param  $words | string
   * @return string
   *
   * creates a url firendly version of the given string .
   * ------------------------------------------------------------------------ */
  public function uglify_words( $words )
  {
    return strToLower( str_replace( ' ', '_', $words ) );
  }
}