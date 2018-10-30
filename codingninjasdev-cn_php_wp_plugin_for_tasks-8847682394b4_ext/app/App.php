<?php
namespace codingninjas_ext_v1;

use \Exception;

class App
{
	const FREELANCER_POST_TYPE 		= 'freelancer';
	const FREELANCER_META_ID 		= 'freelancer_id';
	const FREELANCER_SELECT_ID 		= 'freelancer_select';
	const TASKS_ROUTE				= 'tasks';
	
	private $required_plugins = array("codingninjasdev-cn_php_wp_plugin_for_tasks-8847682394b4/coding-ninjas.php");
		
	/**
	* Task 1.
	* Check required plugins
	*/
	function has_required_plugins()
	{
        if (empty($this->required_plugins)) {
            return true;
		}
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
				
        foreach ($this->required_plugins as $key => $required) {
            if (!in_array($required, $active_plugins) && !array_key_exists($required, $active_plugins))
                return false;
        }
        return true;
    }
	
	/**
	* Task 1.
	* Check whether coding-ninjas in installed and activated
	*/
	public function on_dependency_check()
	{
		if(!$this->has_required_plugins()){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( self::$main_file, true );
			$url = admin_url( 'plugins.php' );
			add_action( 'admin_notices', array($this, 'on_display_admin_notice' ));
		}
	}
	
    /**
	* Task 1.
    * Display notice in admin
    */
    public function on_display_admin_notice()
    {
        echo self::view(
            ['admin', 'notice.php'],
			['error_msg' => __('Coding Ninjas Tasks plugin is disabled. Please active it to use the Coding Ninjas Tasks Bugaudin Extension.', 'cne')]
        );
    }
	
