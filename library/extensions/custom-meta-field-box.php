<?php
/**
 * Custom Meta Field Box
 * ------------------------------------------------------------------------
 * custom-meta-field-box.php
 * @version 2.1 | April 1st 2013
 * @package lt3
 * @author  Beau Charman | @beaucharman | http://beaucharman.me
 * @link    https://github.com/beaucharman/lt3
 * @license MIT license
 *
 * To declare a custom meta field box, simply create a new instance of the
 * LT3_Custom_Meta_Field_Box class.
 *
 * Configuration guide:
 * https://github.com/beaucharman/wordpress-custom-meta-field-boxes
 *
 * For more information about registering custom meta field boxes:
 * http://codex.wordpress.org/Function_Reference/add_meta_box
 */

/* ------------------------------------------------------------------------
   Custom Meta Field Box class
   ------------------------------------------------------------------------ */
class LT3_Custom_Meta_Field_Box
{
  protected $cmfb;
  protected $id;
  protected $title;
  protected $post_type;
  protected $context;
  protected $priority;
  protected $fields;

  /**
   * Class constructor
   * ------------------------------------------------------------------------
   * __construct()
   * @param  $cmfb | array
   * ------------------------------------------------------------------------ */
  function __construct( $cmfb )
  {
    /* Set class values */
    $this->cmfb      = $cmfb;
    $this->id        = $this->uglify_words( '_cmfb_'. $this->cmfb['id'] );
    $this->title     = ( isset( $this->cmfb['title'] ) )
      ? $this->cmfb['title'] : $this->prettify_words( $this->cmfb['id'] );
    $this->post_type = ( isset( $this->cmfb['post_type'] ) )
      ? $this->cmfb['post_type'] : 'post';
    $this->context   = ( isset( $this->cmfb['context'] ) )
      ? $this->cmfb['context']   : 'advanced';
    $this->priority  = ( isset( $this->cmfb['priority'] ) )
      ? $this->cmfb['priority']  : 'default';
    $this->fields    = $this->cmfb['fields'];

    /* Magic */
    add_action( 'add_meta_boxes', array(  &$this, 'add_custom_meta_field_box' ) );
    add_action( 'save_post', array(  &$this, 'save_data' ) );
  }

  /**
   * Add custom meta field box
   * ------------------------------------------------------------------------
   * add_custom_meta_field_box()
   * ------------------------------------------------------------------------ */
  public function add_custom_meta_field_box()
  {
    add_meta_box(
      $this->id,
      $this->title,
      array( &$this, 'show_custom_meta_field_box' ),
      $this->post_type,
      $this->context,
      $this->priority
     );
  }

