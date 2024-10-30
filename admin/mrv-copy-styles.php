<?php

final class MetaRankerCopyStyles
{
	/**
	 * Singleton
	 */
	private function __construct()
	{
		// Nope
	}

	/**
	 * Singleton
	 */
	public static function init()
	{
		static $self = null;

		if (null === $self) {
			$self = new self;
		}

		add_action('admin_menu', array($self, 'add_admin_menu'),50);
	}

	/**
	 * Add menu page to the admin dashboard
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_admin_menu($context)
	{
		$this->hook_name = add_submenu_page('edit.php?post_type=meta-ranker', __('Copy Styles', 'meta-ranker'), __('Copy Styles', 'meta-ranker'), 'manage_options', 'copy-styles', array($this, 'render'));
	}

    /**
     * Render the menu page
     *
     * @internal Callback.
     */
    public function render($page_data)
    {

            $args = array(
                'post_type' => 'meta-ranker',
                'posts_per_page' => -1,
            );
            $posts = get_posts($args);
            $options = array();
            echo '<form id="mrv_styles_form" class="mrv-form" action="" method="post">';
            echo '<select id="mrv_styles_from" class="mrv-select" name="mrv_styles_from">  <optgroup label="--Select List to copy styles from--"> ';
            foreach ( $posts as $post ) {
                $post_id1 = $post->ID;
                $post_name = $post->post_name;
                echo '<option value=' . $post_id1 . '>' . $post_name . '</option>';
            }
            echo '</optgroup></select>';
            echo '<button id="mrv_save_style" class="mrv-button">Copy Style to</button>';
            echo '                         
            <select id="mrv_styles_to" class="mrv-select" name="mrv_styles_to"><optgroup label="--Select List to apply styles to--"> ';
            foreach ( $posts as $post ) {
                $post_id2 = $post->ID;
                $post_name = $post->post_name;
                echo '<option value=' . $post_id2 . '>' . $post_name . '</option>';
            }
            echo '</optgroup></select></form> 
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
        document.getElementById("mrv_styles_form").addEventListener("submit", function(event) {
            event.preventDefault();
            
            // Delay form submission by 1 second
            setTimeout(function() {
                document.getElementById("mrv_styles_form").submit();
            }, 1000);
        });

        document.getElementById("mrv_save_style").addEventListener("click", function() {
            Swal.fire({
                title: "Styles Copied!",
                icon: "success",
            });
        });
        </script>';

            if (!empty($_POST['mrv_styles_from']) && !empty($_POST['mrv_styles_to'])) {
                $mrv_styles_from = sanitize_text_field($_POST['mrv_styles_from']);
                $mrv_styles_to = sanitize_text_field($_POST['mrv_styles_to']);
            
                $style_from = get_post_meta($mrv_styles_from, 'mrv_post_settinga', true);
                $style_to = get_post_meta($mrv_styles_to, 'mrv_post_settinga', true);
            
                $ignoreKeys = [
                    'mrv-item',
                    'item-title',
                    'item-media-image',
                    'item-media-type',
                    'item-desc',
                    'url',
                    'alt',
                    'title',
                    'description',
                    'thumbnail',
                    'item-media-youtube',
                    'id',
                    'height',
                    'width'
                ];
            
                function copy_styles(&$array_to, $array_from, $ignoreKeys) {
                    foreach ($array_to as $key => &$value) {
                        if (is_array($value)) {
                            if(isset($array_from[$key]) && is_array($array_from[$key])) {
                                copy_styles($value, $array_from[$key], $ignoreKeys);
                            }
                        } else if (!in_array($key, $ignoreKeys) && isset($array_from[$key])) {
                            $value = $array_from[$key];
                        }
                    }
                }
                
                copy_styles($style_to, $style_from, $ignoreKeys);
            
                update_post_meta($mrv_styles_to, 'mrv_post_settinga', $style_to);
            }
        
            echo '<div id="mrv_iframe_container" class="mrv-iframe-container">';
            echo '<div><iframe id="iframe_from" class="mrv-iframe" src="" width="400" height="400"></iframe></div>';
            echo '<div><iframe id="iframe_to" class="mrv-iframe" src="" width="400" height="400"></iframe></div>';
            echo '</div>';

            // Include jQuery code to change the iframe src on select change
            echo '<script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#mrv_styles_from, #mrv_styles_to").change(function() {
                        var postId = $(this).val();
                        var iframeId = $(this).attr("id") === "mrv_styles_from" ? "iframe_from" : "iframe_to";
                        $.ajax({
                            url: "'.admin_url('admin-ajax.php').'",
                            type: "POST",
                            data: {
                                action: "get_permalink",
                                post_id: postId
                            },
                            success: function(response){
                                $("#"+iframeId).attr("src", response);
                            }
                        });
                    });
                });
            </script>';
    }

}



// Initialize the Singleton.
MetaRankerCopyStyles::init();