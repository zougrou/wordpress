<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/admin
 * @author     WebToffee <support@webtoffee.com>
 */
class Wp_Migration_Duplicator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/*
	 * module list, Module folder and main file must be same as that of module name
	 * Please check the `register_modules` method for more details
	 */
	public static $modules=array(
		'export',
		'import',
		'backups',
		'uninstall-feedback',
	);

	public static $existing_modules=array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	* @since 1.1.2
	* Admin page
	*/
	public function admin_settings_page()
	{
		// save settings
		include WT_MGDP_PLUGIN_PATH.'/admin/partials/wp-migration-duplicator-admin-display.php';
	}

	/**
	 * Generate tab head for settings page.
	 * method will translate the string to current language
	 * @since     1.1.2
	 */
	public static function generate_settings_tabhead($title_arr,$type="plugin")
	{	
		$out_arr=apply_filters("wt_mgdp_".$type."_settings_tabhead",$title_arr);
		foreach($out_arr as $k=>$v)
		{			
			if(is_array($v))
			{
				$v=(isset($v[2]) ? $v[2] : '').$v[0].' '.(isset($v[1]) ? $v[1] : '');
			}
		?>
			<a class="nav-tab" href="#<?php echo $k;?>"><?php echo $v; ?></a>
		<?php
		}
	}

	/**
	* @since 1.1.2
	* Admin menu hook
	*/
	public function admin_menu()
	{
		$menus=array(
			array(
				'menu',
				__('WordPress Migration','wp-migration-duplicator'),
				__('WordPress Migration','wp-migration-duplicator'),
				'manage_options',
				$this->plugin_name,
				array($this,'admin_settings_page'),
				'dashicons-image-rotate-left',
				56
			)
		);
		$menus=apply_filters('wt_mgdp_admin_menu',$menus);
		if(count($menus)>0)
		{
			foreach($menus as $menu)
			{
				if($menu[0]=='submenu')
				{
					add_submenu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6]);
				}else
				{
					add_menu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6],$menu[7]);	
				}
			}
		}

		if(function_exists('remove_submenu_page')){
			//remove_submenu_page(WF_PKLIST_POST_TYPE,WF_PKLIST_POST_TYPE);
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Migration_Duplicator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Migration_Duplicator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-migration-duplicator-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Migration_Duplicator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Migration_Duplicator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-migration-duplicator-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	* @since 1.1.2
	* Registers modules: public+admin	 
	*/
	public function admin_modules()
	{ 
		$wt_mgdp_admin_modules=get_option('wt_mgdp_admin_modules');
		if($wt_mgdp_admin_modules===false)
		{
			$wt_mgdp_admin_modules=array();
		}
		foreach (self::$modules as $module) //loop through module list and include its file
		{
			$is_active=1;
			if(isset($wt_mgdp_admin_modules[$module]))
			{
				$is_active=$wt_mgdp_admin_modules[$module]; //checking module status
			}else
			{
				$wt_mgdp_admin_modules[$module]=1; //default status is active
			}
			$module_file=plugin_dir_path( __FILE__ )."modules/$module/$module.php";
			if(file_exists($module_file) && $is_active==1)
			{
				self::$existing_modules[]=$module; //this is for module_exits checking
				require_once $module_file;
			}else
			{
				$wt_mgdp_admin_modules[$module]=0;	
			}
		}
		$out=array();
		foreach($wt_mgdp_admin_modules as $k=>$m)
		{
			if(in_array($k,self::$modules))
			{
				$out[$k]=$m;
			}
		}
		update_option('wt_mgdp_admin_modules',$out);
	}

	/**
	* @since 1.1.2
	* Sanitize input data 
	*/
	public static function sanitize_array($arr)
	{
		foreach ($arr as $key => $value)
		{
			$arr[$key]=sanitize_text_field($value);
		}
		return $arr;
	}

	/**
	 * @since 1.1.2 
	 * Envelope settings tab content with tab div.
	 * relative path is not acceptable in view file
	 */
	public static function envelope_settings_tabcontent($target_id,$view_file="",$html="",$variables=array(),$need_submit_btn=0)
	{
		extract($variables);
	?>
		<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
			<?php
			if($view_file!="" && file_exists($view_file))
			{
				include_once $view_file;
			}else
			{
				echo $html;
			}
			?>
			<?php 
			if($need_submit_btn==1)
			{
				include WT_MGDP_PLUGIN_PATH."admin/views/admin-settings-save-button.php";
			}
			?>
		</div>
	<?php
	}

	/**
	*  	Download file via a nonce URL
	*	@since 1.1.6
	*/
	public function download_file()
	{
		if(isset($_GET['wt_mgdp_download']))
		{
			if(Wt_Mgdp_Sh::check_write_access(WT_MGDP_POST_TYPE)) /* check nonce and role */
			{
				$file_name=(isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '');
				if($file_name!="")
				{
					$file_arr=explode(".", $file_name);
					$file_ext=end($file_arr);
					if($file_ext=='zip') /* only zip files */
					{
						$file_path=Wp_Migration_Duplicator::$backup_dir.'/'.$file_name;
						if(file_exists($file_path)) /* check existence of file */
						{
							
							header('Pragma: public');
						    header('Expires: 0');
						    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						    header('Cache-Control: private', false);
						    header('Content-Transfer-Encoding: binary');
						    header('Content-Disposition: attachment; filename="'.$file_name.'";');
						    header('Content-Type: application/zip');
						    header('Content-Length: '.filesize($file_path));

						    $chunk_size=1024 * 1024;
						    $handle=@fopen($file_path, 'rb');
						    while(!feof($handle))
						    {
						        $buffer = fread($handle, $chunk_size);
						        echo $buffer;
						        ob_flush();
						        flush();
						    }
						    fclose($handle);
						    exit();

						}
					}
				}	
			}
		}
	}

	/**
	*  	Generate nonce URL for backup file
	*	@since 1.1.6
	*	@param string $file_name name of the file to be downloaded
	*/
	public static function generate_backup_file_url($file_name)
	{
		return wp_nonce_url(admin_url('admin.php?wt_mgdp_download=true&file='.$file_name), WT_MGDP_POST_TYPE);
	}
}
