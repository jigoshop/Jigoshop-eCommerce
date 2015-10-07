<?php

namespace WPAL;

/**
 * Provides abstraction for WordPress calls.
 *
 * @package WPAL
 * @author Amadeusz Starzykiewicz
 */
class Wordpress
{
	private $wp;
	/** @var \wpdb */
	private $wpdb;
	/** @var array */
	private $menu;
	/** @var array */
	private $submenu;
	/** @var mixed */
	private $currentScreen;
	/** @var \WP_Roles */
	private $roles;
	/** @var null|\WP_Post */
	private $post;
	/** @var string */
	private $post_type;
	/** @var string */
	private $pageNow;
	/** @var \WP_Query */
	private $query;
	/** @var \WP_Rewrite */
	private $rewrite;
	/** @var \WP_Theme */
	private $theme;
	/** @var Wordpress\Helpers */
	private $helpers;

	public function __construct()
	{
		global $wp;
		global $wpdb;
		global $wp_query;
		global $wp_rewrite;
		global $menu;
		global $submenu;
		global $current_screen;
		global $post;
		global $pagenow;
		global $post_type;

		$this->wp = &$wp;
		$this->wpdb = &$wpdb;
		$this->menu = &$menu;
		$this->submenu = &$submenu;
		$this->currentScreen = &$current_screen;
		$this->post = &$post;
		$this->post_type = &$post_type;
		$this->pageNow = &$pagenow;
		$this->query = &$wp_query;
		$this->rewrite = &$wp_rewrite;
		$this->theme = wp_get_theme();
		$this->helpers = new Wordpress\Helpers();
	}

	public function getHelpers()
	{
		return $this->helpers;
	}

	/** @return \stdClass */
	public function getWp()
	{
		return $this->wp;
	}

	/** @return \wpdb WPDB instance. */
	public function getWPDB()
	{
		return $this->wpdb;
	}

	/** @return \WP_Query Current query object. */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * @return \WP_Rewrite
	 */
	public function getRewrite()
	{
		return $this->rewrite;
	}

	/** @return \WP_Theme Currently used theme. */
	public function wpGetTheme()
	{
		return $this->theme;
	}

	/** @return array Menu data. */
	public function getMenu()
	{
		return $this->menu;
	}

	/** @return array Submenu data. */
	public function getSubmenu()
	{
		return $this->submenu;
	}

	/** @return null|\WP_Post Post object. */
	public function getGlobalPost()
	{
		return $this->post;
	}

	/**
	 * @param $newPost \WP_Post Post to update global one.
	 */
	public function updateGlobalPost($newPost)
	{
		global $post;
		$post = $newPost;
	}

	/** @return string Post type value. */
	public function getPostType()
	{
		return $this->post_type;
	}

	/** @return string Page now global value. */
	public function getPageNow()
	{
		return $this->pageNow;
	}

	public function getBlogInfo($show = '', $filter = 'raw')
	{
		return get_bloginfo($show, $filter);
	}

	public function getQueryParameter($parameter, $default = null)
	{
		if(!isset($this->query->query[$parameter])){
			return $default;
		}

		return $this->query->query[$parameter];
	}

	/** @return \WP_Roles Roles object. */
	public function getRoles()
	{
		if($this->roles === null){
			global $wp_roles;
			if (class_exists('WP_Roles') && !($wp_roles instanceof \WP_Roles)) {
				$wp_roles = new \WP_Roles();
			}
			$this->roles = &$wp_roles;
		}
		return $this->roles;
	}

	/**
	 * @return string Current displayed type.
	 */
	public function getTypeNow()
	{
		global $typenow;
		return $typenow;
	}

	public function getCurrentScreen()
	{
		return $this->currentScreen;
	}

	public function getPost($post = null, $output = OBJECT, $filter = 'raw')
	{
		return get_post($post, $output, $filter);
	}

	public function getPostMeta($post_id, $key = '', $single = false)
	{
		return get_post_meta($post_id, $key, $single);
	}

	public function getPosts($args = null)
	{
		return get_posts($args);
	}

	public function updatePostMeta($id, $meta_key, $meta_value, $previous_value = '')
	{
		return update_post_meta($id, $meta_key, $meta_value, $previous_value);
	}

	public function getPostField($field, $post, $context = 'display')
	{
		return get_post_field($field, $post, $context);
	}

	public function getCategories($args = '')
	{
		return get_categories($args);
	}

	public function getPages($args = array())
	{
		return get_pages($args);
	}

	public function getPageUri($page)
	{
		return get_page_uri($page);
	}

	public function getPostTypeArchiveLink($post_type)
	{
		return get_post_type_archive_link($post_type);
	}

