<?php
class LeftToRightHelper extends AppHelper {

	var $helpers = array('Html');

	public function draw($endToEndFeeds = array(), $controller = '', $action = '') {

		$this->controller = $controller;
		$this->action = $action;

		if (!$endToEndFeeds) return '';

		$this->output = '<div class="side-to-side"><table><tr>';

		$this->__inFeeds($endToEndFeeds['in']);
		$this->__thisFeed($endToEndFeeds['this']);
		$this->__outFeeds($endToEndFeeds['out']);

		$this->output .= '</tr></table></div>';

		return $this->output;

	}

	private function __inFeeds($feeds = array()) {

		$this->output .= '<td>';

			$this->output .= '<table>';

				if (empty($feeds)) {

					$this->output .= '<tr><td>';
						$this->__node('tertiary', 'No dependencies.');
					$this->output .= '</td></tr>';

				} else {

					$feedCount = count($feeds);

					foreach ($feeds as $key => $feed) {
						$this->__inFeed($feed, $key+1, $feedCount);
						if ($feedCount > $key+1) {
							$this->__spacerRow('right', 4);
						}
					}

				}

			$this->output .= '</table>';

		$this->output .= '</td>';

	}

	private function __thisFeed($feed) {

		$this->output .= '<td>';

			$this->output .= '<table>';

				$this->output .= '<tr class="top">';
					$this->output .= '<td colspan="3"></td>';
				$this->output .= '</tr>';

				$this->output .= '<tr>';

					$this->__lineTd(array('bottom'));

					$this->output .= '<td rowspan="2">';

						$url = $this->Html->url(
							array(
								'controller' => $this->controller,
								'action' => $this->action,
								$feed['id']
							)
						);

						$content = '<a href="' . $url . '">' . $feed['title'] . '</a>';

						$this->__node(
							'primary',
							$content
						);

					$this->output .= '</td>';

					$this->__lineTd(array('bottom'));

				$this->output .= '</tr>';

				$this->output .= '<tr>';

					$this->__lineTd(array('top'));
					$this->__lineTd(array('top'));

				$this->output .= '</tr>';

				$this->output .= '<tr>';
					$this->output .= '<td colspan="3"></td>';
				$this->output .= '</tr>';

			$this->output .= '</table>';

		$this->output .= '</td>';

	}

	private function __outFeeds($feeds = array()) {

		$this->output .= '<td>';

			$this->output .= '<table>';

				if (empty($feeds)) {

					$this->output .= '<tr><td>';
						$this->__node('tertiary', 'No dependents.');
					$this->output .= '</td></tr>';

				} else {

					$feedCount = count($feeds);
					foreach ($feeds as $key => $feed) {
						$this->__outFeed($feed, $key+1, $feedCount);
						if ($feedCount > $key+1) {
							$this->__spacerRow('left', 4);
						}
					}

				}

			$this->output .= '</table>';

		$this->output .= '</td>';

	}

	private function __inFeed($feed, $key, $feedCount) {

		$feedReceiptCount = $feed['business_function_feed_receive_count'];

		$this->output .= '<tr>';

			$this->output .= '<td rowspan="2">';

				$content = 'Depends on ' . $feedReceiptCount . ' other business ' . __n('function', 'functions', $feedReceiptCount) . '.';
				$this->__node(
					'tertiary',
					$content
				);

			$this->__lineTd(array('bottom'));

			$this->output .= '<td rowspan="2">';

				$url = $this->Html->url(
					array(
						'controller' => $this->controller,
						'action' => $this->action,
						$feed['id']
					)
				);

				$content = '<a href="' . $url . '">' . $feed['title'] . '</a>';

				$this->__node(
					'secondary',
					$content
				);

			$this->output .= '</td>';

			if ($key == 1) {
				$this->__lineTd(array('bottom'));
			} else {
				$this->__lineTd(array('bottom', 'right'));
			}

		$this->output .= '</tr>';

		$this->output .= '<tr>';

			$this->__lineTd(array('top'));

			if ($key == $feedCount) {
				$this->__lineTd(array('top'));
			} else {
				$this->__lineTd(array('top', 'right'));
			}

		$this->output .= '</tr>';

	}

	private function __outFeed($feed, $key, $feedCount) {

		$feedSendCount = $feed['business_function_feed_count'];

		$this->output .= '<tr>';

			if ($key === 1) {
				$this->__lineTd(array('bottom'));
			} else {
				$this->__lineTd(array('bottom', 'left'));
			}

			$this->output .= '<td rowspan="2">';

				$url = $this->Html->url(
					array(
						'controller' => $this->controller,
						'action' => $this->action,
						$feed['id']
					)
				);

				$content = '<a href="' . $url . '"><strong>' . $feed['title'] . '</strong></a>';

				$this->__node(
					'secondary',
					$content
				);

			$this->output .= '</td>';

			// $this->__lineTd(array('bottom'));
			if ($feedSendCount) {
				$this->__lineTd(array('bottom'));
			} else {
				$this->__lineTd();
			}

			$this->output .= '<td rowspan="2">';

				if ($feedSendCount) {
					$content = $feedSendCount . __n(' dependent', ' dependents', $feedSendCount) . '.';

					$this->__node(
						'tertiary',
						$content
					);
				}

			$this->output .= '</td>';

		$this->output .= '</tr>';

		$this->output .= '<tr>';

			if ($key === $feedCount) {
				$this->__lineTd(array('top'));
			} else {
				$this->__lineTd(array('top', 'left'));
			}

			if ($feedSendCount) {
				$this->__lineTd(array('top'));
			} else {
				$this->__lineTd();
			}

		$this->output .= '</tr>';



	}

	private function __lineTd($lines = array(), $colspan = null) {

		$class = '';

		if ($lines) {
			foreach ($lines as $line) {
				$class .= ' line-' . $line;
			}
			$class = ' class="' . trim($class) . '"';
		}

		if ($colspan) {
			$colspan = ' colspan="' . $colspan . '"';
		}

		$this->output .= '<td' . $colspan . $class . '></td>';

	}

	private function __spacerRow($side, $colspan = null) {
		$this->output .= '<tr class="spacer">';
			$this->__lineTd(array($side), $colspan);
		$this->output .= '</tr>';
	}

	public function __node($class, $content) {
		$this->output .= '<div class="node ' . $class . '-node">' . $content . '</div>';
	}

}