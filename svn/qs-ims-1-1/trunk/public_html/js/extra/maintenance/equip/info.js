// Hien thi thiet bi
var active_maintplan_global     = 0; // Ke hoach bao tri dang duoc chon
var tab_of_component            = 1; // tab cua thong tin cau truc bo phan
var tab_global                  = 1; // tab cua thong tin thiet bi
var active_maintplan_sub_global = 1; // cac dong tab con cua ke hoach bao tri trong thiet bi
var eq_ioid_global              = 0; // ioid cua thiet bi
var eq_ifid_global              = 0; // ifid cua thiet bi
var component_ioid_global       = 0; // ioid cua bo phan
var component_ifid_global       = 0; // ifid cua bo phan
var tech_note_obj               = 'OThongSoKyThuatTB'; // obj id cua chi so thiet bi
var deptid                      = 1; // deptid hien tai
var doc_type_global             = 0;
var doc_dtid_global             = 0;
var active_maintplan_sub_for_component_global = 1;//cac dong tab con cua ke hoach bao tri trong bo phan
var active_maintplan_sub_for_location_global = 1;//cac dong tab con cua ke hoach bao tri trong bo phan

// const, bien nay duoc set theo Extra_MaintenanceController
var NODE_TYPE_NONE        = 'NONE';
var NODE_TYPE_LOCATION    = 'LOCATION';
var NODE_TYPE_EQUIP       = 'EQUIP';
var NODE_TYPE_COMPONENT   = 'COMPONENT';
var NODE_TYPE_EQUIP_GROUP = 'EQ_GROUP';
var NODE_TYPE_EQUIP_TYPE  = 'EQ_TYPE';
var NODE_TYPE_EQUIP_ONLY  = 'EQ_ONLY';
var NODE_TYPE_PROJECT     = 'PROJECT';

//var GROUP_EQ_NONE         = 'NONE';
//var GROUP_EQ_BY_GROUP     = 'EQ_GROUP';
//var GROUP_EQ_BY_TYPE      = 'EQ_TYPE';

//===========================================================================
//INIT: CHON DONG DAU TIEN 
//===========================================================================

jQuery(document).ready(function() {
	// set kieu xem mac dinh la less view
	einfo_setView();

	// SET LAI CHIEU CAO
	einfo_setHeight();

	einfo_load_jstree(true);
	
	$('#einfo-search-eq-textbox').keypress(function(e){
		if(e.keyCode == 13) {
			var val = $(this).val();
			if(val != ''){
				$('#einfo-search-eq-table').jstree('destroy');//.destroy();
				einfo_load_jstree(true);
			}
			else{
				einfo_refresh_tree();
			}
		 }
	});	
});

function deleteDocument(ifid,id)
{
    qssAjax.confirm('Bạn có thực sự muốn xóa tài liệu này?',function(){
        var url = sz_BaseUrl + '/user/form/dettach';
        var data = {ifid:ifid,id:id};
        qssAjax.call(url, data, function(jreturn) {
            einfo_refresh_tree();
        }, function(jreturn) {
            qssAjax.alert(jreturn.message);
        });
    });
}

function einfo_duplicateEquip(ifid)
{
	var url  = sz_BaseUrl + '/button/m705/copy/equip/index';
	var data = {ifid:ifid, redirect: 0};

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({width: 900, height: 450, close: function(){}});
	});
}

function einfo_loadPeriod() {
	var url  = sz_BaseUrl + '/static/m780/equipdetail/plan/period2';
	var data = $('#einfo_period').serialize();

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo_loadPeriod').html(jreturn);
	});
}

