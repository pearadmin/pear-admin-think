
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
		<button class="pear-btn pear-btn-primary pear-btn-md" lay-event="renew">
			<i class="layui-icon layui-icon-refresh"></i>
			恢复数据
		</button>
		<button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove">
			<i class="layui-icon layui-icon-delete"></i>
			彻底删除
		</button>
		</script>
		<script src="__ADMIN__/layui/layui.js"></script>
        <script src="__ADMIN__/pear/pear.js"></script>
        <script src="__ADMIN__/multi/{{$multi}}/{{$multi_name}}/recycle.js"></script>
	</body>
</html>