  /**
   * Show custom meta field box
   * ------------------------------------------------------------------------
   * show_custom_meta_field_box()
   * ------------------------------------------------------------------------ */
  public function show_custom_meta_field_box()
  {
    global $post;
    echo '<input type="hidden" name="custom_meta_fields_box_nonce" value="'
      . wp_create_nonce( basename( __FILE__ ) ) . '" />';
    echo '<ul class="lt3-form-container ' . $this->context . '">';

    foreach ( $this->fields as $field )
    {
      /* Get the field ID */
      $field_id = $this->get_field_id( $this->id, $field['id'] );

      /* Get the saved value, if there is one */
      $value = get_post_meta( $post->ID, $field_id, true );
      $value = ( $value ) ? $value : '';

      /* Get the label */
      $field_label = ( isset( $field['label'] ) )
        ? $field['label'] : $this->prettify_words( $field['id'] );

      echo '<li class="custom-field-container">';

      echo '<p class="label-container">';
      echo '  <label for="' . $field_id . '"><strong>' . $field_label . '</strong></label>';
      echo '</p>';

      echo '<p class="input-container">';

      /* Render required field */
      $field['type'] = ( isset( $field['type'] ) ) ? $field['type'] : '';

      switch( $field['type'] )
      {
        /**
         * text
         * ------------------------------------------------------------------------
         * @param type        | string
         * @param id          | string
         * @param label       | string | optional
         * @param description | string | optional
         * @param placeholder | string | optional
         * ------------------------------------------------------------------------ */
        case 'text':
          $field_placeholder = ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '';
          echo '<input type="text" name="'.$field_id.'" id="'
            .$field_id.'" placeholder="'.$field_placeholder.'" value="'.$value.'" size="50">';
          break;

        /**
         * textarea
         * ------------------------------------------------------------------------
         * @param type        | string
         * @param id          | string
         * @param label       | string | optional
         * @param description | text   | optional
         * ------------------------------------------------------------------------ */
        case 'textarea':
          echo '<textarea name="' . $field_id . '" id="' . $field_id . '">' . $value . '</textarea>';
          break;

        /**
         * checkbox
         * ------------------------------------------------------------------------
         * @param type        | string
         * @param id          | string
         * @param options     | array
         * @param label       | string | optional
         * @param description | text   | optional
         * ------------------------------------------------------------------------ */
        case 'checkbox':
          echo '<ul>';
          foreach( $field['options'] as $option => $label ):
            echo '<li>';
            echo '  <label for="' . $field_id . '[' . $option . ']">';
            echo '  <input type="checkbox" name="' . $field_id . '[' . $option . ']" id="'
              . $field_id . '[' . $option . ']" value="' . $option . '" ', isset( $value[$option] )
                ? ' checked' : '', ' />';
            echo '  &nbsp;' . $label . '</label>';
            echo '</li>';
          endforeach;
          echo '</ul>';
          break;

        /**
         * select
         * ------------------------------------------------------------------------
         * @param type         | string
         * @param id           | string
         * @param options      | array
         * @param label        | string | optional
         * @param null_option  | string | optional
         * @param description  | text   | optional
         * ------------------------------------------------------------------------ */
        case 'select':
          $field_null_label = ( isset( $field['null_option'] ) )
            ? $field['null_option'] : 'Select';
          echo '<select name="' . $field_id . '" id="' . $field_id . '">';
          echo '  <option value="">' . $field_null_label . '&hellip;</option>';
          foreach( $field['options'] as $option => $label ):
          echo '  <option value="' . $option . '" ', $value == $option
            ? ' selected' : '', '>' . $label . '</option>';
          endforeach;
          echo '</select>';
          break;

        /**
         * post_select
         * ------------------------------------------------------------------------
         * @param type         | string
         * @param id           | string
         * @param post_type    | string
         * @param label        | string | optional
         * @param null_option  | string | optional
         * @param description  | text   | optional
         * ------------------------------------------------------------------------ */
        case 'post_select':
          $items = get_posts( array(
            'post_type'      => $field['post_type'],
            'posts_per_page' => -1 )
           );

          if ( $items )
          {
            $field_null_label = ( isset( $field['null_option'] ) )
              ? $field['null_option'] : 'Select';
            echo '<select name="' . $field_id . '" id="' . $field_id . '">';
            echo '  <option value="">' . $field_null_label . '&hellip;</option>';
            foreach( $items as $item ):
              $is_select = ( in_array( $item->ID, $value ) ) ? ' checked' : '';
              $post_type_label = ( isset( $field['post_type'][1] ) && is_array( $field['post_type'] ) )
                ? ' <small>( ' . $item->post_type . ' )</small>' : '';
              echo '  <option value="' . $item->ID . '" ', $value == $item->ID
                ? ' selected' : '','>' . $item->post_title . $post_type_label . '</option>';
            endforeach;
            echo '</select>';
          }
          else
          {
            echo 'Sorry, there are currently no ' . $field['post_type'] . ' items to choose from.';
          }
          break;

        /**
         * term_select
         * ------------------------------------------------------------------------
         * @param type         | string
         * @param id           | string
         * @param taxonomy     | string
         * @param args         | array
         * @param label        | string | optional
         * @param null_option  | string | optional
         * @param description  | text   | optional
         * ------------------------------------------------------------------------ */
        case 'term_select':

          $field['args'] = ( isset( $field['args'] ) && is_array( $field['args'] ) )
            ? $field['args'] : array();

          $args = array_merge(
            array(
              'orderby'       => 'name',
              'order'         => 'ASC',
              'hide_empty'    => false
             ), $field['args']
           );

          $items = get_terms( $field['taxonomy'], $args );

          if ( $items )
          {
            $field_null_label = ( isset( $field['null_option'] ) )
              ? $field['null_option'] : 'Select';
            echo '<select name="' . $field_id . '" id="' . $field_id . '">';
            echo '  <option value="">' . $field_null_label . '&hellip;</option>';
            foreach( $items as $item ):
              $is_select = ( in_array( $item->term_id, $value ) ) ? ' checked' : '';
              echo '  <option value="' . $item->term_id . '" ', $value == $item->term_id
                ? ' selected' : '','>' . $item->name . '</option>';
            endforeach;
            echo '</select>';
          }
          else
          {
            echo 'Sorry, there are currently no '
              . lt3_prettify_words( $field['post_type'] )
              . ' items to choose from.';
          }
          break;

        /**
         * radio
         * ------------------------------------------------------------------------
         * @param type        | string
         * @param id          | string
         * @param options     | array
         * @param label       | string | optional
         * @param description | text   | optional
         * ------------------------------------------------------------------------ */
        case 'radio':
          echo '<ul>';
          foreach( $field['options'] as $option => $label ):
            echo '<li>';
            echo '  <label for="' . $option . '">';
            echo '  <input type="radio" name="' . $field_id . '" id="' . $option
              . '" value="' . $option . '" ', $value == $option ? ' checked' : '',' />';
            echo '  &nbsp;' . $label . '</label>';
            echo '</li>';
          endforeach;
          echo '</ul>';
          break;

        /**
         * post_checkbox
         * ------------------------------------------------------------------------
         * @param type        | string
         * @param id          | string
         * @param post_type   | string
         * @param label       | string | optional
         * @param description | string | optional
         * ------------------------------------------------------------------------ */
        case 'post_checkbox':
          $value = ( $value ) ? $value : array();
          $items = get_posts( array(
            'post_type'      => $field['post_type'],
            'posts_per_page' => -1 )
           );

          if ( $items )
          {
            echo '<ul>';
            foreach( $items as $item ):
              $is_select = ( in_array( $item->ID, $value ) ) ? ' checked' : '';
              $post_type_label = ( isset( $field['post_type'][1] ) && is_array( $field['post_type'] ) )
                ? ' <small>( ' . $item->post_type . ' )</small>' : '';
              echo '<li>';
              echo '  <label for="' . $field_id . '[' . $item->ID . ']">';
              echo '  <input type="checkbox" name="' . $field_id . '[' . $item->ID
                .']" id="'.$field_id.'['. $item->ID .']" value="'.$item->ID.'" '. $is_select .'>';
              echo '  &nbsp;'.$item->post_title . $post_type_label.'</label>';
              echo '</li>';
            endforeach;
            echo '</ul>';
          }
          else
          {
            echo 'Sorry, there are currently no '. lt3_prettify_words( $field['post_type'] )
              .' items to choose from.';
          }
          break;

        /**
         * file
         * ------------------------------------------------------------------------
         * @param type        | string
         * @param id          | string
         * @param label       | string | optional
         * @param description | string | optional
         * @param placeholder | string | optional
         * ------------------------------------------------------------------------ */
        case 'file':
          $field_placeholder = ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '';
          echo '<input name="'.$field_id.'" id="'.$field_id.'" type="text" placeholder="'
            .$field_placeholder.'" class="custom_upload_file" value="'.$value.'" size="100" />
            <input class="custom_upload_file_button button" type="button" value="Choose File" />
            <br><small><a href="#" class="custom_clear_file_button">Remove File</a></small>';
          ?>
            <script>
            jQuery(function($) {
              $('.custom_upload_file_button').click(function() {
                $formField = $(this).siblings('.custom_upload_file');
                tb_show('Select a File', 'media-upload.php?type=image&TB_iframe=true');
                window.send_to_editor = function($html) {
                 $fileUrl = $($html).attr('href');
                 $formField.val($fileUrl);
                 tb_remove();
                };
                return false;
              } );
              $('.custom_clear_file_button').click(function() {
                $(this).parent().siblings('.custom_upload_file').val('');
                return false;
              });
            });
            </script>
          <?php
          break;

        /* default */
        default:
          echo '<p><span style="color: red;">Sorry, '
            . 'the type allocated for this input is not valid.</span></p>';
          break;
      }

      echo '</p>';

      /* Display the description */
      if ( isset( $field['description'] ) )
      {
        echo '<p class="description">'.$field['description'].'</p>';
      }

      echo '</li>';
    }
    echo '</ul>';
  }

