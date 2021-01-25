
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
        <link rel="stylesheet" href="__STATIC__/component/pear/css/pear.css" />
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
        <script src="__STATIC__/component/layui/layui.all.js"></script>
        <script src="__STATIC__/component/pear/pear.js"></script>
        <script src="__STATIC__/admin/js/{{$name}}/recycle.js"></script>
	</body>
</html>
