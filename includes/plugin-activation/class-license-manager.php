<?php

/**
 * MetaRankerLicenseManager
 *
 * Show the Terms & Conditions consent after activation.
 */
final class MetaRankerLicenseManager
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
		add_action('admin_init', array($self, 'setup'), PHP_INT_MAX, 0);
		add_action('admin_enqueue_scripts', array($self, 'enqueue_assets'));
	}

	/**
	 * Add menu page to the admin dashboard
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_admin_menu($context)
	{
		$activated = get_option('metaRankerActivated');

		if (!$activated) {
			$status ="";
			
			$status = MetaRankerRestApi::getActivationStatus('meta-ranker');
			
			if ('registered' === $status) {
				update_option('metaRankerActivated', 1);
			}
		}

		if (!get_option('metaRankerActivated')) {
			$this->hook_name = add_submenu_page('edit.php?post_type=meta-ranker', __('Plugin Activation', 'meta-ranker'), __('Plugin Activation', 'meta-ranker'), 'manage_options', 'metaranker-activation', array($this, 'render'));
		}
	}

	/**
	 * Route to this page on activation
	 *
	 * @internal Used as a callback.
	 */
	public function setup()
	{
		$run_setup = get_transient('metaRanker_init_activation') && !get_option('metaRankerActivated');

		if ($run_setup) {
			
			 if (delete_transient('metaRanker_init_activation')) {	
				wp_safe_redirect(admin_url('admin.php?page=metaranker-activation'));
				exit;
			 }
			
		}
	}

    /**
     * Render the menu page
     *
     * @internal Callback.
     */
    public function render($page_data)
    {
        $siteurl = get_site_url();
        $admin_email = get_option('admin_email');
       
        

?>
        <div class="wrap meta-ranker-activation-page">
				<div class="card-top">
				<img class="img" src="<?php echo esc_url(MRV_URL . 'assets/images/plugin-logo.png'); ?>" alt="Logo">
				<p id="messager" class="description">
                    <?= __('One more minute, please accept our Terms & Conditions!', 'meta-auth') ?>
                    <br>
                    You will be directed to connect your wallet to activate the plugin
                </p>

				<form method="POST" action="">
<!-- 
				
					<div class="form-group">
						<label>Email :
						<input id="registration_email" size="40" placeholder="      Email address" type="text"
							name="registration_email">
						</label>
					</div> -->
					<!-- <div class="form-group">
					<label>Wallet  :
						<input id="registration_wallet_address" size="40" placeholder="      Wallet address " type="text"
							name="registration_wallet_address">
						</label>
					</div> -->
					<br>
					<div class="form-group">
						<label>
							<input id="accept_tos" type="checkbox" name="accept_tos" value="1">
							<span>
								<?= sprintf(__('I agree to the %sTerms & Conditions%s.', 'meta-ranker'), '<a href="' . admin_url('edit.php?post_type=meta-ranker&page=meta-ranker-settings#tab=terms-of-use') . '" target="_blank">', '</a>') ?>
							</span>
						</label>
					</div>

					<div class="card-bottom">
						<button id="meta-plugin-activate-btn" class="button button-primary" type="submit" data-plugin="meta-ranker"><?= __('Activate', 'meta-ranker') ?></button>
						<a class="to-dashboard" href="<?= admin_url() ?>">&larr; <?= __('Back to dashboard', 'meta-ranker') ?></a>	
					</div>
                    <p class="permalink"><?= __('Make sure to use <b>Settings >> Permalinks >> Post name (/%postname%/)</b> before activating this plugin. ', 'meta-ranker') ?></p>
				</form>
			</div>
		</div>
<?php

    }

	/**
	 * Enqueue assets
	 *
	 * @internal Used as a callback.
	 */
	public function enqueue_assets($hook_name)
	{
		if (!isset($this->hook_name) || $hook_name !== $this->hook_name) {
			return;
		}

wp_add_inline_style('dashicons', '
		@media only screen and (max-width: 782px) {
			#messager.err{color:#f22424;padding:19.5px 10px;}#messager.ok{color:#11bd40;padding:19.5px 10px;}
			.img{width:100px;height:100px;padding:10px 10px;}
			.card-top{box-shadow: 2.5px 2.5px 5px 2.5px #C0C7CA;margin-top:32px;width:300px;height:479px;}
			.card-bottom{margin-top: 15px;display:flex;flex-direction:column;align-items:center;background-color:#C0C7CA;width:100%;}
			.permalink{color:red;font-size:15px;padding:10px 10px;}
			.notice,.updated{display:none !important}
			.wp-admin{background-color:#fff !important}
			.meta-ranker-activation-page{margin:0 !important}
			.meta-ranker-activation-page h1{font-weight:600;font-size:28px}
			.meta-ranker-activation-page form{display:flex;flex-direction:column;align-items:center;}
			.meta-ranker-activation-page form label{display:block;margin-bottom:2px}
			.meta-ranker-activation-page form input[type="email"]{width:100%;padding:10px 10px}
			.meta-ranker-activation-page form .button{padding:10px 10px;text-transform:uppercase;margin-bottom:12px;margin-top:12px;}
			.meta-ranker-activation-page form .to-dashboard{text-decoration:none}
			.meta-ranker-activation-page h1,.meta-ranker-activation-page p{padding:10px 10px;}
			.wp-admin #wpwrap{text-align:center}
			#wpwrap #wpcontent{display:flex;flex-direction:column;align-items:center;}
			#wpwrap ##wpbody-content{padding-bottom:0;float:none}
			#adminmenumain,#wpadminbar{}
			#wpfooter{display:none !important}
		}

		@media only screen and (min-width: 782px) {
			#messager.err{color:#f22424;padding:10px 10px;}#messager.ok{color:#11bd40;padding:10px 10px;}
			.img{width:100px;height:100px;padding:10px 10px;}
			.card-top{box-shadow: 2.5px 2.5px 5px 2.5px #C0C7CA;margin-top:32px;width:500px;height:424px;}
			.card-bottom{margin-top: 15px;display:flex;flex-direction:column;align-items:center;padding:10px 10px;background-color:#C0C7CA;width:480px;}
			.permalink{color:red;font-size:15px;padding:10px 10px;}
			.notice,.updated{display:none !important}
			.wp-admin{background-color:#fff !important}
			.meta-ranker-activation-page{margin:0 !important}
			.meta-ranker-activation-page h1{font-weight:600;font-size:28px}
			.meta-ranker-activation-page form{display:flex;flex-direction:column;align-items:center;}
			.meta-ranker-activation-page form label{display:block;margin-bottom:2px}
			.meta-ranker-activation-page form input[type="email"]{width:100%;padding:10px 10px}
			.meta-ranker-activation-page form .button{padding:10px 10px;text-transform:uppercase;margin-bottom:12px;margin-top:12px; width:250px;}
			.meta-ranker-activation-page form .to-dashboard{text-decoration:none}
			.meta-ranker-activation-page h1,.meta-ranker-activation-page p{padding:10px 10px;}
			.wp-admin #wpwrap{text-align:center}
			#wpwrap #wpcontent{display:flex;flex-direction:column;align-items:center;}
			#wpwrap ##wpbody-content{padding-bottom:0;float:none}
			#adminmenumain,#wpadminbar{}
			#wpfooter{display:none !important}
		}
        ');

		wp_localize_script(
			'jquery-core',
			'metaRanker',
			array(
				'nonce' => wp_create_nonce('m3t4L0k3r'),
				'ajaxURL' => admin_url('admin-ajax.php'),
				'adminURL' => admin_url(),
				'pluginVer' => MRV_VERSION,
				'pluginUri' => MRV_URL,
				'tosRequired' => __('You must accept our Terms & Conditions!', 'meta-ranker')
			)
		);
		$symbols = require MRV_PATH . 'assets/symbols.php';
        $testnets = require MRV_PATH . 'assets/testnets.php';

		wp_enqueue_script('admin', MRV_URL . 'assets/js/admin.js', [], MRV_VERSION, true);
		wp_localize_script('admin', 'networkInfo', array('symbols' => $symbols, 'testnets' => $testnets));  // Localize the first script
	}
}

// Initialize the Singleton.
MetaRankerLicenseManager::init();