function einfo_savePeriod() {
	var url  = sz_BaseUrl + '/static/m780/equipdetail/plan/period3';
	var data = $('#einfo_period').serialize();

	qssAjax.call(url, data, function(jreturn) {
		if(jreturn.message != '')
		{
			qssAjax.alert(jreturn.message);
		}

		einfo_load_tab_for_equip(4);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}

function einfo_setView(value) {
	if(value == undefined)
	{
		if($.cookie('einfo_view'))
		{
			value = $.cookie('einfo_view');
		}
		else
		{
			value = 2;
		}
	}

	$.cookie('einfo_view', value , {path:'/'});

	if($.cookie('einfo_view') == 2) // full view
	{
		$('#einfo-change-view-button').text('List view');
		$('#einfo-change-view-button').attr('onclick', 'einfo_setView(1);');
		einfo_load_jstree(true);
		einfo_refresh_tree();
	}
	else // less view
	{
		$('#einfo-change-view-button').text('Tree view');
		$('#einfo-change-view-button').attr('onclick', 'einfo_setView(2);');
		einfo_load_jstree(true);
		einfo_refresh_tree();
	}
}

function einfo_setHeight()
{
	$('#einfo_inner_show_info').css({'height': ($('#view').height() - 85) + 'px', 'overflow-y': 'auto', 'overflow-x': 'hidden'});
	$('#einfo-search-eq-table').css({'height': ($('#view').height() - 65) + 'px','min-height': $('#view').height() * 0.775 + 'px','overflow-y': 'auto', 'overflow-x': 'auto'});
	$('#einfo-info-table').css({'height': ($('#view').height() - 80) + 'px','min-height': $('#view').height() * 0.775 + 'px','overflow-y': 'auto', 'overflow-x': 'hidden'});


	$('.einfo-tab-select-column').each(function() {
		$(this).css('height', $('#view').height() * 0.75 + 'px');
	});
}

//===========================================================================
// EQUIP TREE VIEW
//===========================================================================        


function einfo_refresh_tree()
{
	$('#einfo-search-eq-table').jstree('destroy');//.destroy();
	einfo_load_jstree(false);
}
/*$(document).on('dnd_stop.vakata', function(e, data) {
	console.log(data);
	var parent = data.data.nodes[0];
	var old = $(data.element.closest('.jstree-node')).attr("id");
	alert(parent);
	alert(old);
	var t = $(data.event.target);
	var targetnode = t.closest('.jstree-node');
	var nodeID = targetnode.attr("id");
});*/
function einfo_load_jstree(start)
{
	$('#einfo-search-eq-table')
			.jstree({
				"plugins": ["themes", "json_data", "ui", "state", "cookie","dnd"],
				'core': {
					'check_callback': function(operation, node, node_parent, node_position, more) {
						var ret = true;
						if (operation === "move_node") {
							//check
							var nodetype = node.original.attr.nodetype;
							if(node_parent.original === undefined){
								var newtype = NODE_TYPE_NONE;
							}
							else{
								var newtype = node_parent.original.attr.nodetype;
							}
							if( nodetype == NODE_TYPE_EQUIP_GROUP
									|| nodetype == NODE_TYPE_EQUIP_ONLY
									|| nodetype == NODE_TYPE_PROJECT)
							{
								ret = false;
							}
							else if((newtype == NODE_TYPE_NONE || newtype == NODE_TYPE_LOCATION) 
									&& nodetype == NODE_TYPE_LOCATION){//|| (nodetype == NODE_TYPE_EQUIP && newtype == NODE_TYPE_LOCATION )
								ret = true;
							}
							if(more && more.dnd) { //only reoder
								ret = more.pos !== "i" && node_parent.id == node.parent;
						    }
						}
						//alert(node_position);
						return ret;
					},
					'data': {
						"url": function(node) {

							// Neu ko phai node dau tien (thoi diem khoi tao)
							if (node.id !== '#')
							{
								var nodeType = NODE_TYPE_NONE;
								var nodeID = 0;
								var nodeIFID = 0;
								var nodeIOID = 0;
								var groupEq = NODE_TYPE_EQUIP_TYPE;

								if (node.original.attr.nodetype)
								{
									nodeType = node.original.attr.nodetype;
								}

								if (node.original.attr.nodeid)
								{
									nodeID = node.original.attr.nodeid;
								}

								if (node.original.attr.nodeifid)
								{
									nodeIFID = node.original.attr.nodeifid;
								}

								if (node.original.attr.nodeioid)
								{
									nodeIOID = node.original.attr.nodeioid;
								}

								var ex = '';
								ex += (node.original.attr.eqtypelevel) ? '&eqtypelevel=' + node.original.attr.eqtypelevel : '';
								ex += (node.original.attr.comlevel) ? '&comlevel=' + node.original.attr.comlevel : '';
								ex += (node.original.attr.locioid) ? '&locioid=' + node.original.attr.locioid : '';
								ex += (node.original.attr.loccode) ? '&loccode=' + node.original.attr.loccode : '';
								ex += (node.original.attr.eqtypeioid) ? '&eqtypeioid=' + node.original.attr.eqtypeioid : '';
								ex += (node.original.attr.eqlevel) ? '&eqlevel=' + node.original.attr.eqlevel : '';
								ex += '&viewtype=' + $.cookie('einfo_view');

								return sz_BaseUrl + "/static/m780/search?nodeType="
										+ nodeType + '&nodeID=' + nodeID + '&groupEq=' + groupEq
										+ '&nodeIFID=' + nodeIFID + '&nodeIOID=' + nodeIOID + ex;
							}
							else
							{
								if (start) {
									var search = $('#einfo-search-eq-textbox').val();
									if(search != ''){
										return sz_BaseUrl + "/static/m780/search?nodeType=" + NODE_TYPE_EQUIP_ONLY + '&search=' + search + "&viewtype=" + $.cookie('einfo_view');;
									}
									else{
										return sz_BaseUrl + "/static/m780/search?start=1&viewtype=" + $.cookie('einfo_view');
									}
								}
								else {
									return sz_BaseUrl + "/static/m780/search?viewtype=" + $.cookie('einfo_view');
								}
							}

//							return node.id === '#' ?
//									sz_BaseUrl + "/static/m780/search"
//									: "/static/m780/search";
						},
						"dataType": "json"// needed only if you do not supply JSON headers
					}
				}
			});


	$('#einfo-search-eq-table')
			.bind("select_node.jstree", function(e, data) {
				// Hien Thi Thong Tin Chi Tiet Cua Mot Node
				einfo_show_node_detail(data.node.original.attr);
			})
			.on("move_node.jstree", function (e, data) {
				//data.node, data.parent, data.old_parent is what you need
				//check change
				if(data.parent != data.old_parent || data.position != data.old_position){
					treeInst = $('#einfo-search-eq-table').jstree(true)
					var parentnode = treeInst.get_node( data.parent );
					var old_parentnode = treeInst.get_node( data.old_parent );
					var position = data.position; 
					var up = 0;
					if(position > 0 ){
						position--;
					}
					else{
						position++;
						up = 1;
					}
					
					var target = parentnode.children[position]; 
					var targetnode = treeInst.get_node(target);
					//call ajax to update database
					url = sz_BaseUrl + '/static/m780/reorder';
					var data = {
							up					:up,
							type				:data.node.original.attr.nodetype,
							parent_type			:(parentnode.original !== undefined)?parentnode.original.attr.nodetype:NODE_TYPE_LOCATION,
							ifid				:data.node.original.attr.nodeifid,
							ioid				:data.node.original.attr.nodeioid,
							parent_ifid			:(parentnode.original !== undefined)?parentnode.original.attr.nodeifid:0,
							parent_ioid			:(parentnode.original !== undefined)?parentnode.original.attr.nodeioid:0,
							old_parent_ifid		:(old_parentnode.original !== undefined)?old_parentnode.original.attr.nodeifid:0,
							old_parent_ioid		:(old_parentnode.original !== undefined)?old_parentnode.original.attr.nodeioid:0,
							after_ifid			:targetnode.original.attr.nodeifid,
							after_ioid			:targetnode.original.attr.nodeioid
						};
					qssAjax.call(url, data, function(jreturn) {
						
					});
	
				}
			});
}

/**
 * Thiet lap gia tri global cho thiet bi hien tai
 * @param {type} nodeAttr
 * @returns {undefined}
 */
function setEquip(nodeAttr)
{
	if (nodeAttr.nodetype == NODE_TYPE_EQUIP)
	{
		eq_ioid_global = nodeAttr.nodeioid;
		eq_ifid_global = nodeAttr.nodeifid;
	}
}

/**
 * Hien thi du lieu theo node duoc chon.
 * @param {type} $node
 * @returns {undefined}
 */
function einfo_show_node_detail(nodeAttr)
{
	var url  = '';
	var data = {};
	
	if(nodeAttr.nodetype == NODE_TYPE_LOCATION)
	{
		setLocation(nodeAttr);
		
		data = {
			nodeioid: nodeAttr.nodeioid
			, nodeifid: nodeAttr.nodeifid
			, loccode: (nodeAttr.loccode) ? nodeAttr.loccode : ''
		};
		url = sz_BaseUrl + '/static/m780/locationdetail/index';
	}
	else if(nodeAttr.nodetype == NODE_TYPE_EQUIP_GROUP)
	{
		data = {
			nodeioid: nodeAttr.nodeioid
			, nodeifid: nodeAttr.nodeifid
			, loccode: (nodeAttr.loccode) ? nodeAttr.loccode : ''
			, eqgroup: (nodeAttr.eqgroup) ?nodeAttr.eqgroup : ''
		};		
		url = sz_BaseUrl + '/static/m780/groupdetail/index';
	}
	else if(nodeAttr.nodetype == NODE_TYPE_EQUIP_TYPE)
	{
		data = {
			nodeioid: nodeAttr.nodeioid
			, nodeifid: nodeAttr.nodeifid
			, loccode: (nodeAttr.loccode) ? nodeAttr.loccode : ''
			, eqtype: (nodeAttr.eqtype) ? nodeAttr.eqtype : ''
		};		
		url = sz_BaseUrl + '/static/m780/typedetail/index';
	}
	else if(nodeAttr.nodetype == NODE_TYPE_PROJECT)
	{
		data = {
			nodeioid: nodeAttr.nodeioid
			, nodeifid: nodeAttr.nodeifid
			, loccode: (nodeAttr.loccode) ? nodeAttr.loccode : ''
			, eqtype: (nodeAttr.eqtype) ? nodeAttr.eqtype : ''
		};		
		url = sz_BaseUrl + '/static/m780/projectdetail/index';
	}	
	else if(nodeAttr.nodetype == NODE_TYPE_EQUIP)
	{
		// Thiet lap gia tri global cho thiet bi dang xem
		setEquip(nodeAttr);		
		
		data = {
			eqioid: eq_ioid_global
			, eqifid: eq_ifid_global
			, eqtab: tab_global ? tab_global : ''
		};		
		url = sz_BaseUrl + '/static/m780/equipdetail/index';
	}
	else if(nodeAttr.nodetype == NODE_TYPE_COMPONENT)
	{
		setComponent(nodeAttr);		
		
		// Chua co data co component
		data = {
			nodeioid: nodeAttr.nodeioid
			, nodeifid: nodeAttr.nodeifid
		};			
		url = sz_BaseUrl + '/static/m780/componentdetail/index';
	}
	else
	{
		return; // ko lam gi ca
	}
	
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo-show-info').html(jreturn);
		einfo_setHeight();
	});
}

