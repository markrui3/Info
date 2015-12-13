/*
selector_id:selector id
hasClick:true--点击有动作|false--点击无动作
*/
function loadRepositoryTree(selector_id, hasClick, hasTopClass) {
	var setting = {
		async: {
			enable: true,
			url: rootUrl + '/Admin/Repository/getRepository/',
			dataFilter: function(treeId, parentNode, responseData) {
				if (responseData) {
					for (var i = 0; i < responseData.length; i++) {
						if (typeof(responseData[i].a_repository_id) == 'undefined') {
							//根节点
							responseData[i].id = responseData[i].b_repository_id;
							responseData[i].pid = 0;
							responseData[i].open = false;
							if (hasClick) {
								responseData[i].click = 'searchBRepository(\'' + responseData[i].b_repository_id + '\')';
							}
						} else {
							//子节点
							responseData[i].id = responseData[i].b_repository_id + responseData[i].a_repository_id;
							responseData[i].pid = responseData[i].b_repository_id;
							if (hasClick) {
								responseData[i].click = 'searchARepository(\'' + responseData[i].a_repository_id + '\')';
							}
						}
					}

					//如果有顶层分类
					if (hasTopClass) {
						var productclass = {
							"id": "a",
							"pid": "0",
							"name": "全部仓库",
							"click":"searchBRepository('')"
						};
						responseData.splice(0, 0, productclass);
					}

				}
				return responseData;
			}
		},
		data: {
			simpleData: {
				enable: true,
				idKey: "id",
				pIdKey: "pid",
				rootPId: 0
			}
		},
		view: {
			selectedMulti: false
		}
	};
	$.fn.zTree.init($("#" + selector_id), setting);
}

/*
selector_id:selector id
hasClick:true--点击有动作|false--点击无动作
hasTopClass:true--有顶级分类|false--无顶级分类
*/
function loadProductClassTree(selector_id, hasClick, hasTopClass) {
	var setting = {
		async: {
			enable: true,
			url: rootUrl + '/Admin/Product/getProductClassTree/',
			dataFilter: function(treeId, parentNode, responseData) {
				if (responseData) {
					responseData = responseData.data;
					
					for (var i = 0; i < responseData.length; i++) {
						responseData[i].open = false;
						if (hasClick) {
							if (responseData[i].type == '1') {
								responseData[i].click = 'searchClass1(\'' + responseData[i].product_class_id + '\')';
							} else if (responseData[i].type == '2') {
								responseData[i].click = 'searchClass2(\'' + responseData[i].product_class_id + '\')';
							} else if (responseData[i].type == '3') {
								responseData[i].click = 'searchClass3(\'' + responseData[i].product_class_id + '\')';
							}
						}
					}
					//如果有顶层分类
					if (hasTopClass) {
						var productclass = {
							"product_class_id": "a",
							"parent_id": "0",
							"name": "顶级分类",
							"type":"0",
							"click":"searchClass1('')"
						};
						responseData.splice(0, 0, productclass);
					}
				}
				return responseData;
			}
		},
		data: {
			simpleData: {
				enable: true,
				idKey: "product_class_id",
				pIdKey: "parent_id",
				rootPId: 0
			}
		},
		view: {
			selectedMulti: false
		}
	};
	$.fn.zTree.init($("#" + selector_id), setting);
}

/*
selector_id:selector id
hasClick:true--点击有动作|false--点击无动作
*/
function loadDistrictTree(selector_id, hasClick) {
	var setting = {
		async: {
			enable: true,
			url: rootUrl + '/Admin/District/getDistrictTree/',
			dataFilter: function(treeId, parentNode, responseData) {
				//新加数组，存储为中间节点a_repository_id=>中间节点id
				var parent = new Array();
				if (responseData) {
					for (var i = 0; i < responseData.length; i++) {
						responseData[i].open = false;
						if (typeof(responseData[i].a_repository_id) != 'undefined' && typeof(responseData[i].b_repository_id) != 'undefined') {
							//中间节点
							responseData[i].id = responseData[i].b_repository_id + responseData[i].a_repository_id;
							responseData[i].pid = responseData[i].b_repository_id;
							//
							parent[responseData[i].a_repository_id] = responseData[i].b_repository_id + responseData[i].a_repository_id;
							
						} else if(typeof(responseData[i].a_repository_id) == 'undefined'){
							//根节点
							responseData[i].id = responseData[i].b_repository_id;
							responseData[i].pid = 0;

						}else if(typeof(responseData[i].b_repository_id) == 'undefined'){
							//叶节点
							responseData[i].id = responseData[i].a_repository_id + responseData[i].district_id;
							responseData[i].pid = parent[responseData[i].a_repository_id];

							if (hasClick) {
								responseData[i].click = 'searchDistrict(\'' + responseData[i].district_id + '\')';
							}
						}
					}
				}
				return responseData;
			}
		},
		data: {
			simpleData: {
				enable: true,
				idKey: "id",
				pIdKey: "pid",
				rootPId: 0
			}
		},
		view: {
			selectedMulti: false
		}
	};
	$.fn.zTree.init($("#" + selector_id), setting);
}

/*
selector_id:selector id
*/
function loadDatetimepicker(selector_id){
	$('#'+selector_id).datetimepicker({
		language:  'zh-CN',
		format: 'yyyy-mm-dd',
		minView:'2',
		autoclose:true
	});
}

//判断是否为null，是返回''
function transNull(varible){
	if(varible == null){
		return "";
	}else{
		return varible;
	}
}

//日期加减
 function addDate(date, time){ 
  var retDate = new Date(new Date(date).getTime() + time * 60 * 1000);
  return retDate.Format("yyyy-MM-dd hh:mm:ss");  
} 

// 对Date的扩展，将 Date 转化为指定格式的String   
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，   
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)   
// 例子：   
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423   
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18   
Date.prototype.Format = function(fmt)   
{ //author: meizz   
  var o = {   
    "M+" : this.getMonth()+1,                 //月份   
    "d+" : this.getDate(),                    //日   
    "h+" : this.getHours(),                   //小时   
    "m+" : this.getMinutes(),                 //分   
    "s+" : this.getSeconds(),                 //秒   
    "q+" : Math.floor((this.getMonth()+3)/3), //季度   
    "S"  : this.getMilliseconds()             //毫秒   
  };   
  if(/(y+)/.test(fmt))   
    fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));   
  for(var k in o)   
    if(new RegExp("("+ k +")").test(fmt))   
  fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));   
  return fmt;   
} 
