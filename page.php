<?php
/**
 * Page
 * ------------------------------------------------------------------------
 * page.php
 * @version 2.0 | April 1st 2013
 * @package lt3
 * @author  Beau Charman | @beaucharman | http://beaucharman.me
 * @link    https://github.com/beaucharman/lt3
 * @license GNU http://www.gnu.org/licenses/lgpl.txt
 *
 * Page template.
 * ------------------------------------------------------------------------ */ ?>
<?php get_header(); ?>

  <?php if ( have_posts() ) : ?>

    <?php get_template_part( LT3_TEMPLATE_PARTS_PATH . '/loop', 'page' ); ?>

  <?php else : ?>

    <?php lt3_get_message( 'Not Found' ); ?>

  <?php endif; ?>

<?php get_footer(); ?>