function einfo_load_tab_for_equip(eq_tab_no)
{
	//lấy cookie
	var tab = $.cookie('eq_tab_no');
	// Ghi lai gia tri tab vao global
	tab_global = parseInt((eq_tab_no ? eq_tab_no : (tab?tab:1)));

	// Danh dau tab da chon
	$('.einfo_tab').each(function(){ $(this).parent().removeClass('active'); });
	$('#einfo_tab_' + tab_global).parent().addClass('active');
	var data = {
			tab: tab_global,
			eqID: eq_ioid_global,
			eqIFID: eq_ifid_global
		};
		
	// Lay thong tin cua cac tab
	switch(tab_global)
	{
		case 1:
			url = sz_BaseUrl + '/static/m780/equipdetail/general';
		break;
		
		case 2:
			url = sz_BaseUrl + '/static/m780/equipdetail/monitors';
		break;
		
		case 3:
			url = sz_BaseUrl + '/static/m780/equipdetail/document/index';
		break;
		
		case 4:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/index';
		break;		
		
		case 5:
			url = sz_BaseUrl + '/static/m780/equipdetail/history/index';
		break;

        case 6:
            url = sz_BaseUrl + '/static/m780/equipdetail/breakdown/index';
        break;

        case 7:
        	data['search'] = $('#sparepart_search').val();
            url = sz_BaseUrl + '/static/m780/equipdetail/sparepart/index';
        break;

		case 8:
			url = sz_BaseUrl + '/static/m780/equipdetail/class/index';
		break;
	}
	
	
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo_inner_show_info').html('');
		$('#einfo_inner_show_info').html(jreturn);
		einfo_setHeight();
	});	
	$.cookie('eq_tab_no',tab_global,{path:'/'});
	if(tab_global == 4){
		$.cookie('tab_of_component',2,{path:'/'});
	}
	
}


