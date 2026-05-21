<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'shift_name',width:150,halign:'center'">Shift</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Name</th>
            <th rowspan="2" data-options="field:'tolerance',width:100,halign:'center'">Time Tolerance</th>
            <th rowspan="2" data-options="field:'briefing',width:100,halign:'center'">Time Briefing</th>
            <th rowspan="2" data-options="field:'start',width:100,halign:'center'">Start</th>
            <th rowspan="2" data-options="field:'end',width:100,halign:'center'">End</th>
            <th rowspan="2" data-options="field:'working',width:120,halign:'center'">Working Status</th>
            <th rowspan="2" data-options="field:'days',width:150,halign:'center'">Working Day</th>
            <th rowspan="2" data-options="field:'description',width:200,halign:'center'">Remarks</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                <input style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" id="filter_name" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Category</span>
                <input style="width:60%;" name="shift_id" panelHeight="auto" required="" id="shift_id" class="easyui-combobox">
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" required="" id="name" class="easyui-textbox">
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Time Tolerance</span>
                <input style="width:30%;" name="tolerance" required="" data-options="buttonText:'Hour', buttonAlign:'right'" class="easyui-numberbox">
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Time Briefing</span>
                <input style="width:30%;" name="briefing" required="" mask="99:99:99" class="easyui-maskedbox">
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Time Start</span>
                <input style="width:30%;" name="start" required="" mask="99:99:99" class="easyui-maskedbox">
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Time Ending</span>
                <input style="width:30%;" name="end" required="" mask="99:99:99" class="easyui-maskedbox">
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Working Status</span>
                <select style="width:60%;" name="working" panelHeight="auto" required="" id="status_working" class="easyui-combobox">
                    <option value="WEEKDAY">WEEKDAY</option>
                    <option value="WEEKEND">WEEKEND</option>
                </select>
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Working Day</span>
                <select style="width:60%;" name="days" panelHeight="auto" required="" id="working_day" class="easyui-combobox">
                    <option value="4">4 Days a Week</option>
                    <option value="5">5 Days a Week</option>
                    <option value="6">6 Days a Week</option>
                </select>
            </div>
            <div class="fitem" style="margin-bottom: 2px;">
                <span style="width:30%; display:inline-block;">Remarks</span>
                <input style="width:60%; height: 50px;" name="description" multiline="true" id="remarks" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('attandance/shift_details/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('attandance/shift_details/create') ?>';
        $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('attandance/shift_details/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('attandance/shift_details/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_name = $("#filter_name").textbox('getValue');

        var url = "?filter_shift=" + filter_shift + "&filter_name=" + filter_name;

        $('#dg').datagrid({
            url: '<?= base_url('attandance/shift_details/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('attandance/shift_details/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_name = $("#filter_name").textbox('getValue');

        var url = "?filter_shift=" + filter_shift + "&filter_name=" + filter_name;

        window.location.assign('<?= base_url('attandance/shift_details/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('attandance/shift_details/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#shift_id').combobox({
            url: '<?php echo base_url('attandance/shifts/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Shift'
        });

        $('#filter_shift').combobox({
            url: '<?php echo base_url('attandance/shifts/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    });
</script>