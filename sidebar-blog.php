<?php
/**
 * Sidebar for Blog areas.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Direct 1.0
 */
?>
<div class="grid_4">
<div id="secondary">
<?php
/* When we call the dynamic_sidebar() function, it'll spit out
 * the widgets for that widget area. If it instead returns false,
 * then the sidebar simply doesn't exist, so we'll hard-code in
 * some default sidebar stuff just in case.
 */
if ( ! dynamic_sidebar( 'blog' ) ) :
?>
<div class="block widget_archive">
    <h3 class="title"><span class="spacer"><?php _e( 'Archives', 'progo' ); ?></span></h3>
    <div class="inside">
    <ul>
        <?php wp_get_archives( 'type=monthly' ); ?>
    </ul>
    </div>
</div>
<?php endif; // end primary widget area ?>
</div>
</div>