function einfo_showSparepartHistory(refThietBi, refBoPhan, refVatTu, filter)
{
    var url  = sz_BaseUrl + '/static/m780/equipdetail/sparepart/history';
    var data = {refThietBi: refThietBi, refBoPhan: refBoPhan,refVatTu: refVatTu, filter:filter};

    qssAjax.getHtml(url, data, function(jreturn) {
        $('#qss_trace').html(jreturn);
        $('#qss_trace').dialog({width: 900, height: 450, close: function(){}});
    });
}



//===========================================================================
//THIET BI: TAB3: TAI LIEU
//===========================================================================    
function einfo_show_document(doc_id, doc_type)
{
	var url  = sz_BaseUrl + '/static/m780/equipdetail/document/show';

	// set global
	doc_type_global = doc_type;
	doc_dtid_global = doc_id;

	// danh dau loai tai lieu
	$('.einfo-doctab-select-doctype').each(function() {
		$(this).parent().removeClass('marker');
	});
	$('.einfo-doctab-select-doctype[type='+doc_type_global+']'+'[id='+doc_dtid_global+']').parent().addClass('marker');

	// mo sparepart
	var data = {
		dtid: doc_dtid_global,
		type: doc_type_global,
		eqIFID: eq_ifid_global
	};	
	
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo-doc-attach').html('');
		$('#einfo-doc-attach').html(jreturn);
	});	
}

// Tai tai lieu ve may
function downloadDoc(id){
    var url = sz_BaseUrl + '/static/m016/download?popup=1&id='+id;
    window.open(url);
    enabledLayout();
}


function rowRecord() {
	//console.log($('#upload_folder').val());
	if (!eq_ifid_global || !deptid) {
		return;
	}
	var url = sz_BaseUrl + '/user/form/document';
	var data = {ifid: eq_ifid_global, deptid: deptid,popup:1};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({width: 900, height: 450, close: function(){einfo_load_tab_for_equip(3);}});
		$('#upload').file(function(inp) {
			inp.id = inp.name = 'myFileInput';

			/* Upload file by ajax */
			disabledLayout();
			$.ajaxFileUpload({
				url: sz_BaseUrl + '/static/m016/upload?folder=' + $('#upload_folder').val(),
				secureuri: false,
				fileElementId: inp,
				dataType: 'json',
				success: function(data, status) {
					/* Upload file successfully */
					if (data.error) {
						qssAjax.alert(data.message);
						enabledLayout();
					} else {
						//attachDocument(data.id,row.id);
						$('#document_id').val(data.id);
						$('#document').val(data.name);
						enabledLayout();

					}

				},
				error: function(data, status, e) {
					/* If upload error */
					qssAjax.alert(e);
				}
			});
		});
	});
}

