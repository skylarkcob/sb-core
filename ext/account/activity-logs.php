<?php
defined( 'ABSPATH' ) || exit;

class HTE_User_Activity_Logs {
	protected static $_instance = null;

	protected $table_name = 'user_activity_logs';
	public $table;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		global $pagenow;

		if ( 'admin-ajax.php' == $pagenow ) {
			return;
		}

		add_action( 'init', array( $this, 'init_action' ) );

		if ( is_admin() ) {
			$cur_page = HT_Admin()->get_plugin_page();

			if ( 'hocwp_theme' == $cur_page ) {
				$tab = $_GET['tab'] ?? '';

				if ( 'account' == $tab ) {
					$this->database_table_init();
				}
			}

			add_filter( 'user_row_actions', array( $this, 'user_row_actions_filter' ), 10, 2 );
			add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );
			add_filter( 'screen_settings', array( $this, 'screen_settings_filter' ), 10, 2 );
			add_filter( 'set-screen-option', array( $this, 'set_screen_option_filter' ), 10, 3 );
		}
	}

	public function set_screen_option_filter( $screen_option, $option, $value ) {
		if ( 'posts_per_page' == $option ) {
			if ( $value < 1 ) {
				$screen_option = 999999999999999999999;
			} else {
				$screen_option = $value;
			}
		}

		return $screen_option;
	}

	public function screen_settings_filter( $settings, $screen ) {
		if ( $screen instanceof WP_Screen && 'users_page_' . $this->table_name == $screen->base ) {
			add_screen_option( 'per_page', array(
				'label'   => __( 'Items per page', 'sb-core' ),
				'default' => 50,
				'option'  => 'posts_per_page'
			) );
		}

		return $settings;
	}

	public function admin_menu_action() {
		add_submenu_page( 'users.php', __( 'Users Activity Logs', 'sb-core' ), __( 'Activity Logs', 'sb-core' ), 'manage_options', $this->table_name, array(
			$this,
			'display_logs_callback'
		) );
	}

	public function display_logs_callback() {
		global $plugin_page;

		if ( ! ( $this->table instanceof HTE_User_Logs_Table ) ) {
			$this->table = new HTE_User_Logs_Table();
		}

		$this->table->process_bulk_action();
		$this->table->prepare_items();
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Users Activity Logs', 'sb-core' ); ?></h1>
            <hr class="wp-header-end"/>

			<?php settings_errors(); ?>
            <form method="post">
                <input type="hidden" name="page" value="<?php echo $plugin_page; ?>">
				<?php
				$this->table->search_box( __( 'Search', 'sb-core' ), 'search_id' );
				$this->table->display();
				?>
            </form>
        </div>
		<?php
	}

	public function user_row_actions_filter( $actions, $user ) {
		$actions['activity_logs'] = "<a class='activity-logs' href='" . admin_url( "users.php?&page=$this->table_name&amp;user=$user->ID" ) . "'>" . esc_html__( 'Activity Logs', 'sb-core' ) . "</a>";

		return $actions;
	}

	public function init_action() {
		if ( is_user_logged_in() ) {
			$this->insert( get_current_user_id() );
		}
	}

	public function database_table_init() {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_name;

		$sql = "ID bigint(20) unsigned NOT NULL auto_increment,
		        user_id bigint(20) unsigned NOT NULL default '0',
		        date datetime NOT NULL default '0000-00-00 00:00:00',
		        IP varchar(100) NOT NULL default '',
		        agent varchar(255) NOT NULL default '',
		        url text NOT NULL,
		        PRIMARY KEY (ID),
		        KEY user_id (user_id)";

		HT_Util()->create_database_table( $table_name, $sql );
	}

	public function insert( $args = array() ) {
		if ( is_numeric( $args ) ) {
			$args = array( 'user_id' => $args );
		}

		$id = HT()->get_value_in_array( $args, 'user_id' );

		if ( HT()->is_positive_number( $id ) ) {
			global $wpdb;

			$table_name = $wpdb->prefix . $this->table_name;

			if ( ! HT_util()->is_database_table_exists( $table_name ) ) {
				return;
			}

			$datetime = current_time( 'mysql' );
			$ip       = HT()->get_IP();
			$agent    = HT()->get_user_agent();
			$url      = HT_Util()->get_current_url( true );

			$sql = "INSERT INTO $table_name (user_id, date, IP, agent, url)";

			$sql .= " VALUES ('$id', '$datetime', '$ip', '$agent', '$url')";

			$wpdb->query( $sql );
		}
	}

	public function delete( $id ) {
		$res = false;

		if ( is_array( $id ) ) {
			foreach ( $id as $item ) {
				$res = $this->delete( $item );
			}

			return $res;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_name;

		return $wpdb->query( "DELETE FROM $table_name WHERE ID = " . $id );
	}

	public function get( $user_id = null, $number = false, $orderby = 'date', $order = 'desc', $where = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;

		$sql = "SELECT * FROM $table_name WHERE 1 = 1";

		if ( ! empty( $where ) ) {
			$sql .= ' AND ' . trim( $where );
		}

		if ( null != $user_id ) {
			if ( is_array( $user_id ) ) {
				$sql .= ' AND (';

				foreach ( $user_id as $id ) {
					$sql .= " user_id = '$id' OR";
				}

				$sql = trim( $sql, ' OR' );
				$sql .= ')';
			} elseif ( ! empty( $user_id ) ) {
				$sql .= " AND user_id = '$user_id'";
			}
		}

		$sql .= " ORDER BY $orderby " . strtoupper( $order );

		if ( is_numeric( $number ) ) {
			$sql .= ' LIMIT ' . $number;
		}

		return $wpdb->get_results( $sql );
	}
}

