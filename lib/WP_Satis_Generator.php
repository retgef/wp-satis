<?php
/**
 * Satis config generator for WordPress packages
 */
class WP_Satis_Generator{

	/**
	 * Packages index
	 * @var array
	 */
	var $packages = array();

	/**
	 * Setter
	 * @param string $k key
	 * @param string $v value
	 */
	function set($k, $v){
		$this->$k = $v;
	}

	/**
	 * Build controller
	 * @return string JSON
	 */
	function build(){
		$this->generate_packages();
		return $this->render_json();
	}

	/**
	 * Get included WordPress versions limited by user
	 * @return mixed array/(bool)false
	 */
	function get_included_versions(){
		if($included = $this->included_versions){
			$parts = explode(',', $included);
			foreach($parts as $k => $v){
				$parts[$k] = trim($v);
			}
			return $parts;
		}
		else{
			return false;
		}
	}

	/**
	 * Generate packages for tags and branches
	 * @return mixed array/(bool)false
	 */
	function generate_packages(){
		$tags = $this->get_tags_list();

		$included = $this->get_included_versions();

		if(is_array($tags) && $tags){
			foreach($tags as $tag){
				$include = true;
				if($included && !in_array($tag, $included)){
					$include = false;
				}
				if($include){
					$this->add_package('tag', $tag);
				}
			}
		}

		$branches = $this->get_branches_list();
		if(is_array($branches) && $branches){
			foreach($branches as $branch){
				$include = true;
				if($included && (!in_array($branch, $included) && !in_array('dev-'.$branch, $included))){
					$include = false;
				}
				if($include){
					$this->add_package('branch', $branch);
				}
			}
		}
	}

	/**
	 * List tags from WordPress/WordPress git repo
	 * @return array versions
	 */
	function get_tags_list(){
		$list = `git ls-remote --tags git@github.com:WordPress/WordPress.git | cut -d '/' -f 3`;
		if($list){
			$parts = explode("\n", trim($list));
			$tags = $parts ? $parts : false;
		}
		return $tags;
	}

	/**
	 * List branches from WordPress/WordPress git repo
	 * @return array branch names
	 */
	function get_branches_list(){
		$list = `git ls-remote --heads git@github.com:WordPress/WordPress.git | cut -d '/' -f 3`;
		if($list){
			$parts = explode("\n", trim($list));
			$branches = $parts ? $parts : false;
		}
		return $branches;
	}

	/**
	 * Add package to index
	 * @param string $type [tag|branch]
	 * @param string $ver  version name/number
	 */
	function add_package($type, $ver){
		$version = $type === 'tag' ? $ver : "dev-$ver";
		$package = array(
			'type' => 'package',
			'package' => array(
				'name' => 'wordpress/wordpress',
				'description' => 'WordPress is web software you can use to create a beautiful website or blog. We like to say that WordPress is both free and priceless at the same time.',
				'version' => $version,
				'keywords' => array('blog', 'cms'),
				'type' => 'wordpress-core',
				'homepage' => 'http://wordpress.org/',
				'license' => 'GPL-2.0+',
				'authors' => array(
					array(
						'name' => 'WordPress Community',
						'homepage' => 'http://wordpress.org/about/'
					)
				),
				'support' => array(
					'issues' => 'http://core.trac.wordpress.org/',
					'forum' => 'http://wordpress.org/support/',
					'wiki' => 'http://codex.wordpress.org/',
					'irc' => 'irc://irc.freenode.net/wordpress',
					'source' => 'http://core.trac.wordpress.org/browser'
				),
				'require' => array(
					'php' => '>='.$this->get_php_dependency(str_replace('-branch', '', $version))
				)
			)
		);
		switch ($type) {
			case 'tag':
				$package['package']['dist'] = array(
					'url' => "http://github.com/WordPress/WordPress/archive/$version.zip",
                	'type' => 'zip'
				);
				break;
			case 'branch':
				$package['package']['source'] = array(
					'url' => 'git@github.com:WordPress/WordPress.git',
					'type' => 'git',
					'reference' => $ver
				);
				break;
		}
		$this->packages[] = $package;
	}

	/**
	 * Map PHP version dependency to WordPress version
	 * @param  string $ver tag/branch
	 * @return string      version
	 */
	function get_php_dependency($ver){
		switch ($ver) {
			case $ver >= 3.2:
				$php = '5.2.4';
				break;
			case $ver >= 2.9 && $ver < 3.2:
				$php = '4.3';
				break;
			case $ver >= 2.5 && $ver < 2.9:
				$php = '4.3';
				break;
			case $ver < 2.5:
				$php = '4.2';
				break;
		}
		return $php;
	}

	/**
	 * Render Satis JSON
	 * @return string JSON
	 */
	function render_json(){
		$satis = array(
			'name' => $this->name,
			'homepage' => $this->homepage,
			'require-all' => true
		);
		if(property_exists($this, 'archives') && $this->archives){
			$satis['archive'] = array(
				'directory' => $this->archives_dir,
				'format' => $this->archives_format,
				'prefix-url' => $this->archives_prefix,
				'skip-dev' => $this->archives_skip_dev
			);

		}
		$satis['repositories'] = $this->packages;
		return json_encode($satis, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	}
}