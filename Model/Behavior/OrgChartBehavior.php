<?php
App::uses('ModelBehavior', 'Model');

class OrgChartBehavior extends ModelBehavior {

	public function hierarchy(Model $model, $id = null) {

		if (!$id) return array();

		$data = $model->findById(
			$id,
			array(
				'parent_id'
			)
		);

		if (!$data) return array();

		$fields = array(
			'id',
			'title',
			'parent_id',
			'lft',
			'rght',
			'child_count',
			'path'
		);

		// Now get the children of the one we want
		$children = $model->find(
			'all',
			array(
				'conditions' => array(
					'parent_id' => $id
				),
				'fields' => $fields,
				'order' => 'title'
			)
		);

		if ($children) {
			$_children = array();
			foreach ($children as $key => $child) {
				$child = $child[$model->alias];
				$child['nodeClass'] = 'child';
				$child['child_node'] = $this->_addChildCount($child);
				$_children[] = $child;
			}
			$children = $_children;
		}

		// Now get all children of the parent, which will also include the one we want
		$siblings = $model->find(
			'all',
			array(
				'conditions' => array(
					'parent_id' => $data[$model->alias]['parent_id']
				),
				'fields' => $fields,
				'order' => 'title'
			)
		);

		// Now the siblings - highlighting 'this' one
		if ($siblings) {

			$_siblings = array();

			foreach ($siblings as $key => $sibling) {

				$sibling = $sibling[$model->alias];

				if ($sibling['id'] == $id) {
					$sibling['nodeClass'] = 'selected';
					if ($children) {
						$sibling['children'] = $children;
					}
				} else {
					$sibling['nodeClass'] = 'sibling';
					$sibling['child_node'] = $this->_addChildCount($sibling);
				}

				$_siblings[] = $sibling;
			}

			$siblings = $_siblings;

		}

		// Get the path back to the top.
		$parents = $model->getPath($id, $fields);

		if (!$parents) {

			$hierarchy = $siblings;

		} else {

			$hierarchy = array();

			$parents = array_reverse($parents);

			$parentCount = count($parents);

			foreach ($parents as $key => $parent) {

				$parent = $parent[$model->alias];

				if ($parent['id'] == $id) {
					$parent['nodeClass'] = 'selected';
				} else {
					$parent['nodeClass'] = 'parent';
				}

				if ($parentCount && $key === 0) {
					$parent['children'] = $children;
				} elseif ($parentCount && $key == 1) {
					$parent['children'] = $siblings;
				} elseif ($key > 1) {
					$parent['children'] = $hierarchy;
				}

				// $parent['nodeClass'] = 'parent';

				$hierarchy[0] = $parent;

			}
		}
// die(debug($hierarchy));
		return $hierarchy;

	}

	private function _addChildCount($node) {

		if (empty($node['child_count'])) return;

		$childCount = $node['child_count'];

		if ($childCount) {
			return array(
				'title' => $childCount . ' direct ' . __n(' child', ' children', $childCount),
				'id' => $node['id'],
				'child_count' => 0,
				'nodeClass' => 'child_count'
			);
		}

	}

}