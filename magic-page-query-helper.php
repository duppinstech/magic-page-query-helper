<?php
/**
 * Plugin Name: 	  Magic Page Query Helper
 * Plugin URI: 		  https://github.com/duppinstech/magic-page-query-helper
 * Description: 	  Reduces location-term query load when using Magic Page (or similar) with many locations. Bulk-loads all location term meta in one query and short-circuits get_term_meta() so the main plugin never runs 10k+ individual queries. Safe to use with LiteSpeed/other full-page cache; does not replace Magic Page cache.
 * Version: 		  1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Update URI:        https://github.com/duppinstech/magic-page-query-helper
 * Author:            Duppins Technology
 * Author URI: 	      https://duppinstech.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: magic-page-query-helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MPQH_VERSION', '1.0.0' );
define( 'MPQH_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-mpqh-github-updater.php';

// Updates from GitHub Releases. Booted at load (not admin_init) so WP-Cron and background auto-updates see releases.
// Disable with define( 'MPQH_DISABLE_GITHUB_UPDATER', true ).
MPQH_GitHub_Updater::boot(
	array(
		'file'    => MPQH_FILE,
		'version' => MPQH_VERSION,
		'owner'   => 'duppinstech',
		'repo'    => 'magic-page-query-helper',
		'prefix'  => 'mpqh',
	)
);

/**
 * Load all term meta and all location terms in bulk so get_term_meta() and get_term()
 * are served from cache instead of 10k+ individual queries. Uses get_term_metadata
 * filter and WordPress _prime_term_caches(). Does not modify Magic Page.
 */
final class MPQH_Magic_Page_Query_Helper {

	const TAXONOMY = 'location';
	const FILTER   = 'get_term_metadata';
	const PRIORITY = 1;

	/** @var array<int,array<string,array<string>>> [ term_id => [ meta_key => [ values ] ] ] */
	private static $cache = null;

	/** @var bool */
	private static $loaded = false;

	/** @var array Diagnostic counters (always tracked for dashboard). */
	private static $debug = array( 'filter_calls' => 0, 'short_circuits' => 0, 'cache_primed' => false );

	public static function init() {
		add_filter( self::FILTER, array( __CLASS__, 'shortcircuit_term_meta' ), self::PRIORITY, 5 );
		add_action( 'init', array( __CLASS__, 'prime_on_request' ), 1 );
		add_action( 'wp', array( __CLASS__, 'prime_on_request' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'prime_on_request' ), 0 );
		add_filter( 'the_content', array( __CLASS__, 'prime_before_content' ), -999 );
		add_action( 'shutdown', array( __CLASS__, 'maybe_write_debug' ), 9999 );
		add_action( 'shutdown', array( __CLASS__, 'maybe_save_last_request' ), 9998 );
		add_action( 'admin_menu', array( __CLASS__, 'add_dashboard' ) );
	}

	/**
	 * Prime caches right before content (and shortcodes) are processed.
	 *
	 * @param string $content Post content (unchanged).
	 * @return string Same content.
	 */
	public static function prime_before_content( $content ) {
		self::prime_on_request();
		return $content;
	}

	/**
	 * Short-circuit get_term_meta when we have the value in our bulk-loaded cache.
	 *
	 * @param mixed  $value     Current value (null = not short-circuited).
	 * @param int    $object_id Term ID.
	 * @param string $meta_key  Meta key.
	 * @param bool   $single    Whether to return a single value.
	 * @param string $meta_type Meta type (e.g. 'term').
	 * @return mixed Value to return, or null to let WordPress run the normal DB path.
	 */
	public static function shortcircuit_term_meta( $value, $object_id, $meta_key, $single, $meta_type ) {
		self::$debug['filter_calls']++;

		if ( $meta_type !== 'term' ) {
			return $value;
		}

		self::maybe_load_cache();
		// Lazy-prime term cache on first get_term_meta so get_term() hits cache before any loop.
		self::prime_term_cache();

		if ( self::$cache === null ) {
			return $value;
		}

		if ( ! isset( self::$cache[ $object_id ] ) ) {
			return $value;
		}

		// get_term_meta( $term_id ) with no key = all meta for term (one query per term in WP).
		if ( $meta_key === '' || $meta_key === false ) {
			self::$debug['short_circuits']++;
			return self::$cache[ $object_id ];
		}

		if ( ! isset( self::$cache[ $object_id ][ $meta_key ] ) ) {
			return $value;
		}

		self::$debug['short_circuits']++;
		$vals = self::$cache[ $object_id ][ $meta_key ];
		if ( $single ) {
			return isset( $vals[0] ) ? $vals[0] : '';
		}
		return $vals;
	}