//===========================================================================
//THIET BI: TAB4: KE HOAHC BAO TRI
//===========================================================================
// Chon dong bao tri dinh ky de hien thi thong tin phu
function  einfo_select_maintaint_plan(ele)
{
	var tab = 1;
	// danh dau ke hoach bao tri
	$('.einfo-select-maintaint-plan').each(function() {
		$(this).removeClass('marker einfo-active-maintaint-plan');
	});
	$(ele).addClass('marker einfo-active-maintaint-plan');
	active_maintplan_global = $(ele).attr('ifid') ? parseInt($(ele).attr('ifid')) : 0;

	// select tab 
	$('.einfo_maintain_plan_tab').each(function() {
		if ($(this).hasClass('active'))
		{
			tab = parseInt($(this).attr('tab'));
		}
	});
	tab = active_maintplan_sub_global ? active_maintplan_sub_global : tab;
	einfo_show_maintain_plan_tab(active_maintplan_sub_global);
}

// Hien thi tab phu cua thong tin bao tri dinh ky
function einfo_show_maintain_plan_tab(tab)
{
	
	var url, data;
	var plan_ifid  = $('.einfo-active-maintaint-plan').attr('ifid');

	// ghi lai tab dang hoat dong
	active_maintplan_sub_global = tab; //su dung cho ham khac

	// danh dau tab thong tin ve ke hoach bao tri
	$('#einfo_maintain_plan_tab a').each(function() {
		$(this).removeClass('active');
	});
	$('#einfo_maintain_plan_tab_' + tab).addClass('active');

	
	data = {
		tab: tab,
		plan: plan_ifid
	};

	switch (tab)
	{
		case 1:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/work';
		break;
		case 2:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/stopline';
		break;
		case 3:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/material';
		break;
		case 4:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/day';
		break;
		case 5:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/technote';
		break;
		case 6:
			url = sz_BaseUrl + '/static/m780/equipdetail/plan/install';
		break;
	}
	

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo-maintain-plan-child-info').html('');
		$('#einfo-maintain-plan-child-info').html(jreturn);

		einfo_setHeight();
	});		
	//load chu kỳ
	einfo_load_period(plan_ifid);	
}
function einfo_load_period(plan){
	
	url = sz_BaseUrl + '/static/m780/equipdetail/plan/period';
	qssAjax.getHtml(url, {plan:plan}, function(jreturn) {
		$('#einfo-select-maintaint-period').html('');
		$('#einfo-select-maintaint-period').html(jreturn);
		einfo_setHeight();
	});
}

function einfo_delete_plan(sifid)
{
	var deptids = [];
	var ifid    = [];
	deptids[0]  = deptid;
	ifid[0]     = sifid;
	deleteForm(ifid, deptids,function(){ einfo_load_tab_for_equip(tab_global);});
}

function einfo_insert_new_maintplan_subtab()
{
	var plan_ifid = $('.einfo-active-maintaint-plan').attr('ifid') ? parseInt($('.einfo-active-maintaint-plan').attr('ifid')) : 0;
	var tab = 1;
	var objid;

	if (plan_ifid == 0)
	{
		qssAjax.alert('Bạn phải chọn một dòng kế hoạch!');
		return;
	}

	// select tab 
	$('.einfo_maintain_plan_tab').each(function() {
		if ($(this).hasClass('active'))
		{
			tab   = parseInt($(this).attr('tab'));
			objid = $(this).attr('objid');
		}
	});
	popupObjectInsert(plan_ifid, deptid, objid, {}, function(){ einfo_show_maintain_plan_tab(active_maintplan_sub_global);});

}


function einfo_delete_sub_plan_tab(objid, ifid, ioid)
{
	var deptid = parseInt($('#einfo_deptid').val());
	var sioid = [], sdeptid = [];
	sioid[0] = ioid;
	deleteObject(ifid, deptid, objid, sioid, function(){ einfo_show_maintain_plan_tab(active_maintplan_sub_global);});
}

function einfo_edit_sub_plan_tab(objid, ifid, ioid)
{
	var deptid = parseInt($('#einfo_deptid').val());
	popupObjectEdit(ifid, deptid, objid, ioid, {}, function(){ einfo_show_maintain_plan_tab(active_maintplan_sub_global);});
}

//===========================================================================
//THIET BI: TAB5: LICH SU BAO TRI
//===========================================================================    

// Hien thi dong lich su bao tri co phan trang
function einfo_history_show()
{
	var display = $('#einfo-history-display').val();
	var page = $('#einfo-history-page').val();
	var url = sz_BaseUrl + '/static/m780/equipdetail/history/index';
	var html = '';

	var data = {
		page: page,
		display: display,
		eqID: eq_ioid_global
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo-tab-show-table').html(jreturn);
	});
}

// Chon dong lich su, chi danh dau 
function einfo_select_history_row(ele)
{
	$('.einfo-historytab-row').each(function() {
		$(this).removeClass('marker')
	});
	$(ele).addClass('marker');
}

// Xem chi tiet ban ghi
function einfo_rowDetail(ifid) 
{
	var url = sz_BaseUrl + '/user/form/detail';
	var data = {ifid: ifid, deptid: deptid};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({width: 900, height: 450});
	});
}

//KHU VUC

