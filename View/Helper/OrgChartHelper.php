<?php
class OrgChartHelper extends AppHelper {

	var $helpers = array('Html');

	public function draw($nodes = array(), $controller = '', $action = '') {

		$this->controller = $controller;
		$this->action = $action;

		$this->output = '<div class="orgChart">';

		foreach ($nodes as $node) {
			$this->_addNode($node);
		}

		$this->output .= '</div>';

		return $this->output;
	}

	private function _addNode($node = array(), $addTd = false) {

		if ($addTd) {
			$this->output .= '<td colspan="2">';
		}

		$this->output .= '<table>';

		if (!empty($node['children'])) {
			$childCount = count($node['children']);
			$colspan = $childCount * 2;
		} else {
			$childCount = 0;
			$colspan = 2;
		}

		$this->output .= $this->_nodeRow($node, $colspan);

		if ($childCount) {

			$this->_downLineRow($childCount);

			if ($childCount > 1) {
				$this->_lineRow($childCount);
			}

			$this->output .= '<tr>';

				foreach ($node['children'] as $childNode) {
                	$this->_addNode($childNode, true);
				}

			$this->output .= '</tr>';

		} elseif (!empty($node['child_node'])) {

			$this->_downLineRow(1, 'child_count');

			$this->output .= '<tr>';
				$this->_addNode($node['child_node'], true);
			$this->output .= '</tr>';

		}

		$this->output .= '</table>';

		if ($addTd) {
			$this->output .= '</td>';
		}
	}

	private function _nodeRow($node, $colspan) {

		$link = $this->Html->url(
			array(
				'controller' => $this->controller,
				'action' => $this->action,
				$node['id']
			)
		);

		$nodeCell = '<td class="node" colspan="' . $colspan . '">';
			$nodeCell .= '<div class="node ' . $node['nodeClass'] . '">';
				$nodeCell .= '<a href="' . $link . '">';
					$nodeCell .= '<p><strong>' . $node['title'] . '</strong></p>';
				$nodeCell .= '</a>';
			$nodeCell .= '</div>';
		$nodeCell .= '</td>';

		$this->output .= '<tr class="nodes">' . $nodeCell . '</tr>';

	}

	private function _downLineRow($colspan, $class = '') {

		if ($class) {
			$class = 'lines ' . $class;
		} else {
			$class = 'lines';
		}

		$this->output .= '<tr><td colspan="' . $colspan * 2 . '"><table><tr class="' . $class . '"><td class="line left"></td><td class="line right"></td></tr></table></td></tr>';

	}

	private function _lineRow($childCount) {

		$tr = '<tr class="lines">';

		for ($n=1; $n <= $childCount; $n++) {

			if ($n === 1) {
				$class = array(
					'left' => 'line left',
					'right' => 'line right top'
				);
			} elseif ($n === $childCount) {
				$class = array(
					'left' => 'line left top',
					'right' => 'line right'
				);
			} else {
				$class = array(
					'left' => 'line left top',
					'right' => 'line right top'
				);
			}
			$tr .= '<td class="' . $class['left'] . '"></td>';
			$tr .= '<td class="' . $class['right'] . '"></td>';
		}

		$tr .= "</tr>";

		$this->output .= $tr;

	}

}