  /**
   * Get field id
   * ------------------------------------------------------------------------
   * get_field_id()
   * @param $box_id       | string
   * @param $field_id     | string
   * @return the field id | string
   *
   * Get the field id to use throughout class
   * ------------------------------------------------------------------------ */
  public function get_field_id( $box_id, $field_id )
  {
    return $this->uglify_words( $box_id . '_' . $field_id );
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
   * creates a url firendly version of the given string.
   * ------------------------------------------------------------------------ */
  public function uglify_words( $words )
  {
    return strToLower( str_replace( ' ', '_', $words ) );
  }

  /**
   * Save data
   * ------------------------------------------------------------------------
   * save_data()
   * @param $post_id | integer
   * @return null
   * ------------------------------------------------------------------------ */
  public function save_data( $post_id )
  {
    if (  isset(  $_POST['custom_meta_fields_box_nonce']  )  )
    {
      if (  !wp_verify_nonce(  $_POST['custom_meta_fields_box_nonce'], basename( __FILE__ )  )  )
      {
        return $post_id;
      }
      if (  defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE  )
      {
        return $post_id;
      }
      if (  isset( $_POST['post_type']  )  )
      {
        if ( 'page' == $_POST['post_type'] ) {
          if ( !current_user_can( 'edit_page', $post_id ) )
          {
            return $post_id;
          }
        }
      }
      elseif ( !current_user_can( 'edit_post', $post_id ) )
      {
        return $post_id;
      }
      foreach ( $this->fields as $field )
      {
        $field_id = $this->get_field_id(  $this->id, $field['id']  );
        if (  $field_id && isset( $_POST[$field_id] ) )
        {
          $old = get_post_meta( $post_id, $field_id, true );
          $new = $_POST[$field_id];
          if ( $new && $new != $old )
          {
            update_post_meta( $post_id, $field_id, $new );
          }
          elseif ( '' == $new && $old )
          {
            delete_post_meta( $post_id, $field_id, $old );
          }
        }
      }
    }
  }
}