	/**
     * Task 2.
	 * Init post type task
     */
    public function on_init_post_types()
    {
		$labels = array(
            'name'               	=> __( 'Freelancers', 'cne' ),
            'singular_name'      	=> __( 'Freelancer',  'cne' ),
            'menu_name'          	=> __( 'Freelancers', 'cne' ),
            'name_admin_bar'     	=> __( 'Freelancer',  'cne' ),
            'add_new'            	=> __( 'Add New', 'cne' ),
            'add_new_item'       	=> __( 'Add New Freelancer', 'cne' ),
            'new_item'           	=> __( 'New Freelancer', 'cne' ),
            'edit_item'          	=> __( 'Edit Freelancer', 'cne' ),
            'view_item'          	=> __( 'View Freelancer', 'cne' ),
            'all_items'          	=> __( 'All Freelancers', 'cne' ),
            'search_items'       	=> __( 'Search Freelancers', 'cne' ),
            'parent_item_colon'  	=> __( 'Parent Freelancers:', 'cne' ),
            'not_found'          	=> __( 'No freelancers found.', 'cne' ),
            'not_found_in_trash' 	=> __( 'No freelancers found in Trash.', 'cne' ),
			'featured_image' 	 	=> __( 'Avatar', 'cne' ),
			'set_featured_image' 	=> __( 'Set avatar', 'cne' ),
			'remove_featured_image' => __( 'Remove avatar', 'cne' ),
			'use_featured_image'    => __( 'Use as avatar', 'cne' )
        );
		
		$args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'freelancer' ),
            'menu_icon'            => 'dashicons-groups',
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'thumbnail' )
        );

        register_post_type( self::FREELANCER_POST_TYPE, $args );
    }
	
	/**
	* Task 3.
	* Add freelancer metabox to the add new task page
	*/
	public function on_add_meta_boxes()
    {
		add_meta_box(
			self::FREELANCER_META_ID, 								// Freelancer meta ID
			__( 'Freelancer', 'cne' ), 								// Metabox title
			[self::class, 'output_freelancers_select_html'],   		// callback to render metabox html
			\codingninjas\Task::POST_TYPE,  						// Task post type
			'side'													// display on a sidebar
		);
    }
 
 	/**
	* Task 3.
	* Render freelancer metabox html for the create new task page
	*/
	public static function get_freelancers_select_html($post_id = 0, $tasks_max = 0)
    {
        $value = get_post_meta($post_id, self::FREELANCER_META_ID, true);
		$freelancers = self::get_freelancers($tasks_max);
		$def_val = __('Select freelancer...', 'cn');
		
		return self::view(
            ['select', 'freelancer_select.php'],
            [
				'value'			=> $value,
                'freelancers'	=> $freelancers,
				'def_val'		=> $def_val,
				'select_id'		=> self::FREELANCER_SELECT_ID				
            ]
        );
    }
	
	public static function output_freelancers_select_html($post)
    {
        echo self::get_freelancers_select_html($post->ID);
    }
	
	/**
	* Task 3.
	* Save freelancer id for the current tasks
	*/
    public function on_save_task_freelancer($post_id)
    {
        if (array_key_exists(self::FREELANCER_SELECT_ID, $_POST)) {
            update_post_meta(
                $post_id,
                self::FREELANCER_META_ID,
                $_POST[self::FREELANCER_SELECT_ID]
            );
        }
    }
	
	/**
	* Task 4.
	* Change title for the pages with 'Page not Found'.
	* @param $title
	* @return string
	*/
	function on_change_404_title($title) {
		// change title for any page without title found
		// instead of hardcoding the 'Tasks' and 'Dashboard' pages route only
		if(is_404()) {
			$pagename = get_query_var('pagename');
			$title['title'] = ucfirst($pagename);
		}
		return apply_filters ('cne_on_change_404_title', $title, $this);
	}
	
	/**
	* Task 5.
	* Insert Freelancers column title to the tasks table on the page Tasks
	* @param array      $cols
	* @return mixed
	*/
	public static function on_insert_freelancers_title( $cols ) {
		array_splice($cols, 2, 0, [__('Freelancer', 'cn')]);
		return apply_filters ('cne_on_insert_freelancers_title', $cols, $this);
	}

	/**
	* Task 5.
	* Insert Freelancers column values to the tasks table on the page Tasks
	* @param array      $cols
	* @return mixed
	*/	
	public static function on_insert_freelancers_col($cols ) {
		$task_id = str_replace("#", "", $cols[0]);
		$name = '';
		$freelancer_id = get_post_meta($task_id, self::FREELANCER_META_ID, true);
		if($freelancer_id){
			$name = get_the_title( $freelancer_id );
		}
		if(!$name){
			$name = __('Not specified', 'cn');
		}
		array_splice($cols, 2, 0, $name);
		return apply_filters ('cne_on_insert_freelancers_col', $cols, $this);
	}
	
	/**
	 * @param array      $array
	 * @param int|string $position
	 * @param mixed      $insert
	 */
	public static function array_insert(&$array, $position, $insert)
	{
		$array = array_merge(
			array_slice($array, 0, $pos),
			$insert,
			array_slice($array, $pos)
		);
	}
	
	/**
	* Task 7.
	* Add menu item and popup html to the Tasks page
	* @return mixed
	*/
	public function on_add_new_task_menu_item($menu) {
		if(!self::is_tasks_route()) {
			return $menu;
		}
		$popupLink = self::view(
            ['popup', 'popup-link.php']
        );
		$freelancer_select = self::get_freelancers_select_html(0, 2);
		
		$popup = self::view(
            ['popup', 'popup.php'],
            [
                'freelancer_select' => $freelancer_select,
				'task_title' => __('Task title', 'cn'),
				'freelancer_title' => __('Freelancer', 'cn'),
				'add_button_title' => __('Add', 'cn')
            ]
        );
		$res = $menu.$popupLink.$popup;
		return apply_filters ('cne_on_add_new_task_menu_item', $res, $this);
	}
	
	/**
	* Task 7.
	* Ajax request handler for save task with freelancerId id action
	*/
	public function add_task_action() {
		global $wpdb;
		$taskTitle = $_POST['taskTitle'];
		$freelancerId = intval($_POST['freelancerId']);
		
		// Create post object
		$task = array(
			'post_title'    => $taskTitle,
			'post_status'   => 'publish',
			'post_type' => \codingninjas\Task::POST_TYPE,
			'meta_input'   => array(
				self::FREELANCER_META_ID => $freelancerId,
			)
		);

		// Insert the post into the database
		wp_insert_post( $task );

		echo __('Success!', 'cn');

		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	* Task 7.
	* Return freelancers with maximum assigned tasks specified in $max_tasks.
	* If $max_tasks is 0 the function will return all freelancers.
	* @param $max_tasks
    * @return mixed
	*/
	public static function get_freelancers($max_tasks = 0) {
		global $wpdb;
		if($max_tasks <= 0){
			$max_tasks = PHP_INT_MAX;
		}
		$result = $wpdb->get_results( $wpdb->prepare( 
			"
			SELECT fr.id, 
				   fr.post_title 
			FROM   (SELECT freelancers.id, 
						   freelancers.post_title 
					FROM   $wpdb->posts freelancers 
					WHERE  freelancers.post_status = 'publish' 
						   AND freelancers.post_type = %s) AS fr 
				   LEFT JOIN (SELECT meta_value, 
									 Count(meta_value) AS tasks_count 
							  FROM   $wpdb->postmeta
							  WHERE  meta_key = %s 
							  GROUP  BY meta_value) AS frc 
						  ON fr.id = frc.meta_value 
			WHERE  frc.tasks_count <= %d
				   OR ISNULL( frc.tasks_count )			
			",
			self::FREELANCER_POST_TYPE,
			self::FREELANCER_META_ID,
			$max_tasks
		) );
		$freelancers = array();
		foreach ( $result as $r ) 
		{
			$freelancers += array($r->id => $r->post_title);
		}
		return apply_filters ('cne_get_freelancers', $freelancers, $this);
	}
 
	/**
	* Task 8.
	* Handler for cn_dashboard shortcode
	* @return mixed
	*/
	function cne_dashboard_func($atts) {
		$a = shortcode_atts( array(
			'show_info' => true
		), $atts );
		$show_info = ($a['show_info'] == 'true');

		$freelancers_count = wp_count_posts(self::FREELANCER_POST_TYPE)->publish;
		$tasks_count = wp_count_posts(\codingninjas\Task::POST_TYPE)->publish;
		$data = [
			[
				'panel_class'  	=> 'panel-primary',
				'panel_icon'  	=> 'fa-group',
				'items_count' 			=> $freelancers_count,
				'widget_item_name'		=> _n( 'freelancer', 'freelancers', $freelancers_count, 'cne' )
			],
			[
				'panel_class'  	=> 'panel-green',
				'panel_icon'  	=> 'fa-tasks',
				'items_count' 			=> $tasks_count,
				'widget_item_name'		=> _n( 'task', 'tasks', $tasks_count, 'cne' )
			]
		];
		$freelancers_widget = self::view(
            ['widget', 'dashboard-widget.php'],
            [
				'show_info' => $show_info,
                'data' => $data			
            ]
        );
		return apply_filters ('cne_get_freelancers_widget', $freelancers_widget, $this);
	}
	
	/**
     * Init wp actions
     */
    private function initActions()
    {
		add_action( 'init', array($this, 'on_dependency_check'));
		add_action( 'init', array($this, 'on_init_post_types'));
		add_action('add_meta_boxes', array($this, 'on_add_meta_boxes'));
		add_action('save_post', array($this, 'on_save_task_freelancer'));
		add_action( 'wp_enqueue_scripts', array($this, 'onInitScripts'), 21);
		add_action( 'wp_enqueue_scripts', array($this, 'onInitStyles'), 21);
		add_action( 'wp_ajax_add_task_action', array($this, 'add_task_action') );
	}
	
	/**
     * Init wp filters
     */
    private function initFilters()
    {
		add_filter( 'cn_tasks_thead_cols', array($this, 'on_insert_freelancers_title'));
		add_filter( 'cn_tasks_tbody_row_cols', array($this, 'on_insert_freelancers_col'));
		add_filter( 'cn_page_menu_html', array($this, 'on_add_new_task_menu_item'));
		add_filter( 'document_title_parts', array($this, 'on_change_404_title'));
	}
	
	/**
     * Init wp shortcodes
     */
    private function initShortcodes()
    {
		add_shortcode( 'cn_dashboard', array($this, 'cne_dashboard_func' ));
	}
	
	/**
     * Init js scripts
     */
    public function onInitScripts()
    {
        wp_enqueue_script('jquery');

		// use dynatable for task 6 - search, sort and pagination for the tasks column
        wp_enqueue_script(
            'dynatable',
            self::$app_url.'/vendor/dynatable/jquery.dynatable.js',
            ['jquery'],
            '3.3.7',
            true
        );
		
		wp_enqueue_script(
            'cn-ext-js',
            self::$app_url.'/assets/js/cn-ext-js.js',
            ['jquery'],
            '3.3.7',
            true
        );
		
		// register ajax url to use in js file
		wp_localize_script( 'cn-ext-js', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
		wp_localize_script( 'cn-ext-js', 'jsRes',
            array( 'add_new_task_title' => __('Add new task', 'cne' )) );
		
		// enqueue jquery-ui to display popup for task 7
		wp_enqueue_script(
            'jquery-ui-js',
            'https://code.jquery.com/ui/1.12.1/jquery-ui.js',
            ['jquery'],
            '3.3.7',
            true
        );
    }

    /**
     * Init styles
     */
    public function onInitStyles()
    {
        wp_enqueue_style(
            'dynatable',
            self::$app_url.'/vendor/dynatable/jquery.dynatable.css'
        );
		wp_enqueue_style(
            'cn-ext',
            self::$app_url.'/assets/css/cn-ext.css'
        );
		wp_enqueue_style(
            'jquery-ui-css',
            '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'
        );
    }
	
	/** Check is current page is the Tasks page
     * @return bool
     */
	private static function is_tasks_route()
	{
		return self::$route == self::TASKS_ROUTE;
	}
	
	/**
     * Instance of App
     * @var null
     */
    public static $instance = null;

    /**
     * Plugin main file
     * @var
     */
    public static $main_file;

    /**
     * Path to app folder
     * @var string
     */
    public static $app_path;

    /**
     * Url to app folder
     * @var string
     */
    public static $app_url;
	
	/**
     * Current route
     * @var
     */
    public static $route;
	
	/**
     * App constructor.
     * @param $main_file
     */
    public function __construct($main_file)
    {
		self::$main_file = $main_file;
        self::$app_path = dirname ($main_file).'/app';
        self::$app_url = plugin_dir_url( $main_file ).'app';
		$this->initRoute();
		$this->initActions();
		$this->initFilters();
		$this->initShortcodes();
    }
	
	/** Run App
     * @param $main_file
     * @return App|null
     */
    public static function run($main_file)
    {
		if (!self::$instance) {
            self::$instance = new self($main_file);
        }

        return self::$instance;
    }
	
	/**
     * Get template path
     * @param string $file
     * @return string
     */
    private static function getViewPath($file = '')
    {
        $path = self::$app_path.'/views/'.$file;

        return $path;
    }

	/**
     * Render template
     * @param $path
     * @param array $params
     * @return string
     * @throws Exception
     */
    public static function view($path, $params = [])
    {
        if (is_array ($path)) {
            $path = implode('/', $path);
        }

        $file = self::getViewPath($path);

        if (!file_exists ($file)) {
            throw new Exception('View not found '.$file);
        }

        if ($params) {
            extract ($params);
        }

        ob_start ();

        include $file;

        $content = ob_get_clean ();

        return $content;
    }	
	
	/**
     * Init current route
     */
    private function initRoute()
    {
        $route = $_SERVER['REQUEST_URI'];
        $params =  $_SERVER['QUERY_STRING'];

        if ($params) {
            $route = str_replace ('?'.$params, '', $route);
        }

        $route = trim ($route, '/');

        $wp_home_url = home_url ();
        $components = parse_url($wp_home_url);
        $wp_instance_path = '';
        if (array_key_exists ('path', $components)) {
            $wp_instance_path = $components['path'];
            $wp_instance_path = trim ($wp_instance_path, '/');
        }

        if ($wp_instance_path) {
            $len = mb_strlen ($wp_instance_path);
            $route = substr($route, $len);
            $route = trim ($route, '/');
        }

        self::$route = $route;
    }
}