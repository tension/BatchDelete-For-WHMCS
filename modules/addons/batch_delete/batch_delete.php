<?php
use WHMCS\Database\Capsule;
function batch_delete_config() {
	$configarray = array(
		'name' 			=> 'Batch Delete Users/Products',
		'description' 	=> 'This module allows you to batch delete users or products on the WHMCS.',
		'version' 		=> '1.0',
		'author' 		=> '<a href="http://neworld.org" target="_blank">NeWorld</a>',
		'fields' 		=> []
	);
	
	return $configarray;
}

function batch_delete_activate() {
	return [
		'status' => 'success',
		'description' => '模块激活成功。'
	];
}

function batch_delete_deactivate() {
	return [
		'status' => 'success',
		'description' => '模块卸载成功'
	];
}

function batch_delete_output($vars) {
    $systemurl = \WHMCS\Config\Setting::getValue('SystemURL');
    $modulelink = $vars['modulelink'];
    $modulename = explode("=", $modulelink)[1];
    $result = "<link rel=\"stylesheet\" href=\"{$systemurl}/modules/addons/{$modulename}/assets/css/style.css\">
    <link rel=\"stylesheet\" href=\"{$systemurl}/modules/addons/{$modulename}/assets/css/sweetalert.css\">
    <script type=\"text/javascript\" src=\"{$systemurl}/modules/addons/{$modulename}/assets/js/sweetalert.min.js\"></script>
<script>
$(document).ready(function(){
	$('#checkall').click(function (event) {
	    $(event.target).parents('.datatable').find('input').prop('checked',this.checked);
	});
});
</script>";
    
            if (isset($_REQUEST['action'])) {
                switch ($_REQUEST['action']) {
                    case 'users':
                    	$editor .= '<h2>批量删除用户</h2>
							<form action="" method="post">
							<input type="hidden" name="action" value="submit" />
							<input type="hidden" name="acc" value="users" />
							<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
							<tbody>
							<tr><td class="fieldlabel">状态</td><td class="fieldarea"><select name="status" class="form-control select-inline"><option value="Inactive">已停用</option><option value="Closed">已关闭</option><option value="Active">已激活</option></select></td></tr>
							</tbody></table>
							<div class="btn-container"><input type="submit" value="搜索" class="button btn btn-default"></div>
							</form>';
                    	break;
                    case 'products':
						$editor .= '<h2>批量删除产品</h2><form action="" method="post">
							<input type="hidden" name="action" value="submit" />
							<input type="hidden" name="acc" value="products" />
							<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
							<tbody>
							<tr><td class="fieldlabel">状态</td><td class="fieldarea"><select name="status" class="form-control select-inline"><option value="Completed">Completed</option><option value="Suspended">已暂停</option><option value="Terminated">被删除</option><option value="Cancelled">被取消</option><option value="Fraud">有欺诈</option></select></td></tr>
							</tbody></table>
							<div class="btn-container"><input type="submit" id="search-clients" value="搜索" class="button btn btn-default"></div>
							</form>';
                    	break;
                    case 'submit':
                        if ( isset( $_REQUEST ) ) {
	                        $acc = $_REQUEST['acc'];
	                        $header = <<<EOF
<script>
$(document).ready(function(){
	$('#checkall').click(function (event) {
	    $(event.target).parents('.datatable').find('input').prop('checked',this.checked);
	});
	$("#submit").click(function () {
		swal({
			title: '删除确认',
			text: '删除操作所关联账单、产品都将进行删除操作，请确认无误再执行。',
			type: 'warning',
			showCancelButton: true,
			closeOnConfirm: false,
			showLoaderOnConfirm: true,
			cancelButtonText: '取消操作',
			confirmButtonColor: '#DD6B55',
			confirmButtonText: '确认删除',
		},
		function(){
			$.post("{$modulelink}", $('#delclients').serialize(), function (data) {
				console.log(data);
				swal({
					title: "操作结果",
					text: ""+data.info+"",
					type: ""+data.status+"",
				},
				function() {
					location.reload();
				});
			},"json");
		});
	});
});
</script>
EOF;
                        	if ( $acc == 'users' ) {
                            	$count = Capsule::table('tblclients')->where('status', $_REQUEST['status'])->count();
                                $getClients = Capsule::table('tblclients')->where('status', $_REQUEST['status'])->get();
                                
                                //print_r($getClients);die();
				                foreach ( $getClients as $key => $clients ) {
									$value[$key]['id'] 			= $clients->id;
									$value[$key]['firstname'] 	= $clients->firstname;
									$value[$key]['lastname'] 	= $clients->lastname;
									$value[$key]['email'] 		= $clients->email;
									$value[$key]['datecreated'] = $clients->datecreated;
									$value[$key]['status'] 		= $clients->status;
							        $clientslist .= '<tr><td><input type="checkbox" name="selectedclients[]" value="'.$value[$key]['id'].'" class="checkall"></td><td><a href="clientssummary.php?userid='.$value[$key]['id'].'">'.$value[$key]['id'].'</a></td><td><a href="clientssummary.php?userid='.$value[$key]['id'].'">'.$value[$key]['firstname'].'</a></td><td><a href="clientssummary.php?userid='.$value[$key]['id'].'">'.$value[$key]['lastname'].'</a></td><td></td><td><a href="mailto:'.$value[$key]['email'].'">'.$value[$key]['email'].'</a></td><td>'.$value[$key]['datecreated'].'</td><td><span class="label inactive">'.$value[$key]['status'].'</span></td></tr>';
						        }
						        $header .= <<<EOF
<form id="delclients" action="" method="post"><input type="hidden" name="action" value="del"/><input type="hidden" name="acc" value="{$acc}"/><div class="">{$count} 条记录</div><div class="tablebg"><table id="sortabletbl" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3"><tbody><tr><th width="20"><input type="checkbox" id="checkall"></th><th>编号 <img src="images/asc.gif" class="absmiddle"></th><th>名字</th><th>姓氏</th><th>公司名</th><th>邮件地址</th><th>创建时间</th><th>状态</th></tr>
EOF;
						        
						        $footer = '</table><input id="submit" value="删除选中用户" class="btn btn-danger" /></div></form>';
						        
						        $editor = $header . $clientslist . $footer;
	                                
                            } else {
                            	$count = Capsule::table('tblhosting')->where('domainstatus', $_REQUEST['status'])->count();
                                $getHosting = Capsule::table('tblhosting')->where('domainstatus', $_REQUEST['status'])->get();
                                
				                foreach ( $getHosting as $key => $product ) {
					                $getClients = Capsule::table('tblclients')->where('id', $product->userid)->first();
					                $getProducts = Capsule::table('tblproducts')->where('id', $product->packageid)->first();
									$value[$key]['id'] 				= $product->id;
									$value[$key]['userid'] 			= $product->userid;
									$value[$key]['name'] 			= $getProducts->name;
									$value[$key]['firstname'] 		= $getClients->firstname;
									$value[$key]['lastname'] 		= $getClients->lastname;
									$value[$key]['email'] 			= $product->email;
									$value[$key]['datecreated'] 	= $product->datecreated;
									$value[$key]['domainstatus']	= $product->domainstatus;
									$value[$key]['status']			= strtolower($product->domainstatus);
									$value[$key]['nextduedate'] 	= $product->nextduedate;
									$value[$key]['domain'] 			= $product->domain;
									$billingcycle					= $product->billingcycle;
									$value[$key]['billingcycle'] 	= str_replace(array('-', 'account', ' '), '', strtolower($billingcycle));
									$value[$key]['amount'] 			= formatCurrency($product->amount);
							        $clientslist .= <<<EOF
<tr>
	<td><input type="checkbox" name="selectedclients[]" value="{$value[$key]['id']}" class="checkall"></td>
	<td><a href="clientshosting.php?userid={$value[$key]['userid']}&amp;id={$value[$key]['id']}">{$value[$key]['id']}</a></td>
	<td>{$value[$key]['name']} <span class="label {$value[$key]['status']}">{$value[$key]['domainstatus']}</span></td>
	<td><a href="clientshosting.php?userid={$value[$key]['userid']}&amp;id=&amp;id={$value[$key]['id']}">{$value[$key]['domain']}</a></td>
	<td><a href="clientssummary.php?userid={$value[$key]['userid']}">{$value[$key]['firstname']} {$value[$key]['lastname']}</a></td>
	<td>{$value[$key]['amount']}</td>
	<td>{$value[$key]['billingcycle']}</td>
	<td>{$value[$key]['nextduedate']}</td>
</tr>
EOF;
						        }
						        $header .= <<<EOF
<form id="delclients" action="" method="post"><input type="hidden" name="action" value="del"/><input type="hidden" name="acc" value="{$acc}"/><div class="">{$count} 条记录</div><div class="tablebg"><table id="sortabletbl" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3"><tbody><tr><th width="20"><input type="checkbox" id="checkall"></th><th>编号</th><th>产品/服务 <img src="images/desc.gif" class="absmiddle"></th><th>域名</th><th>客户名</th><th>价格</th><th>付款周期</th><th>到期时间</th></tr>
EOF;
						        
						        $footer = '</table><input id="submit" value="删除选中产品" class="btn btn-danger" /></div></form>';
						        
						        $editor = $header . $clientslist . $footer;
						        
                            }
                            
                        } else {
                            $alert = error('未定义操作');
                        }
                        break;
                    case 'del':
                    	if ( isset( $_POST ) ) {
	                    	$clientsID = $_POST['selectedclients'];
	                    	
	                    	if ( $_POST['acc'] == 'users' ) {
	                    		$action = Capsule::table('tblclients')->whereIn('id', $clientsID)->delete();
	                    		$hostaction = Capsule::table('tblhosting')->whereIn('userid', $clientsID)->delete();
	                    		$invoiceaction = Capsule::table('tblinvoices')->whereIn('userid', $clientsID)->delete();
	                    	} else {
	                    		$action = Capsule::table('tblhosting')->whereIn('id', $clientsID)->delete();
							}
							
	                    	if ( !empty( $action ) ) {
		                    	if ( !empty ( $hostaction ) ) {
			                    	if ( !empty ( $invoiceaction ) ) {
										// 返回信息
									    $result = [
									        'status' => 'success',
									        'info' => '成功删除 '.$action.' 条记录'. $hostaction . ' 个产品'. $invoiceaction . ' 个产品',
									    ];
			                    	} else {
										// 返回信息
									    $result = [
									        'status' => 'success',
									        'info' => '成功删除 '.$action.' 条记录'. $hostaction . ' 个产品',
									    ];
			                    	} 
			                    } else {
									// 返回信息
								    $result = [
								        'status' => 'success',
								        'info' => '成功删除 '.$action.' 条记录',
								    ];
			                    }
	                    	} else {
								// 返回错误信息
							    $result = [
							        'status' => 'error',
							        'info' => '操作失败',
							    ];
	                    	}
	                    	//打印数据
	                    	die( json_encode( $result ) );
	                    }
                    	break;
                    default:
                        break;
                }
            }
	        if ( $editor ) {
            	$result .= '<div class="row"><div class="col-xs-12" style="margin-bottom: 20px;"><a href="'.$modulelink.'" class="btn btn-default">返回模块</a></div></div>';
	        	$result .= $editor;
	        } else {
		        $result .='<div class="row">';
		        $result .='<div class="col-sm-6">
			        <a href="'.$modulelink.'&action=users" class="panel panel-default panel-pages panel-add">
			        	<div class="panel-body text-center">
			        		<h4><i class="fa fa-user" aria-hidden="true"></i> 用户类</h4>
						</div>
					</a>
				</div>';
		        $result .='<div class="col-sm-6">
			        <a href="'.$modulelink.'&action=products" class="panel panel-default panel-pages panel-add">
			        	<div class="panel-body text-center">
			        		<h4><i class="fa fa-file-text-o" aria-hidden="true"></i> 产品类</h4>
						</div>
					</a>
				</div>';
		        $result .='</div>';
			}
    echo $alert . $result;
}

if ( !function_exists('success') ) {
	function success($str) {
	    return '<script>jQuery.growl.notice({ title: "成功", message: "'.$str.'" });</script>';
	}
}

if ( !function_exists('error') ) {
	function error($str) {
	    return '<script>jQuery.growl.error({ title: "失败", message: "'.$str.'" });</script>';
	}
}