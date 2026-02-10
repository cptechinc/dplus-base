<?php
	namespace Dplus\Content;

	use Dplus\ProcessWire\DplusWire;

	/**
	 * Class for dealing with Pagination for AJAX or non AJAX pages
	 */
	class Paginator {
		use \Dplus\Base\ThrowErrorTrait;
		use \Dplus\Base\MagicMethodTraits;
		use \Dplus\Base\AttributeParser;

		/**
		 * Page Number
		 * @var int
		 */
		protected $pagenbr;

		/**
		 * Number of Items to paginate for
		 * @var int
		 */
		protected $count;

		/**
		 * Page URL
		 * @var \Purl\Url
		 */
		protected $pageurl;

		/**
		 * Ajax Data string data-focus="{focus}" data-loadinto="{loadinto}"
		 * @var string
		 */
		protected $ajaxdata;

		/**
		 * Where to insert pagination path segment
		 * @var string
		 */
		protected $insertafter;

		/**
		 * CONSTRUCTOR
		 * @param int     $pagenbr     What Page Number the Page is on
		 * @param int     $count       Number of items used to determine the number of pages needed
		 * @param string  $pageurl     pageurl to manipulate
		 * @param string  $insertafter if there isn't a urlsegmnet with page(1,3) in it then
		 * @param string  $ajaxdata    String with ajaxdata needed like data-focus='#ajax'
		 */
		public function __construct($pagenbr, $count, $pageurl, $insertafter) {
			$this->pagenbr = $pagenbr;
			$this->count = $count;
			$this->pageurl = new \Purl\Url($pageurl);
			$this->insertafter = $insertafter;
			$this->ajaxdata = !empty($ajaxdata) ? $this->parse_ajaxdata($ajaxdata) : '';
			$this::setup_displayonpage();
		}

		/* =============================================================
			CLASS FUNCTIONS
		============================================================ */
		/**
		 * This will take the pageurl and append the page number to it or editing the page number in the url
		 * @param  int $pagenbr
		 * @return string         url with page number
		 */
		public function paginate($pagenbr) {
			return self::paginate_purl(new \Purl\Url($this->pageurl), $pagenbr, $this->insertafter);
		}

		public function get_totalpages() {
			$totalpages = ceil($this->count / DplusWire::wire('session')->display);
			return $totalpages == 0 ? 1 : $totalpages;
		}


		/* =============================================================
			STATIC FUNCTIONS
		============================================================ */
		/**
		* Find what page number the $url is on
		* @param  \Purl\Url $url
		* @return int     Page Number
		*/
		public static function generate_pagenbr(\Purl\Url $url) {
			if (preg_match("((page)\d{1,3})", $url->path, $matches)) {
				return str_replace('page', '', $matches[0]);
			} else {
				return 1;
			}
		}

		/**
		 * Return URL after modifying it to change / include the page number
		 * @param  string $url         Destination URL
		 * @param  int    $pagenbr     Page Number to change / include in URL
		 * @param  string $insertafter URL segment to paginate after
		 * @return string              Paginated URL
		 */
		public static function paginate_url($url, $pagenbr, $insertafter) {
			if (strpos($url, 'page') !== false) {
				$regex = "((page)\d{1,3})";
				$replace = ($pagenbr > 1) ? $replace = "page".$pagenbr : "";
				$newurl = preg_replace($regex, $replace, $url);
			} else {
				$insertafter = str_replace('/', '', $insertafter)."/";
				$regex = "(($insertafter))";
				$replace = ($pagenbr > 1) ? $insertafter."page".$pagenbr."/" : $replace = $insertafter;
				$newurl = preg_replace($regex, $replace, $url);
			}
			return $newurl;
		 }

		/**
		 * Paginates the Purl\Url object by adding the page number to its path
		 * @param  \Purl\Url $url         URL Object
		 * @param  int      $pagenbr     Page Number to page to
		 * @param  string   $insertafter Path Segment to insert the page number
		 * @return \Purl\Url              Url object with the paginated path
		 */
		public static function paginate_purl(\Purl\Url $url, $pagenbr, $insertafter) {
			$insertafter = trim($insertafter, '/');
			$path = $url->getPath();

			if (strpos($path, 'page') !== false) {
				$regex = "((page)\d{1,3})";
				$replace = ($pagenbr > 1) ? $replace = "page$pagenbr" : "";
				$newpath = preg_replace($regex, $replace, $path);
			} else {
				$regex = "(($insertafter))";
				$replace = ($pagenbr > 1) ? "{$insertafter}/page{$pagenbr}/" : $insertafter;
				$newpath = preg_replace($regex, $replace, $path);
			}

			$url->path = $newpath;
			return $url;
		}

		/**
		* Initializes the Dplus\ProcessWire\DplusWire::wire('session')->display value;
		*/
		public static function setup_displayonpage() {
			if (DplusWire::wire('input')->get->display) {
				DplusWire::wire('session')->display = DplusWire::wire('input')->get->text('display');
			} else {
				if (!DplusWire::wire('session')->display) {
					DplusWire::wire('session')->display = DplusWire::wire('config')->showonpage;
				}
			}
		}
	}