function HTE_User_Activity_Logs() {
	return HTE_User_Activity_Logs::instance();
}

HTE_User_Activity_Logs();

if ( ! class_exists( 'WP_List_Table' ) ) {
	load_template( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class HTE_User_Logs_Table extends WP_List_Table {
	function no_items() {
		_e( 'No logs found, dude.', 'sb-core' );
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return $item->ID;
			case 'user_id':
				return $item->user_id;
			case 'date':
				return $item->date;
			case 'ip':
				return $item->IP;
			case 'agent':
				return $item->agent;
			case 'url':
				return $item->url;
			default:
				return print_r( $item, true );
		}
	}

	function get_sortable_columns() {
		return array(
			'id'      => array( 'id', false ),
			'user_id' => array( 'user_id', false ),
			'date'    => array( 'date', false ),
			'ip'      => array( 'IP', false ),
			'agent'   => array( 'agent', false ),
			'url'     => array( 'url', false )
		);
	}

	function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'id'      => __( 'ID', 'sb-core' ),
			'user_id' => __( 'User ID', 'sb-core' ),
			'date'    => __( 'Date', 'sb-core' ),
			'ip'      => __( 'IP', 'sb-core' ),
			'agent'   => __( 'User Agent', 'sb-core' ),
			'url'     => __( 'URL', 'sbc-core' )
		);
	}

	function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'sb-core' )
		);
	}

	public function column_cb( $item ) {
		return sprintf(
			'<label class="label-covers-full-cell" for="user_log_id_%1$s"><span class="screen-reader-text">%2$s</span></label>' .
			'<input type="checkbox" name="user_log_id[]" id="user_log_id_%1$s" value="%1$s" /><span class="spinner"></span>',
			esc_attr( $item->ID ),
			sprintf( __( 'Select %s', 'sb-core' ), $item->ID )
		);
	}

	public function process_bulk_action() {
		$action      = $this->current_action();
		$request_ids = isset( $_REQUEST['user_log_id'] ) ? wp_parse_id_list( wp_unslash( $_REQUEST['user_log_id'] ) ) : array();

		if ( empty( $request_ids ) ) {
			return;
		}

		$count    = 0;
		$failures = 0;

		switch ( $action ) {
			case 'delete':
				$res = HTE_User_Activity_Logs()->delete( $request_ids );

				if ( $res ) {
					$count = count( $request_ids );
				} else {
					$failures = count( $request_ids );
				}

				if ( $failures ) {
					add_settings_error(
						'bulk_action',
						'bulk_action',
						sprintf(
						/* translators: %d: Number of user activity logs. */
							_n(
								'%d user log failed to delete.',
								'%d user logs failed to delete.',
								$failures,
								'sb-core'
							),
							$failures
						)
					);
				}

				if ( $count ) {
					add_settings_error(
						'bulk_action',
						'bulk_action',
						sprintf(
						/* translators: %d: Number of user activity logs. */
							_n(
								'%d user log deleted successfully.',
								'%d user logs deleted successfully.',
								$count,
								'sb-core'
							),
							$count
						),
						'success'
					);
				}

				break;
		}
	}

	function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$per_page              = $this->get_items_per_page( 'posts_per_page', 50 );

		if ( ! is_numeric( $per_page ) ) {
			$per_page = 50;
		}

		$user_id = $_GET['user'] ?? '';

		$current_page = $this->get_pagenum();

		$orderby = $_GET['$orderby'] ?? 'ID';

		if ( 'id' == $orderby ) {
			$orderby = 'ID';
		}

		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';

		$where = '';

		$s = $_POST['s'] ?? '';

		if ( ! empty( $s ) ) {
			if ( is_numeric( $s ) ) {
				$where .= 'ID = ' . $s;
				$where .= ' OR user_id = ' . $s;
			}

			if ( ! empty( $where ) ) {
				$where = ' (' . $where . ' OR ';
			} else {
				$where .= '(';
			}

			$where .= "date LIKE '%" . $s . "%'";
			$where .= " OR IP LIKE '%" . $s . "%'";
			$where .= " OR agent LIKE '%" . $s . "%'";
			$where .= " OR url LIKE '%" . $s . "%'";

			$where .= ')';
		}

		$items = HTE_User_Activity_Logs()->get( $user_id, null, $orderby, $order, $where );

		$total_items = count( $items );

		$offset = ( $current_page - 1 ) * $per_page;

		$items = array_slice( $items, $offset, $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		$this->found_data = $items;
		$this->items      = $items;
	}
}