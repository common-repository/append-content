<?php
/*
Plugin Name: Append Content
Description: Append content to your posts/pages, useful for repetitious messaging and calls to action. Apply template to all pages/posts, only to posts, or only to pages. 
Version: 2.1.1
Author URI: http://theandystratton.com
Author: theandystratton <theandystratton@gmail.com>
License: GPL2 or later
*/
namespace WPM;

class AppendContent {
	
	public static $instance;

	public static function init()
	{
		null === self::$instance && self::$instance = new self();
		return self::$instance;
	}

	private function __construct() 
	{
		\add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		\add_filter( 'the_content', [ $this, 'the_content' ] );
		\add_filter( 'plugin_action_links_' . \plugin_basename( __FILE__ ), [ $this, 'action_links' ] );
	}

	public function action_links( $links )
	{
  		$settings_link = '<a href="options-general.php?page=' . \basename( __FILE__ ) . '">Settings</a>'; 
  		$links[] = $settings_link;
		return $links;
	}

	public function admin_menu() 
	{
		\add_submenu_page( 'options-general.php', 'Append Content', 'Append Content', 8, \basename( __FILE__ ), [ $this,'main' ] );
	}
	
	function the_content( $content ) {

		$apc_ignore_pages = \get_option( 'apc_ignore_pages' );
		
		if ( \is_array( $apc_ignore_pages ) && \in_array( get_the_ID(), $apc_ignore_pages ) )
			return $content;

		if ( \is_home() && \get_option( 'apc_omit_home' ) == 1 )
			return $content;

		if ( \is_front_page() && \get_option( 'apc_omit_front' ) == 1 )
			return $content;

		if ( \is_category() && \get_option( 'apc_omit_cat' ) == 1 )
			return $content;

		if ( \is_tag() && \get_option( 'apc_omit_tag' ) == 1 )
			return $content;

		if ( \is_date() && \get_option( 'apc_omit_date' ) == 1 )
			return $content;

		$publish = \get_option( 'apc_publish' );
		$apc_content = \wpautop( \get_option( 'apc_content' ) );

		switch ( $publish ) 
		{
			case 'all':
				$content .= "\n\n" . $apc_content;
				break;

			case 'posts':
				if ( !\is_page( \get_the_ID() ) ) 
				{
					$content .= "\n\n" . $apc_content;
				}
				break;

			case 'pages':
				if ( \is_page( \get_the_ID() ) ) {
					$content .= "\n\n" . $apc_content;
				}
				break;

			default:
				break;

		}

		return $content;
	}
	
