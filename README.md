OrgChart
=============

A behaviour to extract data from a tree based table and a helper to display it in an organisation chart manner. There are jQuery versions of this but as they simply produce HTML I thought it would be sensible to create a helper that produces the HTML without relying on jQuery.

See screenshot for example.

What it does
------------

The OrgChart plugin takes data stored in a tree based table and represents it in an organisation chart style. It consists of a behaviour, a helper and some CSS (LESS). Each node is clickable, taking you to the hierarchy view of the clicked node, which makes it really simple to click around the data.

To install the Plugin
---------------------
* Copy the Plugin into app/Plugin/OrgChart
* Edit app/Config/bootstrap.php:
	CakePlugin::load(array(
		// your existing plugins...,
		'OrgChart'
	);

To display tree based data in an organisation chart style
---------------------------------------------------------

To keep the code clips simple to understand, I have assumed we are working with a Department model (so the controller is departments) and the view function is 'hierarchy'.

1) Attach the behaviour to your model. This assumes:
* You have attached the Tree behaviour
* You have fields called 'id', title', lft', 'rght' and 'parent_id'. I also recommend:
	* a 'child_count' field that is maintianed by counterCache (see notes later)
	* a 'path' field that contains a delimited textual version of the path from 'this' row back to the parent. This is super useful for ordering records and also for showing a user where 'this' row sits in the hierarchy (see later notes).

Attach the behaviour:

	public $actsAs = array(
		...,
		'OrgChart.OrgChart'
	);

2) Extract the data. The behaviour has a single function, hierarchy:

If you are calling the function from a controller:
	$hierarchy = $this->Department->hierarchy($id);

If you are calling the function from a model:
	$hierarchy = $this->hierarchy($id);

The data is returned in an array structure. You might note that there are no keys that contain the model name; this is to make the helper's job easier as it is model agnostic.

The array contains nodes for:

* each parent from the top of the tree to the parent of 'this' node
* 'this' node and its siblings
* each child of 'this' node
* a dummy node for each 'end' node (normally children of 'this' node or its siblings) showing how many direct children it has. This is node is not attached if the end node has no children.

Pass the data through to the view, for example:
$this->set('hierarchy', $hierarchy);

3) Use the helper to display the organisation chart
* Include the helper in your controller:
	public $helpers = array(
		...,
		'OrgChart.OrgChart'
	);

* Draw the organisation chart:
	echo $this->OrgChart->draw(
		$hierarchy,
		'departments',
		'hierarchy'
	); ?>

The draw function accepts three parameters:
* hierarchy - the array containing the data
* controller_name
* action

The last two variables are used for creating the links for each node. When each node is drawn, it is enclosed in an a tag where the url is:
	controller_name/action/id
id is the id of 'this' node.

There is also some CSS (supplied as both LESS and CSS) that must incorporated with your own CSS in whatever manner best suits your project.

child_count column
------------------

I know you can use the left and right fields to calculate ho many direct and indirect children a row in a tree based table has, but I prefer to calculate once and store the value, rather than calculate it each time I need it. I use the counterCache function for this.

I have a belongsTo 'ParentModel' association and use counterCache in its definition:

	public $belongsTo = array(
		...,
		'ParentDepartment' => array(
			'className' => 'Department',
			'foreignKey' => 'parent_id',
			'counterCache' => 'child_count',
			'dependent' => false
		)
	);

You can then also have a child model association:

	public $hasMany = array(
		...,
		'ChildDepartment' => array(
			'className' => 'Department',
			'foreignKey' => 'parent_id'
		)
	);

Now, the child_count field will always contain the number of direct children of 'this' row.

path column
-----------

Ever tried to order data from a tree based table? Unless the data is stored in an ordered manner this is really hard. The challenge is that the order instruction applies to a field (or fields) that apply across the whole data set. So if you order by 'title' all the titles beginning with 'a' will be together, followed by the bs and so on. This brings them out of thier hierarical position as the parent/child relationship is ignored.

You can rely on the lft column assuming that the data is neatly stored and maintained (and you can use the Tree->reorder function for that) but in my experience that can add a noticeble performance overhead, especially on larger tables. This is because each update cascades further updates that can get into a deep loop.

To maintain this I add my TreeOrder plugin that contains the TreeOrder behaviour, which is also on my Git page (https://github.com/classoutfit/TreeOrder).