function setLocation(nodeAttr)
{
	if (nodeAttr.nodetype == NODE_TYPE_LOCATION)
	{
		location_code_global = nodeAttr.loccode;
		location_ifid_global = nodeAttr.nodeifid;
		location_ioid_global = nodeAttr.nodeioid;
	}	
}
//===========================================================================
// BO PHAN
//===========================================================================   
function setComponent(nodeAttr)
{
	if (nodeAttr.nodetype == NODE_TYPE_COMPONENT)
	{
		component_ifid_global = nodeAttr.nodeifid;
		component_ioid_global = nodeAttr.nodeioid;
	}	
}


function einfo_load_tab_for_component(tab)
{
	//lấy cookie
	var tabcomp = $.cookie('tab_of_component');
	// Ghi lai gia tri tab vao global
	tab_of_component = parseInt((tab ? tab : (tabcomp?tabcomp:1)));

	// Danh dau tab da chon
	$('.einfo_tab').each(function(){ $(this).parent().removeClass('active'); });
	$('#einfo_tab_' + tab_of_component).parent().addClass('active');

	// Lay thong tin cua cac tab
	switch(tab_of_component)
	{
		case 1:
			url = sz_BaseUrl + '/static/m780/componentdetail/general';
		break;
		
		case 2:
			url = sz_BaseUrl + '/static/m780/componentdetail/plan/index';
		break;
		
		case 3:
			url = sz_BaseUrl + '/static/m780/componentdetail/history/index';
		break;


        case 4:
            url = sz_BaseUrl + '/static/m780/componentdetail/technote/index';
        break;

		case 5:
			url = sz_BaseUrl + '/static/m780/componentdetail/class/index';
		break;
	}
	
	var data = {
		tab: tab_of_component,
		comIOID: component_ioid_global,
		comIFID: component_ifid_global
	};
	
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo_inner_show_info').html('');
		$('#einfo_inner_show_info').html(jreturn);
		einfo_setHeight();
	});	
	$.cookie('tab_of_component',tab_of_component,{path:'/'});
}

function einfo_load_tab_for_location(tab)
{
	//lấy cookie
	var tablocation = $.cookie('tab_of_location');
	// Ghi lai gia tri tab vao global
	tab_of_location = parseInt((tab ? tab : (tablocation?tablocation:1)));

	// Danh dau tab da chon
	$('.einfo_tab').each(function(){ $(this).parent().removeClass('active'); });
	$('#einfo_tab_' + tab_of_location).parent().addClass('active');

	// Lay thong tin cua cac tab
	switch(tab_of_location)
	{
		case 1:
			url = sz_BaseUrl + '/static/m780/locationdetail/general';
		break;
		
		case 2:
			url = sz_BaseUrl + '/static/m780/locationdetail/plan/index';
		break;
		
		case 3:
			url = sz_BaseUrl + '/static/m780/locationdetail/history/index';
		break;
	}
	
	var data = {
		tab: tab_of_component,
		locationIOID: location_ioid_global,
		locationIFID: location_ifid_global,
		locationCode: location_code_global
	};
	
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo_inner_show_info').html('');
		$('#einfo_inner_show_info').html(jreturn);
		einfo_setHeight();
	});		
}

function einfo_delete_component(objid, ifid, ioid)
{
	var deptid = parseInt($('#einfo_deptid').val());
	var sioid = [], sdeptid = [];
	sioid[0] = ioid;
	deleteObject(ifid, deptid, objid, sioid, function(){ einfo_load_tab_for_component(tab_of_component);});
}

function einfo_edit_component(objid, ifid, ioid)
{
	var deptid = parseInt($('#einfo_deptid').val());
	popupObjectEdit(ifid, deptid, objid, ioid, {}, function(){ einfo_load_tab_for_component(tab_of_component);});
}


function  einfo_select_maintaint_plan_for_component(ele)
{
	var tab = 1;
	// danh dau ke hoach bao tri
	$('.einfo-select-maintaint-plan').each(function() {
		$(this).removeClass('marker einfo-active-maintaint-plan');
	});
	$(ele).addClass('marker einfo-active-maintaint-plan');
	active_maintplan_global = $(ele).attr('ifid') ? parseInt($(ele).attr('ifid')) : 0;

	// select tab 
	$('.einfo_maintain_plan_tab').each(function() {
		if ($(this).hasClass('active'))
		{
			tab = parseInt($(this).attr('tab'));
		}
	});
	tab = active_maintplan_sub_for_component_global ? active_maintplan_sub_for_component_global : tab;

	einfo_show_maintain_plan_tab_for_component(active_maintplan_sub_for_component_global);
}

function  einfo_select_maintaint_plan_for_location(ele)
{
	var tab = 1;
	// danh dau ke hoach bao tri
	$('.einfo-select-maintaint-plan').each(function() {
		$(this).removeClass('marker einfo-active-maintaint-plan');
	});
	$(ele).addClass('marker einfo-active-maintaint-plan');
	active_maintplan_global = $(ele).attr('ifid') ? parseInt($(ele).attr('ifid')) : 0;

	// select tab 
	$('.einfo_maintain_plan_tab').each(function() {
		if ($(this).hasClass('active'))
		{
			tab = parseInt($(this).attr('tab'));
		}
	});
	tab = active_maintplan_sub_for_location_global ? active_maintplan_sub_for_location_global : tab;

	einfo_show_maintain_plan_tab_for_location(active_maintplan_sub_for_location_global);
}

