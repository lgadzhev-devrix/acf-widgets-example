<?php

/**
 * Register the new widget classes here so that they show up in the widget list.
 */
add_action( 'widgets_init', 'crb_register_widgets' );
function crb_register_widgets() {
	register_widget( 'Crb_Widget_Rich_Text' );
	// register_widget( 'Crb_Widget_Latest_Tweets' );
}

/**
 * Base Widget Class
 */
class Carbon_ACF_Widget extends WP_Widget {
	public function form( $instance ) {
		// outputs the options form on admin
		echo '<br/>';
	}

	public function widget( $args, $instance ) {
		// output
		echo $args['before_widget'];
		$this->front_end( $args, $instance );
		echo $args['after_widget'];
	}

	function front_end( $args, $instance ) { }

	public static function get_widget_meta( $widget_id, $meta_key ) {
		if (
			! self::exists()
			|| ! $meta_key
			|| ! $widget_id
		) {
			return;
		}

		return get_field( $meta_key, 'widget_' . $widget_id );
	}

	public static function exists() {
		return class_exists( 'Acf' );
	}
}

/**
 * Displays a block with a title and WYSIWYG rich text content
 */
class Crb_Widget_Rich_Text extends Carbon_ACF_Widget {
	public function __construct() {
		parent::__construct(
			'crb_rich_text', // Base ID
			__( 'Rich Text', 'crb' ), // Name
			array( 'description' => __( 'Displays a block with title and WYSIWYG content.', 'crb' ), ) // Args
		);
	}

	public function front_end( $args, $instance ) {
		$widget_id = $args['widget_id'];

		$fields = array(
			'title' => Carbon_ACF_Widget::get_widget_meta( $widget_id, 'title' ),
			'content' => Carbon_ACF_Widget::get_widget_meta( $widget_id, 'content' ),
		);

		// outputs the content of the widget
		if ( $fields['title'] ) {
			echo $args['before_title'] . $fields['title'] . $args['after_title'];
		}

		?>
		<div class="widget-body">
			<?php echo apply_filters( 'the_content', $fields['content'] ); ?>
		</div><!-- /.widget-body -->
		<?php
	}
}

/**
 * Displays a block with latest tweets from particular user
 */
class Crb_Widget_Latest_Tweets extends Carbon_ACF_Widget {
	public function __construct() {
		parent::__construct(
			'crb_latest_tweets', // Base ID
			__( 'Latest Tweets', 'crb' ), // Name
			array( 'description' => __( 'Displays a block with your latest tweets', 'crb' ), ) // Args
		);
	}

	public function front_end( $args, $instance ) {
		// outputs the content of the widget
		if ( ! carbon_twitter_is_configured() ) {
			return; //twitter settings are not configured
		}

		$instance = array(
			'title' => Carbon_ACF_Widget::get_widget_meta( $widget_id, 'title' ),
			'username' => Carbon_ACF_Widget::get_widget_meta( $widget_id, 'username' ),
			'count' => Carbon_ACF_Widget::get_widget_meta( $widget_id, 'count' ),
		);

		$tweets = TwitterHelper::get_tweets( $instance['username'], $instance['count'] );
		if ( empty( $tweets ) ) {
			return; //no tweets, or error while retrieving
		}

		if ( $instance['title'] ) {
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<div class="widget-body">
			<ul>
				<?php foreach ( $tweets as $tweet ): ?>
					<li><?php echo $tweet->tweet_text; ?> - <span><?php printf( __( '%1$s ago', 'crb' ), $tweet->time_distance ); ?></span></li>
				<?php endforeach ?>
			</ul>
		</div><!-- /.widget-body -->
		<?php
	}
}
