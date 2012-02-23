<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event side info view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Info extends View_Article {

	/**
	 * @var  Model_Event
	 */
	public $event;

	/**
	 * @var  string  Article class
	 */
	public $id = 'event-info';

	/**
	 * @var  integer  View grid span
	 */
	public $span = 4;

	/**
	 * Create new article.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->event = $event;

		$this->title = HTML::time(Date('l ', $this->event->stamp_begin) . ', ' . Date::format(Date::DMY_LONG, $this->event->stamp_begin), $this->event->stamp_begin, true);

		$this->meta = __('Added') .  ' ' . HTML::time(Date::format(Date::DMY_SHORT, $this->event->created), $this->event->created);
		if ($this->event->modified) {
			$this->meta .= ', ' . __('last modified') . ' ' . HTML::time(Date::short_span($this->event->modified, false), $this->event->modified);
		}

	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Stamp
		if ($this->event->stamp_begin != $this->event->stamp_end) {
			echo $this->event->stamp_end ?
				'<i class="icon-time"></i> ' . __('From :from to :to', array(
					':from' => HTML::time(Date::format('HHMM', $this->event->stamp_begin), $this->event->stamp_begin),
					':to'   => HTML::time(Date::format('HHMM', $this->event->stamp_end), $this->event->stamp_end)
				)) :
				'<i class="icon-time"></i> ' . __('From :from onwards', array(
					':from' => HTML::time(Date::format('HHMM', $this->event->stamp_begin), $this->event->stamp_begin),
				)), '<br />';
		}


		// Price
		if ($this->event->price == 0) {
			echo '<i class="icon-tag"></i> ', __('Free entry'), '<br />';
		} else if ($this->event->price > 0) {
			echo '<i class="icon-tag"></i> ', __('Tickets :price', array(':price' => '<var>' . Num::format($this->event->price, 2, true) . '&euro;</var>'));
			echo $this->event->price2 !== null ? ', ' . __('presale :price', array(':price' => '<var>' . Num::format($this->event->price2, 2, true) . '&euro;</var>')) : '', '<br />';
		}


		// Age limit
		if ($this->event->age > 0) {
			echo  '<i class="icon-user"></i> ', __('Age limit'), ': ', __(':years years', array(':years' => '<var>' . $this->event->age . '</var>')), '<br />';
		}


		// Homepage
		if (!empty($this->event->homepage)) {
			echo '<i class="icon-home"></i> ', HTML::anchor($this->event->homepage);
		}


		// Tags
		if ($tags = $this->event->tags()) {
			echo '<i class="icon-music"></i> ', implode(', ', $tags);
		} else if (!empty($this->event->music)) {
			echo '<i class="icon-music"></i> ', $this->event->music;
		}


		// Venue
		if ($_venue = $this->event->venue()) {

			// Venue found from db
			$venue   = HTML::anchor(Route::model($_venue), HTML::chars($_venue->name));
			$address = HTML::chars($_venue->address) . ', ' . HTML::chars($_venue->city_name);
			$info    = HTML::anchor(Route::model($_venue), __('Venue info'));

			if ($_venue->latitude) {
				$map = array(
					'marker'     => HTML::chars($_venue->name),
					'infowindow' => HTML::chars($_venue->address) . '<br />' . HTML::chars($_venue->city_name),
					'lat'        => $_venue->latitude,
					'long'       => $_venue->longitude
				);
				Widget::add('foot', HTML::script_source('
head.ready("anqh", function() {
	$("#event-info a[href=#map]").on("click", function toggleMap(event) {
		$("#map").toggle("fast", function openMap() {
			$("#map").googleMap(' .  json_encode($map) . ');
		});

		return false;
	});
});
'));
			}

		} else if ($this->event->venue_name) {

			// No venue in db
			$venue   = HTML::chars($this->event->venue_name);
			$address = HTML::chars($this->event->city_name);
			$info    = $this->event->venue_url ? HTML::anchor($this->event->venue_url, HTML::chars($this->event->venue_url)) : '';

		} else {

			// Venue not set
			$venue   = $this->event->venue_hidden ? __('Underground') : __('(Unknown)');
			$address = HTML::chars($this->event->city_name);
			$info    = '';

		}
		echo '<address><strong><i class="icon-map-marker"></i> ', $venue, '</strong><br />';
		echo $address, '<br />';
		if (isset($map)) {
			echo HTML::anchor('#map', __('Toggle map')), '<br />';
			echo '<div id="map" style="display: none">', __('Map loading'), '</div><br />';
		}
		echo '</address>';
		echo $info;


		return ob_get_clean();
	}

}