function einfo_show_maintain_plan_tab_for_component(tab)
{
	var url, data;
	var plan_ifid  = $('.einfo-active-maintaint-plan').attr('ifid');

	// ghi lai tab dang hoat dong
	active_maintplan_sub_for_component_global = tab; //su dung cho ham khac

	// danh dau tab thong tin ve ke hoach bao tri
	$('#einfo_maintain_plan_tab a').each(function() {
		$(this).removeClass('active');
	});
	$('#einfo_maintain_plan_tab_' + tab).addClass('active');

	
	data = {
		tab: tab,
		plan: plan_ifid,
		comIOID: component_ioid_global,
		comIFID: component_ifid_global
	};

	switch (tab)
	{
		case 1:
			url = sz_BaseUrl + '/static/m780/componentdetail/plan/work';
		break;
		case 2:
			url = sz_BaseUrl + '/static/m780/componentdetail/plan/material';
		break;
	}

	qssAjax.getHtml(url, data, function(jreturn) {
		
		$('#einfo-maintain-plan-child-info').html('');
		$('#einfo-maintain-plan-child-info').html(jreturn);

		$('td').each(function() {
			$(this).attr('valign', 'top')
		});			
	});			
	//load chuky
	einfo_load_period(plan_ifid);	
}

function einfo_show_maintain_plan_tab_for_location(tab)
{
	var url, data;
	var plan_ifid  = $('.einfo-active-maintaint-plan').attr('ifid');

	// ghi lai tab dang hoat dong
	active_maintplan_sub_for_location_global = tab; //su dung cho ham khac

	// danh dau tab thong tin ve ke hoach bao tri
	$('#einfo_maintain_plan_tab a').each(function() {
		$(this).removeClass('active');
	});
	$('#einfo_maintain_plan_tab_' + tab).addClass('active');

	
	data = {
		tab: tab,
		plan: plan_ifid,
		comIOID: component_ioid_global,
		comIFID: component_ifid_global
	};

	switch (tab)
	{
		case 1:
			url = sz_BaseUrl + '/static/m780/locationdetail/plan/work';
		break;
		case 2:
			url = sz_BaseUrl + '/static/m780/locationdetail/plan/material';
		break;
	}

	qssAjax.getHtml(url, data, function(jreturn) {
		
		$('#einfo-maintain-plan-child-info').html('');
		$('#einfo-maintain-plan-child-info').html(jreturn);

	});			
	//load chuky
	einfo_load_period(plan_ifid);	
}


// Hien thi dong lich su bao tri co phan trang
function einfo_history_show_for_component()
{
	var display = $('#einfo-history-display').val();
	var page = $('#einfo-history-page').val();
	var url = sz_BaseUrl + '/static/m780/componentdetail/history/index';
	var html = '';

	var data = {
		page: page,
		display: display,
		comIOID: component_ioid_global,
		comIFID: component_ifid_global
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#einfo-tab-show-table').html(jreturn);
	});
}

function einfo_failure_class_show_failure(ele)
{
	var data = {
		tab: tab_global,
		eqID: eq_ioid_global,
		eqIFID: eq_ifid_global,
		m780_equip_class_index_start: $('#m780_equip_class_index_start').val(),
		m780_equip_class_index_end: $('#m780_equip_class_index_end').val()
	};

	var url  = sz_BaseUrl + '/static/m780/equipdetail/class/types';

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_class_types').html(jreturn);
	});
}

function einfo_break_show(ele)
{
	var data = {
		tab: tab_global,
		eqID: eq_ioid_global,
		eqIFID: eq_ifid_global,
		m780_equip_break_index_start: $('#m780_equip_break_index_start').val(),
		m780_equip_break_index_end: $('#m780_equip_break_index_end').val()
	};

	var url  = sz_BaseUrl + '/static/m780/equipdetail/breakdown/types';

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_break_types').html(jreturn);
	});
}

function einfo_failure_class_select_failure(ele)
{
	// Danh dau dong loai
	$('.failure_line').removeClass('marker');
	$(ele).addClass('marker');

	// Lay ioid cua loai cho viec loc
	var type_ioid = $(ele).attr('typeioid');

	// Hien thi danh sach nguyen nhan co the
	var url  = sz_BaseUrl + '/static/m780/equipdetail/class/reasons';
	var data = {
		eq: eq_ioid_global,
		type: type_ioid,
		m780_equip_class_index_start: $('#m780_equip_class_index_start').val(),
		m780_equip_class_index_end: $('#m780_equip_class_index_end').val()
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_class_reasons').html(jreturn);
	});
}