	public function addRole($role, $display_name, $capabilities = array())
	{
		return add_role($role, $display_name, $capabilities);
	}

	public function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		return add_action($tag, $function_to_add, $priority, $accepted_args);
	}

	public function removeAction($tag, $function_to_remove, $priority = 10)
	{
		return remove_action($tag, $function_to_remove, $priority);
	}

	public function doAction($tag, $arg = '')
	{
		return call_user_func_array('do_action', func_get_args());
	}

	public function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		return add_filter($tag, $function_to_add, $priority, $accepted_args);
	}

	public function removeFilter($tag, $function_to_remove, $priority = 10)
	{
		return remove_filter($tag, $function_to_remove, $priority);
	}

	public function clearScheduledHook($hook, $args = array())
	{
		wp_clear_scheduled_hook($hook, $args);
	}

	public function nextScheduled($hook, $args = array())
	{
		return wp_next_scheduled($hook, $args);
	}

	public function scheduleEvent($timestamp, $recurrence, $hook, $args = array())
	{
		return wp_schedule_event($timestamp, $recurrence, $hook, $args);
	}

	public function applyFilters($tag, $args)
	{
		return call_user_func_array('apply_filters', func_get_args());
	}

	public function addImageSize($size, $width = 0, $height = 0, $crop = false)
	{
		return add_image_size($size, $width, $height, $crop);
	}

	public function updateOption($option, $options)
	{
		return update_option($option, $options);
	}

	public function getOption($option, $default = false)
	{
		return get_option($option, $default);
	}

	public function deleteOption($option)
	{
		return delete_option($option);
	}

	public function isAdmin()
	{
		return is_admin();
	}

	public function isSsl()
	{
		return is_ssl();
	}

	public function isMultisite()
	{
		return is_multisite();
	}

	public function siteUrl($path = '', $scheme = null)
	{
		return site_url($path, $scheme);
	}

	public function adminUrl($path = '', $scheme = 'admin')
	{
		return admin_url($path, $scheme);
	}

	public function networkAdminUrl($path = '', $scheme = null)
	{
		return network_admin_url($path, $scheme);
	}

	public function wpUploadDir($time = null)
	{
		return wp_upload_dir($time);
	}

	public function currentUserCan($capability)
	{
		return current_user_can($capability);
	}

	public function getCurrentUserId()
	{
		return get_current_user_id();
	}

	public function isUserLoggedIn()
	{
		return is_user_logged_in();
	}

	public function wpGetCurrentUser()
	{
		return wp_get_current_user();
	}

	public function wpCreateUser($username, $password, $email = '')
	{
		return wp_create_user($username, $password, $email);
	}

	public function wpUpdateUser($userdata)
	{
		return wp_update_user($userdata);
	}

	public function getCurrentUserInfo()
	{
		return get_currentuserinfo();
	}

	public function getUserData($user_id)
	{
		return get_userdata($user_id);
	}

	public function getUserBy($field, $value)
	{
		return get_user_by($field, $value);
	}

	public function getUserMeta($user_id, $key = '', $single = false)
	{
		return get_user_meta($user_id, $key, $single);
	}

	public function updateUserMeta($user_id, $meta_key, $meta_value, $prev_value = '')
	{
		return update_user_meta($user_id, $meta_key, $meta_value, $prev_value);
	}

	public function wpCheckPassword($password, $hash, $user_id = '')
	{
		return wp_check_password($password, $hash, $user_id);
	}

	public function getUsers($args = array())
	{
		return get_users($args);
	}

	public function addMenuPage($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null)
	{
		return add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
	}

	public function addSubmenuPage($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '')
	{
		return add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
	}

	public function getStylesheetDirectory()
	{
		return get_stylesheet_directory();
	}

	public function getStylesheetDirectoryUri()
	{
		return get_stylesheet_directory_uri();
	}

	public function registerPostType($post_type, $args = array())
	{
		return register_post_type($post_type, $args);
	}

	public function registerPostStatus($post_status, $args = array())
	{
		return register_post_status($post_status, $args);
	}

	public function registerTaxonomy($taxonomy, $object_type, $args = array())
	{
		return register_taxonomy($taxonomy, $object_type, $args);
	}

	public function registerWidget($widget_class)
	{
		register_widget($widget_class);
	}

	public function unregisterWidget($widget_class)
	{
		unregister_widget($widget_class);
	}

	public function isPostTypeArchive($post_types = '')
	{
		return is_post_type_archive($post_types);
	}

	public function isPage($page = '')
	{
		return is_page($page);
	}

	public function isTax($taxonomy = '', $term = '')
	{
		return is_tax($taxonomy, $term);
	}

	public function isSingular($post_types = '')
	{
		return is_singular($post_types);
	}

	public function wpEnqueueScript($handle, $src = false, $deps = array(), $ver = false, $in_footer = false)
	{
		wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
	}

	public function wpDeregisterScript($handle)
	{
		wp_deregister_script($handle);
	}

	public function wpEnqueueStyle($handle, $src = false, $deps = array(), $ver = false, $media = false)
	{
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	}

	public function wpDeregisterStyle($handle)
	{
		wp_deregister_style($handle);
	}

	public function wpEnqueueMedia($args = array())
	{
		wp_enqueue_media($args);
	}

	public function addMetaBox($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null)
	{
		add_meta_box($id, $title, $callback, $screen, $context, $priority, $callback_args);
	}

	public function removeMetaBox($id, $screen, $context)
	{
		remove_meta_box($id, $screen, $context);
	}

	public function wpCountPosts($type = 'post', $perm = '')
	{
		return wp_count_posts($type, $perm);
	}

	public function wpCountTerms($taxonomy, $args = array())
	{
		return wp_count_terms($taxonomy, $args);
	}

	public function fetchFeed($url)
	{
		return fetch_feed($url);
	}

	public function isWpError($thing)
	{
		return is_wp_error($thing);
	}

	public function humanTimeDiff($from, $to = '')
	{
		return human_time_diff($from, $to);
	}

	public function getTerm($term, $taxonomy, $output = OBJECT, $filter = 'raw')
	{
		return get_term($term, $taxonomy, $output, $filter);
	}

	public function getTerms($taxonomies, $args = '')
	{
		return get_terms($taxonomies, $args);
	}

	public function getTermBy($field, $value, $taxonomy, $output = OBJECT, $filter = 'raw')
	{
		return get_term_by($field, $value, $taxonomy, $output, $filter);
	}

	public function getTheTerms($post, $taxonomy)
	{
		return get_the_terms($post, $taxonomy);
	}

	public function getTermLink($term, $taxonomy = '')
	{
		return get_term_link($term, $taxonomy);
	}

	public function wpInsertPost($data, $wp_error = false)
	{
		return wp_insert_post($data, $wp_error);
	}

	public function wpUpdatePost($data = array(), $error = false)
	{
		return wp_update_post($data, $error);
	}

	public function wpPublishPost($post)
	{
		wp_publish_post($post);
	}

	public function wpUniquePostSlug($slug, $post_ID, $post_status, $post_type, $post_parent )
	{
		return wp_unique_post_slug($slug, $post_ID, $post_status, $post_type, $post_parent);
	}

	public function wpAddPostTags($post_id = 0, $tags = '')
	{
		return wp_add_post_tags($post_id, $tags);
	}

	public function wpSetPostTags($post_id = 0, $tags = '', $append = false)
	{
		return wp_set_post_tags($post_id, $tags, $append);
	}

	public function wpSetPostCategories($post_id = 0, $post_categories = array(), $append = false)
	{
		return wp_set_post_categories($post_id, $post_categories, $append);
	}

	public function wpTransitionPostStatus($new_status, $old_status, $post)
	{
		wp_transition_post_status($new_status, $old_status, $post);
	}

	public function registerSetting($option_group, $option_name, $sanitize_callback = '')
	{
		return register_setting($option_group, $option_name, $sanitize_callback);
	}

	public function addSettingsSection($id, $title, $callback, $page)
	{
		add_settings_section($id, $title, $callback, $page);
	}

	public function addSettingsField($id, $title, $callback, $page, $section = 'default', $args = array())
	{
		 add_settings_field($id, $title, $callback, $page, $section, $args);
	}

	public function wpGetAttachmentUrl($post_id = 0)
	{
		return wp_get_attachment_url($post_id);
	}

	public function wpGetAttachmentImage($attachment_id, $size = 'thumbnail', $icon = false, $attr = '')
	{
		return wp_get_attachment_image($attachment_id, $size, $icon, $attr);
	}

	public function wpGetAttachmentImageSrc($attachment_id, $size = 'thumbnail', $icon = false)
	{
		return wp_get_attachment_image_src($attachment_id, $size, $icon);
	}

	public function getPostThumbnailId($post_id = null)
	{
		return get_post_thumbnail_id($post_id);
	}

	public function hasPostThumbnail($post_id = null)
	{
		return has_post_thumbnail($post_id);
	}

	public function setPostThumbnail($post, $thumbnail_id)
	{
		return set_post_thumbnail($post, $thumbnail_id);
	}

	public function getPermalink($id = 0, $leavename = false)
	{
		return get_permalink($id, $leavename);
	}

	public function getPageLink($post = false, $leavename = false, $sample = false)
	{
		return get_page_link($post, $leavename, $sample);
	}

	public function addRewriteRule($regex, $redirect, $after = 'bottom')
	{
		add_rewrite_rule($regex, $redirect, $after);
	}

	public function addRewriteEndpoint($name, $places, $query_var = null)
	{
		add_rewrite_endpoint($name, $places, $query_var);
	}

	public function addRewriteTag($tag, $regex, $query = '')
	{
		add_rewrite_tag($tag, $regex, $query);
	}

	public function addPermastruct($name, $struct, $args = array())
	{
		return add_permastruct($name, $struct, $args);
	}

	public function flushRewriteRules($hard = true)
	{
		flush_rewrite_rules($hard);
	}

	public function getSiteOption($option, $default = false, $use_cache = true)
	{
		return get_site_option($option, $default, $use_cache);
	}

	public function addSiteOption($option, $value)
	{
		return add_site_option($option, $value);
	}

	public function updateSiteOption($option, $value)
	{
		return update_site_option($option, $value);
	}

	public function deleteSiteOption($option)
	{
		return delete_site_option($option);
	}

	public function wpRemoteGet($url, $args = array())
	{
		return wp_remote_get($url, $args);
	}

	public function wpRemotePost($url, $args = array())
	{
		return wp_remote_post($url, $args);
	}

	public function wpSafeRemoteGet($url, $args = array())
	{
		return wp_safe_remote_get($url, $args);
	}

	public function wpSafeRemotePost($url, $args = array())
	{
		return wp_safe_remote_post($url, $args);
	}

	public function wpSafeRedirect($location, $status = 302)
	{
		wp_safe_redirect($location, $status);
		exit;
	}

	public function wpRedirect($location, $status = 302)
	{
		wp_redirect($location, $status);
		exit;
	}

	public function wpInsertComment($commentdata)
	{
		return wp_insert_comment($commentdata);
	}

	public function wpUpdateComment($commentarr)
	{
		return wp_update_comment($commentarr);
	}

	public function getCommentMeta($comment_id, $key = '', $single = false)
	{
		return get_comment_meta($comment_id, $key, $single);
	}

	public function addCommentMeta($comment_id, $meta_key, $meta_value, $unique = false)
	{
		return add_comment_meta($comment_id, $meta_key, $meta_value, $unique);
	}

	public function deleteCommentMeta($comment_id, $meta_key, $meta_value = '')
	{
		return delete_comment_meta($comment_id, $meta_key, $meta_value);
	}

	public function haveComments()
	{
		return have_comments();
	}

	public function commentsOpen($post_id = null)
	{
		return comments_open($post_id);
	}

	public function getCommentsNumber($post_id = null)
	{
		return get_comments_number($post_id);
	}

	public function commentsNumber($zero = false, $one = false, $more = false, $deprecated = '')
	{
		return comments_number($zero, $one, $more, $deprecated);
	}

	public function getCommentsNumberText($zero = false, $one = false, $more = false)
	{
		return get_comments_number_text($zero, $one, $more);
	}

	public function wpMail($to, $subject, $message, $headers = '', $attachments = array())
	{
		return wp_mail($to, $subject, $message, $headers, $attachments);
	}

	public function wpSetAuthCookie($user_id, $remember = false, $secure = '')
	{
		wp_set_auth_cookie($user_id, $remember, $secure);
	}

	public function wpNewUserNotification($user_id, $plaintext_pass = '')
	{
		wp_new_user_notification($user_id, $plaintext_pass);
	}

	public function getTransient($transient)
	{
		return get_transient($transient);
	}

	public function setTransient($transient, $value, $expiration = 0)
	{
		return set_transient($transient, $value, $expiration);
	}

	public function deleteTransient($transient)
	{
		return delete_transient($transient);
	}

	public function getHomeUrl($blog_id = null, $path = '', $scheme = null)
	{
		return get_home_url($blog_id, $path, $scheme);
	}

	/**
	 * @return string URL for Ajax calls.
	 */
	public function getAjaxUrl()
	{
		return admin_url('admin-ajax.php');
	}

	/**
	 * Redirects to selected page.
	 * Exits after redirect as well.
	 *
	 * @param $pageId int Page ID.
	 * @param $status int Status of redirection.
	 */
	public function redirectTo($pageId, $status = 302)
	{
		$this->wpRedirect($this->getPermalink($pageId), $status);
		exit;
	}

	public function __debugInfo() {
		return 'WordPress Abstraction Layer';
	}
}