	/**
	 * Run one bulk query for all location term meta and populate self::$cache.
	 * Only runs once per request; safe to call from the filter.
	 */
	private static function maybe_load_cache() {
		if ( self::$loaded ) {
			return;
		}
		self::$loaded = true;
		self::$debug['cache_primed'] = true;

		global $wpdb;
		// Use direct table names; taxonomy may not be registered yet on early hooks.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk load; no high-level API loads all location term meta in one query.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tm.term_id, tm.meta_key, tm.meta_value FROM {$wpdb->termmeta} tm
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id AND tt.taxonomy = %s",
				self::TAXONOMY
			),
			OBJECT
		);
		if ( empty( $rows ) ) {
			return;
		}

		self::$cache = array();
		foreach ( $rows as $row ) {
			$tid = (int) $row->term_id;
			$key = $row->meta_key;
			if ( ! isset( self::$cache[ $tid ] ) ) {
				self::$cache[ $tid ] = array();
			}
			if ( ! isset( self::$cache[ $tid ][ $key ] ) ) {
				self::$cache[ $tid ][ $key ] = array();
			}
			self::$cache[ $tid ][ $key ][] = maybe_unserialize( $row->meta_value );
		}
	}

	/**
	 * Prime the cache early on front-end requests (including REST) so the first
	 * get_term_meta doesn't trigger the query during heavy template logic.
	 */
	public static function prime_on_request() {
		if ( is_admin() || wp_doing_cron() ) {
			return;
		}
		// Term meta: one bulk query; term objects: one bulk query + update_term_cache().
		self::maybe_load_cache();
		self::prime_term_cache();
	}

	/** @var bool Whether we have primed the WordPress term cache (get_term) for this request. */
	private static $term_cache_primed = false;

	/**
	 * Prime WordPress term cache so get_term( $id, 'location' ) hits cache.
	 * We load terms with raw SQL and update_term_cache() so we control the exact cache payload.
	 */
	private static function prime_term_cache() {
		if ( self::$term_cache_primed ) {
			return;
		}
		self::$term_cache_primed = true;

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk prime term cache; matches update_term_cache() payload.
		$terms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.term_id, t.name, t.slug, t.term_group, tt.term_taxonomy_id, tt.taxonomy, tt.description, tt.parent, tt.count
			FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s",
				self::TAXONOMY
			)
		);
		if ( empty( $terms ) ) {
			return;
		}
		// Ensure term_id is int so cache keys match wp_cache_get( (int) $id, 'terms' ).
		$to_cache = array();
		foreach ( $terms as $term ) {
			$term->term_id = (int) $term->term_id;
			$to_cache[]   = $term;
		}
		if ( function_exists( 'update_term_cache' ) ) {
			update_term_cache( $to_cache );
		}
	}

	public static function maybe_write_debug() {
		if ( ! ( defined( 'MPQH_PROFILE_QUERIES' ) || defined( 'MPQH_DEBUG' ) ) ) {
			return;
		}
		$out = 'MPQH diagnostic (one request):' . "\n"
			. 'filter_calls: ' . self::$debug['filter_calls'] . "\n"
			. 'short_circuits: ' . self::$debug['short_circuits'] . "\n"
			. 'cache_primed: ' . ( self::$debug['cache_primed'] ? 'yes' : 'no' ) . "\n"
			. 'cached_terms: ' . ( self::$cache === null ? 0 : count( self::$cache ) ) . "\n";
		@file_put_contents( WP_CONTENT_DIR . '/mpqh-debug.txt', $out, LOCK_EX );
	}

	/**
	 * Save last front-end request stats for the dashboard (non-admin requests only).
	 */
	public static function maybe_save_last_request() {
		if ( is_admin() || wp_doing_cron() ) {
			return;
		}
		$cached = self::$cache === null ? 0 : count( self::$cache );
		global $wpdb;
		$queries = isset( $wpdb->num_queries ) ? (int) $wpdb->num_queries : null;
		update_option( 'mpqh_last_request', array(
			'time'                => time(),
			'uri'                 => isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
			'filter_calls'        => self::$debug['filter_calls'],
			'short_circuits'       => self::$debug['short_circuits'],
			'cache_primed'        => self::$debug['cache_primed'],
			'cached_terms'        => $cached,
			'term_cache_primed'    => self::$term_cache_primed,
			'total_queries'        => $queries,
		), false );
	}

	/**
	 * Register dashboard under Settings.
	 */
	public static function add_dashboard() {
		add_options_page(
			__( 'Magic Page Query Helper', 'magic-page-query-helper' ),
			__( 'MPQH Dashboard', 'magic-page-query-helper' ),
			'manage_options',
			'mpqh-dashboard',
			array( __CLASS__, 'render_dashboard' )
		);
	}

	/**
	 * Render the dashboard page.
	 */
	public static function render_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$last = get_option( 'mpqh_last_request', array() );
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Dashboard count only; not hot path.
		$location_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT tt.term_id) FROM {$wpdb->term_taxonomy} tt WHERE tt.taxonomy = %s",
				self::TAXONOMY
			)
		);
		$taxonomy_exists = taxonomy_exists( self::TAXONOMY );
		$debug_file = WP_CONTENT_DIR . '/mpqh-debug.txt';
		$profile_file = WP_CONTENT_DIR . '/query-profile.txt';
		$slow_log = WP_CONTENT_DIR . '/debug-slow.log';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Magic Page Query Helper — Dashboard', 'magic-page-query-helper' ); ?></h1>

			<section class="mpqh-section" style="margin-top:1.5em;">
				<h2><?php esc_html_e( 'Status', 'magic-page-query-helper' ); ?></h2>
				<table class="widefat striped" style="max-width: 600px;">
					<tr>
						<td><?php esc_html_e( 'Plugin', 'magic-page-query-helper' ); ?></td>
						<td><strong><?php esc_html_e( 'Active', 'magic-page-query-helper' ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Taxonomy "location"', 'magic-page-query-helper' ); ?></td>
						<td><?php echo $taxonomy_exists ? '<strong style="color:green;">' . esc_html__( 'Registered', 'magic-page-query-helper' ) . '</strong>' : '<span style="color:orange;">' . esc_html__( 'Not registered (admin only?)', 'magic-page-query-helper' ) . '</span>'; ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Location terms in DB', 'magic-page-query-helper' ); ?></td>
						<td><?php echo (int) $location_count; ?></td>
					</tr>
				</table>
			</section>

			<section class="mpqh-section" style="margin-top:1.5em;">
				<h2><?php esc_html_e( 'What the helper does', 'magic-page-query-helper' ); ?></h2>
				<ul style="list-style: disc; margin-left: 1.5em;">
					<li><strong><?php esc_html_e( 'Term meta', 'magic-page-query-helper' ); ?></strong> — One bulk query loads all location term meta; <code>get_term_meta()</code> is short-circuited so the DB is not hit per call.</li>
					<li><strong><?php esc_html_e( 'Term cache', 'magic-page-query-helper' ); ?></strong> — One bulk query loads all location terms and primes WordPress’s term cache so <code>get_term()</code> can hit cache instead of the DB.</li>
				</ul>
			</section>

			<section class="mpqh-section" style="margin-top:1.5em;">
				<h2><?php esc_html_e( 'Last front-end request (from any visitor)', 'magic-page-query-helper' ); ?></h2>
				<?php if ( ! empty( $last['time'] ) ) : ?>
					<p><?php
					echo esc_html(
						sprintf(
							/* translators: 1: Request time (UTC), 2: Request URI. */
							__( 'At %1$s — %2$s', 'magic-page-query-helper' ),
							gmdate( 'Y-m-d H:i:s', $last['time'] ),
							isset( $last['uri'] ) ? $last['uri'] : ''
						)
					);
					?></p>
					<table class="widefat striped" style="max-width: 600px;">
						<tr>
							<td><?php esc_html_e( 'get_term_meta filter calls', 'magic-page-query-helper' ); ?></td>
							<td><?php echo (int) ( $last['filter_calls'] ?? 0 ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Short-circuited (served from cache)', 'magic-page-query-helper' ); ?></td>
							<td><strong><?php echo (int) ( $last['short_circuits'] ?? 0 ); ?></strong> <?php echo ( isset( $last['filter_calls'] ) && $last['filter_calls'] > 0 ) ? '(' . (int) round( 100 * ( $last['short_circuits'] / $last['filter_calls'] ) ) . '%)' : ''; ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Term meta cache primed', 'magic-page-query-helper' ); ?></td>
							<td><?php echo ! empty( $last['cache_primed'] ) ? '✓' : '—'; ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Cached location terms (meta)', 'magic-page-query-helper' ); ?></td>
							<td><?php echo (int) ( $last['cached_terms'] ?? 0 ); ?></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Term object cache primed', 'magic-page-query-helper' ); ?></td>
							<td><?php echo ! empty( $last['term_cache_primed'] ) ? '✓' : '—'; ?></td>
						</tr>
						<?php if ( isset( $last['total_queries'] ) && $last['total_queries'] !== null ) : ?>
						<tr>
							<td><?php esc_html_e( 'Total DB queries (that request)', 'magic-page-query-helper' ); ?></td>
							<td><?php echo (int) $last['total_queries']; ?> <?php echo (int) $last['total_queries'] > 1000 ? '<span style="color:red;">(' . esc_html__( 'high', 'magic-page-query-helper' ) . ')</span>' : ''; ?></td>
						</tr>
						<?php endif; ?>
					</table>
					<p class="description"><?php esc_html_e( 'Total queries are only stored when SAVEQUERIES is enabled in wp-config.php.', 'magic-page-query-helper' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'No front-end request recorded yet. Load a Magic Page URL (e.g. a service-area or heavy-equipment-towing page) to populate this.', 'magic-page-query-helper' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="mpqh-section" style="margin-top:1.5em;">
				<h2><?php esc_html_e( 'Interpretation', 'magic-page-query-helper' ); ?></h2>
				<ul style="list-style: disc; margin-left: 1.5em;">
					<li><?php esc_html_e( 'If “Short-circuited” is close to “filter calls”, term meta is being served from cache (working as intended).', 'magic-page-query-helper' ); ?></li>
					<li><?php esc_html_e( 'If “filter calls” is 0 on a Magic Page request, the helper did not run on that URL or the page did not call get_term_meta for location terms.', 'magic-page-query-helper' ); ?></li>
					<li><?php esc_html_e( 'If “Total DB queries” is still very high (e.g. 14k–37k) despite term meta short-circuits, the remaining queries are likely from get_term() / get_term_link() in loops; the term cache prime may not be taking effect in your environment.', 'magic-page-query-helper' ); ?></li>
				</ul>
			</section>

			<section class="mpqh-section" style="margin-top:1.5em;">
				<h2><?php esc_html_e( 'Diagnostic files', 'magic-page-query-helper' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Enable in wp-config.php: define(\'SAVEQUERIES\', true); and define(\'MPQH_PROFILE_QUERIES\', true); then load one slow Magic Page URL. Remove the defines after capturing.', 'magic-page-query-helper' ); ?></p>
				<table class="widefat striped" style="max-width: 800px;">
					<tr>
						<td><code>wp-content/mpqh-debug.txt</code></td>
						<td><?php echo file_exists( $debug_file ) ? '<strong style="color:green;">' . esc_html__( 'Present', 'magic-page-query-helper' ) . '</strong>' : '<span style="color:#666;">' . esc_html__( 'Not found', 'magic-page-query-helper' ) . '</span>'; ?></td>
					</tr>
					<tr>
						<td><code>wp-content/query-profile.txt</code></td>
						<td><?php echo file_exists( $profile_file ) ? '<strong style="color:green;">' . esc_html__( 'Present', 'magic-page-query-helper' ) . '</strong>' : '<span style="color:#666;">' . esc_html__( 'Not found', 'magic-page-query-helper' ) . '</span>'; ?></td>
					</tr>
					<tr>
						<td><code>wp-content/debug-slow.log</code></td>
						<td><?php echo file_exists( $slow_log ) ? '<strong style="color:green;">' . esc_html__( 'Present', 'magic-page-query-helper' ) . '</strong>' : '<span style="color:#666;">' . esc_html__( 'Not found', 'magic-page-query-helper' ) . '</span>'; ?></td>
					</tr>
				</table>
				<?php
				if ( file_exists( $debug_file ) ) {
					echo '<h3>' . esc_html__( 'mpqh-debug.txt (last run)', 'magic-page-query-helper' ) . '</h3><pre style="background:#f5f5f5; padding:1em; overflow:auto; max-height:200px;">' . esc_html( file_get_contents( $debug_file ) ) . '</pre>';
				}
				if ( file_exists( $profile_file ) ) {
					$profile = file_get_contents( $profile_file );
					echo '<h3>' . esc_html__( 'query-profile.txt (by-table counts)', 'magic-page-query-helper' ) . '</h3><pre style="background:#f5f5f5; padding:1em; overflow:auto; max-height:300px;">' . esc_html( $profile ) . '</pre>';
				}
				if ( file_exists( $slow_log ) ) {
					$lines = array_slice( array_filter( explode( "\n", file_get_contents( $slow_log ) ) ), -15 );
					echo '<h3>' . esc_html__( 'Last 15 lines of debug-slow.log', 'magic-page-query-helper' ) . '</h3><pre style="background:#f5f5f5; padding:1em; overflow:auto; max-height:280px;">' . esc_html( implode( "\n", $lines ) ) . '</pre>';
				}
				?>
			</section>
		</div>
		<?php
	}
}

add_action( 'plugins_loaded', array( 'MPQH_Magic_Page_Query_Helper', 'init' ), 5 );
