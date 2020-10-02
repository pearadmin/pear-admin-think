
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="__ADMIN__/pear/css/pear.css" />
	</head>
	<body class="pear-container">
        {{searchs}}
		<div class="layui-card">
			<div class="layui-card-body">
				<table id="dataTable" lay-filter="dataTable"></table>
			</div>
		</div>

		<script type="text/html" id="toolbar">
			<button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add">
		        <i class="layui-icon layui-icon-add-1"></i>
		        新增
			</button>
			<button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove">
		        <i class="layui-icon layui-icon-delete"></i>
		        删除
		    </button>
            <button class="pear-btn pear-btn-md" lay-event="recycle">
		        <i class="layui-icon layui-icon-delete"></i>
		        回收站
		    </button>
		</script>

		<script type="text/html" id="options">
			<button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
		    <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
		</script>
		<script src="__ADMIN__/layui/layui.js"></script>
        <script src="__ADMIN__/pear/pear.js"></script>
        <script src="__ADMIN__/multi/{{$multi}}/{{$multi_name}}/index.js"></script>
	</body>
</html>
