<?php
/**
 * Keywords Table class
 * 
 * @package Page_Generator_Pro
 * @author 	Tim Carr
 * @version 1.0.0
 */
class Page_Generator_Pro_Keywords_Table extends WP_List_Table {

	/**
     * Holds the class object.
     *
     * @since 	1.1.3
     *
     * @var 	object
     */
    public static $instance;

    /**
     * Holds the base class object.
     *
     * @since 	1.3.8
     *
     * @var 	object
     */
    public $base;

	/**
	 * Constructor
	 *
	 * @since 	1.0.0
	 */
	public function __construct() {

		parent::__construct( array(
			'singular'	=> 'keyword', 	// Singular label
			'plural' 	=> 'keywords', 	// plural label, also this well be one of the table css class
			'ajax'		=> false 		// We won't support Ajax for this table
		));

	}
	
	/**
	 * Defines the message to display when no items exist in the table
	 *
	 * @since 	1.0.0
	 *
	 * @return 	No Items Message
	 */
	public function no_items() {

		// Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

		_e( 'Keywords are used to product unique content for each Page, Post or Custom Post Type that is generated.', $this->base->plugin->name );
		echo ( '<br /><a href="admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=form" class="button">' . __( 'Create first keyword.', $this->base->plugin->name ).'</a>' );
	
	}
	 
	/**
 	 * Define the columns that are going to be used in the table
 	 *
 	 * @since 	1.0.0
 	 *
 	 * @return 	array 	Columns to use with the table
 	 */
	public function get_columns() {

		// Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

		return array(
			'cb' 					=> '<input type="checkbox" class="toggle" />',
			'col_field_keyword' 	=> __( 'Keyword', $this->base->plugin->name ),
			'col_field_term_count'	=> __( 'Number of Terms', $this->base->plugin->name ),
		);

	}
	
	/**
 	 * Decide which columns to activate the sorting functionality on
 	 *
 	 * @since 	1.0.0
 	 *
 	 * @return 	array 	Columns that can be sorted by the user
 	 */
	public function get_sortable_columns() {

		return $sortable = array(
			'col_field_keyword' => array( 'keyword', true )
		);

	}
	
	/**
	 * Overrides the list of bulk actions in the select dropdowns above and below the table
	 *
	 * @since 	1.0.0
	 *
	 * @return 	array 	Bulk Actions
	 */
	public function get_bulk_actions() {

		// Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

		return array(
			'delete' => __( 'Delete', $this->base->plugin->name ),
		);

	}
	
	/**
 	 * Prepare the table with different parameters, pagination, columns and table elements
 	 *
 	 * @since 	1.0.0
 	 */
	public function prepare_items() {

		global $_wp_column_headers;
		
		$screen = get_current_screen();
		
		// Get params
		$search 	= ( isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '' );
		$order_by 	= ( isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'keyword' );
  		$order 		= ( isset( $_GET['order'] ) ? $_GET['order'] : 'ASC' );
		
		// Adjust as necessary to display the required number of rows per screen
		$rows_per_page = 10;

		// Get instances
		$instance = Page_Generator_Pro_Keywords::get_instance();

		// Get all records
		$total = $instance->total( $search );
		
		// Define pagination if required
		$paged = 1;
		if ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) {
			$paged = absint( $_GET['paged'] );
		}
		$this->set_pagination_args( array(
			'total_items' 	=> $total,
			'total_pages' 	=> ceil( $total / $rows_per_page ),
			'per_page' 		=> $rows_per_page,
		) );
		
		// Set table columns and rows
		$columns = $this->get_columns();
  		$hidden  = array();
  		$sortable = $this->get_sortable_columns();
  		$this->_column_headers = array( $columns, $hidden, $sortable );
  		$this->items = $instance->get_all( $order_by, $order, $paged, $rows_per_page, $search );

	}

	/**
	 * Display the rows of records in the table
	 *
	 * @since 	1.0.0
	 *
	 * @return 	HTML Row Output
	 */
	public function display_rows() {

		// Get base instance
        $this->base = ( class_exists( 'Page_Generator_Pro' ) ? Page_Generator_Pro::get_instance() : Page_Generator::get_instance() );

		// Get rows and columns
		$records = $this->items;
		list( $columns, $hidden ) = $this->get_column_info();
		
		// Bail if no records found
		if ( empty( $records) ) {
			return;
		}

		// Iterate through records
		foreach ( $records as $key => $record ) {
			// Start row
			echo ('<tr id="record_' . $record->keywordID . '"' . ( ( $key % 2 == 0 ) ? ' class="alternate"' : '') . '>' );

			// Iterate through columns
			foreach ( $columns as $column_name => $display_name ) {
				switch ( $column_name ) {
					/**
					* Checkbox
					*/
					case 'cb':
						echo ('	<th scope="row" class="check-column">
									<input type="checkbox" name="ids[' . $record->keywordID . ']" value="' . $record->keywordID . '" />
								</th>'); 
						break;

					/**
					* Keyword
					*/
					case 'col_field_keyword':
						echo ( '<td class="' . $column_name . ' column-' . $column_name . '">
									<strong>
										<a href="admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=form&id=' . absint( $record->keywordID ) . '" title="' . __( 'Edit this item', $this->base->plugin->name ) . '">
											' . $record->keyword . '
										</a>
									</strong>
									<div class="row-actions">
										<span class="edit">
											<a href="admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=form&id=' . absint( $record->keywordID ) . '" title="' . __( 'Edit this item', $this->base->plugin->name ) . '">
											' . __( 'Edit', $this->base->plugin->name ) . '
											</a> | 
										</span>
										<span class="trash">
											<a href="admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=delete&id=' . absint( $record->keywordID ) . '" title="' . __( 'Delete this item', $this->base->plugin->name ).'" class="delete">
											' . __( 'Delete', $this->base->plugin->name ) . '
											</a>
										</span>
									</div>
								</td>'); 
						break;

					/**
					* Number of Terms
					*/
					case 'col_field_term_count':
						echo ( 	'<td class="' . $column_name . ' column-' . $column_name . '">' . count( explode( "\n", $record->data ) ) . '</td>' ); 
						break;

				}
			}

			// End row
			echo (' </tr>' );
		}

	}

	/**
     * Returns the singleton instance of the class.
     *
     * @since 1.1.3
     *
     * @return object Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}