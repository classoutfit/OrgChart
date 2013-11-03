<?php
/**
* This helper takes a $nodes input and outputs a nested table that has a central node
* ('this' feed) and a left side (the incoming or 'in' feeds) and a right side
* (the outgoing or 'out' feeds)
 */
class LeftToRightHelper extends AppHelper {

	var $helpers = array('Html');

	public function draw($endToEndFeeds = array(), $controller = '', $action = '') {

		if (!$endToEndFeeds) return '';

		$this->controller = $controller;
		$this->action = $action;

		// start with a div and table
		$this->output = '<div class="org-side-to-side"><table><tr>';

			// Process the in side
			$this->__inFeeds($endToEndFeeds['in']);

			// Process 'this' node
			$this->__thisFeed($endToEndFeeds['this'], count($endToEndFeeds['in']), count($endToEndFeeds['out']));

			// Process the out side
			$this->__outFeeds($endToEndFeeds['out']);

		// Close the table and div
		$this->output .= '</tr></table></div>';

		// Return the output so the view can render it
		return $this->output;

	}

/**
 * Process the in side - adds to $this->output
 *
 * @param array $feeds - the feeds to show
 * @return void
 */
	private function __inFeeds($feeds = array()) {

		// Start with a td - the whole left hand side is a single table cell
		$this->output .= '<td>';

			// Put a table inside it
			$this->output .= '<table>';

				// If there are no feeds to display, simply show a single 'no feeds' node
				if (empty($feeds)) {

					// Start with a tr and td
					$this->output .= '<tr><td>';

						// Output a tertiary node with simple text
						$this->__node('tertiary', 'No dependencies.');

					// Close out the cell and row
					$this->output .= '</td></tr>';

				} else {

					// First count the feeds
					// This is useful for working out the first and last row
					$feedCount = count($feeds);

					// Loop through the feeds
					foreach ($feeds as $key => $feed) {

						// Output an in feed
						$this->__inFeed($feed, $key+1, $feedCount);

						// If this is not the last feed...
						if ($feedCount > $key+1) {
							// ... we need a spacer row to separate it from the next feed
							$this->__spacerRow('right', 4);
						}
					}

				}

			// Close out the table
			$this->output .= '</table>';

		// Close out the cell
		$this->output .= '</td>';

	}

/**
 * Process a single 'in' feed - adds to $this->output
 *
 * @param array $feed - the feed to show
 * @param array $key - the numeric sequence of this feed
 * @param array $feedCount - the total number of feeds
 * @return void
 */
	private function __inFeed($feed, $key, $feedCount) {

		// Find out how many feeds this one receives - these are the dependents
		$dependents = $feed['core_priority_feed_receive_count'];

		// Each feed is in its own row
		$this->output .= '<tr>';

			// First output the 'dependent' cell
			// It goes in a cell that spans two rows
			$this->output .= '<td rowspan="2">';

				// For simplicity, put the content into a variable
				$content = 'Depends on ' . $dependents . ' other business ' . __n('function', 'functions', $dependents) . $this->__arrow();

				// Then output a tertiary node
				$this->__node(
					'tertiary',
					$content
				);

			// Close the cell
			$this->output .= '</td>';

			// Then output a cell with a bottom border - it spans just one row
			$this->__lineTd(array(
				'lines' => array(
					'bottom'
				),
				'style' => 'tertiary'
			));

			// Now output the in feed
			// It goes in a cell that spans two rows
			$this->output .= '<td rowspan="2">';

				// Set up a $url variable
				$url = $this->Html->url(
					array(
						'controller' => $this->controller,
						'action' => $this->action,
						$feed['id']
					)
				);

				// Construct the content
				$content = '<a href="' . $url . '">' . $feed['title'] . '</a>' . $this->__arrow();

				// Now output a secondary node
				$this->__node(
					'secondary',
					$content
				);

			// Close the cell
			$this->output .= '</td>';

			// Now work out which lines to draw between this feed and the main feed
			// This is the top cell (a bottom one follows)

			// if this is the first feed we just need a bottom order regardless
			if ($key == 1) {

				// Output a cell with a bottom border
				$this->__lineTd(array(
					'lines' => array(
						'bottom'
					)
				));

			} else {

				// Otherwise we need a bottom border and a righ border
				// TODO JB - check total feeds?
				$this->__lineTd(array(
					'lines' => array(
						'bottom',
						'right'
					)
				));
			}

		// Close the row
		$this->output .= '</tr>';

		// The next row has just two cells
		// They provide top borders where needed
		$this->output .= '<tr>';

			// Add a tertiary top border
			$this->__lineTd(array(
				'lines' => array(
					'top'
				),
				'style' => 'tertiary'
			));

			// If this is the last feed just add a top border
			if ($key == $feedCount) {

				$this->__lineTd(array(
					'lines' => array(
						'top'
					)
				));

			} else {

				// otherwise draw a top and right border
				$this->__lineTd(array(
					'lines' => array(
						'top',
						'right'
					)
				));

			}

		// Close the row
		$this->output .= '</tr>';

	}

/**
 * Process the middle column; 'this' node - adds to $this->output
 *
 * @param array $feed - the feed to show
 * @return void
 */
	private function __thisFeed($feed, $inFeedCount = 0, $outFeedCount = 0) {

		// Start the middle column
		$this->output .= '<td>';

			// Nest a table
			$this->output .= '<table>';

				// Start with a row
				$this->output .= '<tr>';

					if ($inFeedCount > 1) {
						// Add a cell with a bottom border to join this onto its in feeds
						$this->__lineTd(array(
							'lines' => array(
								'bottom'
							)
						));
					}

					// Now a cell that spans 2 rows
					$this->output .= '<td rowspan="2">';

						// Create a url
						$url = $this->Html->url(
							array(
								'controller' => $this->controller,
								'action' => $this->action,
								$feed['id']
							)
						);

						// Build the content
						$content = '<a href="' . $url . '" class="primary">' . $feed['title'] . '</a>' . $this->__arrow();

						if (empty($feed['BusinessActivity'])) {
							$content .= '<p>No supporting business activities</p>';
						} else {
							$content .= '<p>Supporting business activities:</p>';

							$content .= '<ul>';

							foreach ($feed['BusinessActivity'] as $id => $businessActivity) {
								$content .= '<li>' . $this->Html->link(
									$businessActivity,
									array(
										'controller' => 'business_activities',
										'action' => 'core_priorities',
										$id
									),
									array(
										'class' => 'business_activity'
									)
								) . '</li>';
							}

							$content .= '</ul>';
						}

						// Outpt the primary node
						$this->__node(
							'primary',
							$content
						);

					// Close the cell
					$this->output .= '</td>';

					if ($outFeedCount > 1) {
						// Add a cell with a bottom border to join it to the out feeds
						$this->__lineTd(array(
							'lines' => array(
								'bottom'
							)
						));
					}

				// Close the row
				$this->output .= '</tr>';

				// Start the row
				$this->output .= '<tr>';

					if ($inFeedCount > 1) {
						$this->__lineTd(array(
							'lines' => array(
								'top'
							)
						));
					}

					if ($outFeedCount > 1) {
						$this->__lineTd(array(
							'lines' => array(
								'top'
							)
						));
					}

				// Close the row
				$this->output .= '</tr>';

			$this->output .= '</table>';

		$this->output .= '</td>';

	}

/**
 * Process the out side - adds to $this->output
 *
 * @param array $feeds - the feeds to show
 * @return void
 */
	private function __outFeeds($feeds = array()) {

		// Start with cell (the whole right hand side) and a nested table
		$this->output .= '<td><table>';

			// If there are no feeds, output a text based row
			if (empty($feeds)) {

				// Open a row and cell
				$this->output .= '<tr>';

					$this->__lineTd(array(
						'lines' => array(
							'bottom'
						)
					));

					$this->output .= '<td rowspan="2">';

						// Output a tertiary node
						$this->__node(
							'tertiary',
							'No dependents'
						);

				// Close the cell and row
				$this->output .= '</tr>';

				$this->output .= '<tr>';

					$this->__lineTd(array(
						'lines' => array(
							'top'
						)
					));

				$this->output .= '</tr>';

			} else {

				// Count the feeds to output
				$feedCount = count($feeds);

				// Loop through the feeds
				foreach ($feeds as $key => $feed) {

					// Output each feed
					$this->__outFeed($feed, $key+1, $feedCount);

					// If this is not the last feed, we need a row to separate this from the next one
					if ($feedCount > $key+1) {
						// Output a spacer row
						$this->__spacerRow('left', 4);
					}

				}

			}

		// Close the table and column
		$this->output .= '</table></td>';

	}

/**
 * Process a single 'out' feed - adds to $this->output
 *
 * @param array $feed - the feed to show
 * @param array $key - the numeric sequence of this feed
 * @param array $feedCount - the total number of feeds
 * @return void
 */
	private function __outFeed($feed, $key, $feedCount) {

		// Count the feeds to output
		$dependencies = $feed['core_priority_feed_count'];

		// Start a row
		$this->output .= '<tr>';

			if ($key === 1) {
				// If this is the first row just show a bottom border
				$this->__lineTd(array(
					'lines' => array(
						'bottom'
					)
				));
			} else {
				// Else show a bottom and a left border
				$this->__lineTd(array(
					'lines' => array(
						'bottom',
						'left'
					)
				));
			}

			// The node will span 2 rows
			$this->output .= '<td rowspan="2">';

				// Get the url
				$url = $this->Html->url(
					array(
						'controller' => $this->controller,
						'action' => $this->action,
						$feed['id']
					)
				);

				// Make the content
				$content = '<a href="' . $url . '">' . $feed['title'] . '</a>';
				if ($dependencies) {
					$content .= $this->__arrow();
				}

				// Output the node
				$this->__node(
					'secondary',
					$content
				);

			// Close the cell
			$this->output .= '</td>';

			// If there are dependencies
			if ($dependencies) {
				// output a tertiary bottom border
				$this->__lineTd(array(
					'lines' => array(
						'bottom'
					),
					'style' => 'tertiary'
				));
			} else {
				// otherwise output a plain cell with no border
				$this->__lineTd();
			}

			// Start a cell that spans 2 rows
			$this->output .= '<td rowspan="2">';

				if ($dependencies) {

					// Create the content
					$content = $dependencies . __n(' dependent', ' dependents', $dependencies);

					// Output a tertiary node with text content
					$this->__node(
						'tertiary',
						$content
					);
				}

			// Close the cell
			$this->output .= '</td>';

		// Close the row
		$this->output .= '</tr>';

		// Open a row for completing the borders
		$this->output .= '<tr>';

			if ($key === $feedCount) {
				// If this is the last feed draw a top border
				$this->__lineTd(array(
					'lines' => array(
						'top'
					)
				));
			} else {
				// Otherwise draw a top and left border
				$this->__lineTd(array(
					'lines' => array(
						'top',
						'left'
					)
				));
			}

			if ($dependencies) {
				// If there are dependencies draw a top tertiary border
				$this->__lineTd(array(
					'lines' => array(
						'top'
					),
					'style' => 'tertiary'
				));
			} else {
				// Otherwise draw an empty cell
				$this->__lineTd();
			}

		// Close the row
		$this->output .= '</tr>';

	}

/**
 * Draw a cell with borders
 *
 * @param array $options:
 * @param array $lines - the border sides to draw
 * @param array $colspan - how many columns to span
 * @param array $style - a CSS class to apply
 * @return void
 */
	private function __lineTd(
		$options = array(
			'lines' => array(),
			'colspan' => null,
			'style' => null
		)
	) {

		// Merge the options with the defaults
		$options = array_merge(
			array(
				'lines' => array(),
				'colspan' => null,
				'style' => null
			),
			$options
		);

		// Extract them into variables
		extract($options);

		$class = '';

		if ($lines) {

			// The classes that define which borders will be shown
			foreach ($lines as $line) {
				$class .= ' line-' . $line;
			}

			// If there is a style passed, add it to the line styles
			if ($style) {
				$class .= ' ' . $style;
			}

			// Then strip off any spaces
			$class = ' class="' . trim($class) . '"';

		}

		if ($colspan) {
			// Do we need to span the td across columns?
			$colspan = ' colspan="' . $colspan . '"';
		}

		// Output the styled td
		$this->output .= '<td' . $colspan . $class . '></td>';

	}

/**
 * Output a blank row with a border on one side
 *
 * @param array $side - the side the borde should be drawn on
 * @param int $colspan - how many columns to span
 * @return void
 */
	private function __spacerRow($side, $colspan = null) {

		// Start a row
		$this->output .= '<tr class="spacer">';

			// Add a cell with a border side and a colspan
			$this->__lineTd(array(
				'lines' => array($side),
				'colspan' => $colspan
			));

		// Close the row
		$this->output .= '</tr>';

	}

/**
 * Output a row with a number of cells and borders on one side
 *
 * @param array $side - the side the borde should be drawn on
 * @param int $cellCount - how many cells to include
 * @return void
 */
	private function __lineRow($side, $cellCount = 1) {

		// Start the row
		$this->output .= '<tr>';

			// Add the right number of bordered cells
			for ($i = 1; $i <= $cellCount; $i++) {

				$this->__lineTd(array(
					'lines' => array(
						$side
					)
				));

			}

		// Close the row
		$this->output .= '</tr>';

	}

/**
 * Output a node div with content
 *
 * @param string $class - the class of the node
 * @param string $content - the actual content
 * @return void
 */
	private function __node($class, $content) {

		// Output the div
		$this->output .= '<div class="node ' . $class . '-node">' . $content . '</div>';

	}

/**
 * Output a span that contains an arrow of a given direction
 *
 * @param string $direction - the way the arrow should point
 * @return string
 */
	private function __arrow($direction = 'right') {

		// Return a span with text content that represents an arrow
		return '<span class="arrow">&rarr;</span>';
	}

}