	public function main() {
?>
<div class="wrap">
	<h2>Append Content Plugin Settings</h2>
	<hr>
	<h3>Content to Append:</h3>
	<?php 
		if ( isset( $_POST['apc_content'] ) )
		{
			$apc_content = \stripslashes( $_POST['apc_content'] );
			\update_option( 'apc_content', $apc_content );
			echo '<div class="message updated"><p>Content settings updated.</p></div>';
		}
	?>
	<form method="post" action="">
		<p>
		    <?php 
				\wp_editor( \get_option( 'apc_content' ), 'apc_content', [
					'textarea_rows' => 5,
					'textarea_name' => 'apc_content'
				])
		    ?>
		</p>
	    
		<p>
			<input type="submit" value="Save Your Content" class="button-primary" />
		</p>
	</form>
	
	<hr>

	<h3>Publishing Settings</h3>
	<?php 
		if ( isset( $_POST['apc_publish'] ) )
		{
			if ( \in_array( $_POST['apc_publish'], [ 'all','posts','pages' ] ) )
			{
				\update_option( 'apc_publish', $_POST['apc_publish'] );
				echo '<div class="message updated"><p>Publishing settings updated.</p></div>';
			}
		}

		$apc_publish = \get_option( 'apc_publish', 'all' );

	?>
	<form method="post" action="?<?php echo $_SERVER['QUERY_STRING']; ?>">
		<p>
			Choose which posts/pages you'd like to apply content to:
		</p>
		<div style="margin-left: 1.5em;">
			<p>
				<label for="all_posts_pages">
					<input type="radio" id="all_posts_pages" name="apc_publish" value="all"<?php 
						\checked( $apc_publish, 'all' );
					?> />
					All posts/pages.
				</label>
			</p>
			<p>
				<label for="posts_only">
					<input type="radio" id="posts_only" name="apc_publish" value="posts"<?php 
						\checked( $apc_publish, 'posts' );
					?> />
					Only to posts.
				</label>
			</p>
			<p>
				<label for="pages_only">
					<input type="radio" id="pages_only" name="apc_publish" value="pages"<?php 
						\checked( $apc_publish, 'pages' );
					?> />
					Only to pages.
				</label>
			</p>
		</div>
		<?php
			if ( isset( $_POST['apc_omit_home'] ) ) 
			{
				if ( $_POST['apc_omit_home'] == 1 ) 
					$apc_omit_home = 1;
				else 
					$apc_omit_home = 0;

				\update_option( 'apc_omit_home', $apc_omit_home );
			}
			$apc_omit_home = \get_option( 'apc_omit_home' );
		?>
		<p>
			<label for="omit_home">
				<input type="checkbox" id="omit_home" name="apc_omit_home" value="1" <?php
					\checked( $apc_omit_home, 1 );
				?> /> Ignore posts on the blog home page.
			</label>
		</p>
		<?php
			if ( isset($_POST['apc_omit_front'] ) )
			{
				if ( $_POST['apc_omit_front'] == 1 ) 
					$apc_omit_front = 1;
				else 
					$apc_omit_front = 0;
			
				\update_option( 'apc_omit_front', $apc_omit_front);
			}

			$apc_omit_front = \get_option( 'apc_omit_front' );
		?>
		<p>
			<label for="omit_front">
				<input type="checkbox" id="omit_front" name="apc_omit_front" value="1" <?php
					\checked( $apc_omit_front, 1 );
				?> /> Ignore posts on the front page.
			</label>
		</p>
		<?php
			if ( isset( $_POST['apc_omit_cat'] ) ) 
			{
				if ( $_POST['apc_omit_cat'] == 1 ) 
					$apc_omit_cat = 1;
				else 
					$apc_omit_cat = 0;
			
				\update_option( 'apc_omit_cat', $apc_omit_cat );
			}

			$apc_omit_cat = \get_option( 'apc_omit_cat' );
		?>
		<p>
			<label for="omit_cat">
				<input type="checkbox" id="omit_cat" name="apc_omit_cat" value="1" <?php
					\checked( $apc_omit_cat == 1 );
				?> /> Ignore posts in category archives.
			</label>
		</p>
		<?php
			if ( isset( $_POST['apc_omit_tag'] ) )
			{
				if ( $_POST['apc_omit_tag'] == 1 ) 
					$apc_omit_tag = 1;
				else 
					$apc_omit_tag = 0;
			
				\update_option( 'apc_omit_tag', $apc_omit_tag);
			}

			$apc_omit_tag = \get_option( 'apc_omit_tag' );
		?>
		<p>
			<label for="omit_tag">
				<input type="checkbox" id="omit_tag" name="apc_omit_tag" value="1" <?php
					\checked( $apc_omit_tag, 1 );
				?> /> Ignore posts in tag archives.
			</label>
		</p>
		<?php
			if ( isset( $_POST['apc_omit_date'] ) )
			{
				if ( $_POST['apc_omit_date'] == 1 ) 
					$apc_omit_date = 1;
				else 
					$apc_omit_date = 0;

				\update_option( 'apc_omit_date', $apc_omit_date );
			}
			$apc_omit_date = \get_option( 'apc_omit_date' );
		?>
		<p>
			<label for="omit_date">
				<input type="checkbox" id="omit_date" name="apc_omit_date" value="1" <?php
					\checked( $apc_omit_date, 1 );
				?> /> Ignore posts in date archives.
			</label>
		</p>

		<p>
			<label for="apc-ignore-pages">
				<strong>Ignore Specific Pages:</strong><br>
			</label>
			<?php
				if ( isset( $_POST ) && count( $_POST ) > 0 )
				{					
					$apc_ignore_pages = array();

					if ( isset( $_POST['apc_ignore_pages'] ) && \is_array( $_POST['apc_ignore_pages'] ) ) 
						$apc_ignore_pages = \array_map( 'intval', $_POST['apc_ignore_pages'] );
					
					\update_option( 'apc_ignore_pages', $apc_ignore_pages );
				}

				$apc_ignore_pages = \get_option( 'apc_ignore_pages' );
				
				$pages = \wp_dropdown_pages([
					'id' => 'apc-ignore-pages',
					'name' => 'apc_ignore_pages[]',
					'echo' => 0
				]);

				$pages = \str_replace( '<select', '<select multiple="multiple" size="10"', $pages );
				
				// select any pages
				if ( isset( $apc_ignore_pages ) && is_array( $apc_ignore_pages ) )
				{
					foreach ( $apc_ignore_pages as $page_id )
					{
						$pages = \str_replace( ' value="' . $page_id . '"', ' value="' . $page_id . '" selected="selected"', $pages );
					}
				}

				echo $pages;
			?>
			<br>
			<small><em>
				If you'd like to append content to ALL pages but ignore a small handful,
				select the pages in this list.
			</em></small>
		</p>
		<p>
			<input type="submit" value="Save Publishing Settings" class="button-primary" />
		</p>
	</form>
</div>
<?php 
	}
	
}

AppendContent::init();