function einfo_failure_class_select_reason(ele)
{
	// Danh dau dong loai
	$('.reason_line').removeClass('marker');
	$(ele).addClass('marker');

	// Lay ioid cua loai cho viec loc
	var reason_ioid = $(ele).attr('reasonioid');

	// Hien thi danh sach nguyen nhan co the
	var url  = sz_BaseUrl + '/static/m780/equipdetail/class/remedies';
	var data = {
		eq: eq_ioid_global,
		reason: reason_ioid,
		m780_equip_class_index_start: $('#m780_equip_class_index_start').val(),
		m780_equip_class_index_end: $('#m780_equip_class_index_end').val()
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_class_remedies').html(jreturn);
	});
}


// -------------------------------------

function einfo_failure_class_show_failure_in_component(ele)
{
	var data = {
		tab: tab_global,
		eqID: eq_ioid_global,
		comID: component_ioid_global,
		eqIFID: eq_ifid_global,
		m780_equip_class_index_start: $('#m780_equip_class_index_start').val(),
		m780_equip_class_index_end: $('#m780_equip_class_index_end').val()
	};

	var url  = sz_BaseUrl + '/static/m780/componentdetail/class/types';

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_class_types').html(jreturn);
	});
}

function einfo_failure_class_select_failure_in_component(ele)
{
	// Danh dau dong loai
	$('.failure_line').removeClass('marker');
	$(ele).addClass('marker');

	// Lay ioid cua loai cho viec loc
	var type_ioid = $(ele).attr('typeioid');

	// Hien thi danh sach nguyen nhan co the
	var url  = sz_BaseUrl + '/static/m780/componentdetail/class/reasons';
	var data = {
		eq: eq_ioid_global,
		com: component_ioid_global,
		type: type_ioid,
		m780_equip_class_index_start: $('#m780_equip_class_index_start').val(),
		m780_equip_class_index_end: $('#m780_equip_class_index_end').val()
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_class_reasons').html(jreturn);
	});
}

function einfo_failure_class_select_reason_in_component(ele)
{
	// Danh dau dong loai
	$('.reason_line').removeClass('marker');
	$(ele).addClass('marker');

	// Lay ioid cua loai cho viec loc
	var reason_ioid = $(ele).attr('reasonioid');

	// Hien thi danh sach nguyen nhan co the
	var url  = sz_BaseUrl + '/static/m780/componentdetail/class/remedies';
	var data = {
		eq: eq_ioid_global,
		com: component_ioid_global,
		reason: reason_ioid,
		m780_equip_class_index_start: $('#m780_equip_class_index_start').val(),
		m780_equip_class_index_end: $('#m780_equip_class_index_end').val()
	};
	qssAjax.getHtml(url, data, function(jreturn) {
		$('#m780_equip_class_remedies').html(jreturn);
	});
}

function rowDocument(){
	$('#qss_trace').dialog('close');	
}
function sparepart_search_enter(event){
	if(event.keyCode == 13){
		einfo_load_tab_for_equip(7);
	}
}

function einfo_install_history(equipIOID)
{
	var url  = sz_BaseUrl + '/static/m780/equipdetail/install/index';
	var data = {equip: equipIOID};

	qssAjax.getHtml(url, data, function(jreturn) {
		$('#qss_trace').html('');
		$('#qss_trace').html(jreturn);
		$('#qss_trace').dialog({width: 900, height: 450});
	});
}

function einfo_install_history_insert()
{
	var url   = sz_BaseUrl + '/static/m780/equipdetail/install/insert';
	var data  = $('#m780_install_wrap #edit_form').serialize();
	var equip = $('#m780_install_wrap #equip').val();

	qssAjax.call(url, data, function(jreturn) {
		if(jreturn.message != '')
		{
			qssAjax.alert(jreturn.message);
		}
		einfo_install_history(equip);
	}, function(jreturn) {
		qssAjax.alert(jreturn.message);
	});
}

function einfo_install_history_edit(ele, text)
{
	$('#ifid').val($(ele).parent().parent().find('.ifid').val());
	$('#ioid').val($(ele).parent().parent().find('.ioid').val());
	$('#date').val($(ele).parent().parent().find('.date').val());
	$('#time').val($(ele).parent().parent().find('.time').val());
	$('#location').val($(ele).parent().parent().find('.location').val());
	$('#line').val($(ele).parent().parent().find('.line').val());
	$('#costcenter').val($(ele).parent().parent().find('.costcenter').val());
	$('#manager').val($(ele).parent().parent().find('.manager').val());
	$('#manager_tag').val($(ele).parent().parent().find('.manager_tag').val());
	$('#update_button').text(text);

}

function einfo_install_history_delete(ifid)
{
	var url   = sz_BaseUrl + '/static/m780/equipdetail/install/remove';
	var data  = {ifid:ifid, equip:$('#m780_install_wrap #equip').val(), equipifid:$('#m780_install_wrap #equipifid').val()};
	var equip = $('#m780_install_wrap #equip').val();

	qssAjax.confirm('Bạn muốn xóa bản ghi này không?', function(){
		qssAjax.call(url, data, function(jreturn) {
			if(jreturn.message != '')
			{
				qssAjax.alert(jreturn.message);
			}
			einfo_install_history(equip);
		}, function(jreturn) {
			qssAjax.alert(jreturn.message);
		});
	});
}