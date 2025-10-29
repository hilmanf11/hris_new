<table id="dg" class="easyui-datagrid" style="width:100%;"></table>

<div id="toolbar"><?= $button ?></div>

<div id="dlg_add" class="easyui-dialog">
    <table>
        <tr>
            <td>Position</td>
            <td><input id="position" class="easyui-combobox" required></td>
        </tr>
        <tr>
            <td>Contract Type</td>
            <td><input id="contract" class="easyui-combobox" required></td>
        </tr>
        <tr>
            <td>Attachment</td>
            <td><form id="formAttachment" enctype="multipart/form-data"><input id="attachment" name="attachment" class="easyui-filebox" required></form></td>
        </tr>
    </table>
</div>

<script>
    var get_datas = {};
    var select_id = '';
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('employee/master_contract/datatables') ?>',
            toolbar: '#toolbar',
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'position', title: 'Position', width: '170', halign: 'center', rowspan: 2},
                {field: 'contract', title: 'Contract', halign: 'center', rowspan: 2},
                {field: 'attachment', title: 'Attachment', width: '100', halign: 'center', rowspan: 2, align: 'center', formatter: function (value, row, index) {
                    return '<a href="javascript:;" class="easyui-linkbutton" onclick="download_attachment('+"'"+window.btoa(value)+"'"+')"><i class="fa fa-download"></i> Download</a>';
                }},
                {title: 'Created', halign: 'center', colspan: 2},
                {title: 'Updated', halign: 'center', colspan: 2}
            ],[
                {field: 'c_by', title: 'By', width: '170', halign: 'center'},
                {field: 'c_date', title: 'Date', width: '100', halign: 'center', align: 'center'},
                {field: 'u_by', title: 'By', width: '170', halign: 'center'},
                {field: 'u_date', title: 'Date', width: '100', halign: 'center', align: 'center'}
            ]],
            onLoadSuccess: function (data) {
                data.rows.forEach(function (value, key) {
                    if (get_datas[value.position_id] == undefined) { get_datas[value.position_id] = []; }

                    value.contract_id.split(',').forEach(function (value2, key2) {
                        get_datas[value.position_id].push(value2);
                    });
                });

                $('.easyui-linkbutton').linkbutton();
                getprocess('forms');
            }
        }).datagrid('enableFilter');

        $('#position').combobox({
            textField: 'name',
            valueField: 'id',
            editable: false
        });
        $('#contract').combobox({textField: 'name', valueField: 'id', editable: false, multiple: true});

        $('#dlg_add').dialog({
            closed: true,
            modal: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var file = $('#attachment').next().find('input[type=file]')[0].files[0];
                    var contracts = $('#contract').combobox('getValues');
                    var position = $('#position').combobox('getValue');
                    var save = true;

                    if ([
                        position,
                        $('#contract').combobox('getValue'),
                        $('#attachment').filebox('getValue')
                    ].includes('')) { save = false; toastr.warning('Please fill in all fields!', 'Warning'); }

                    else if (!['xls', 'xlsx'].includes(file.name.split('.').pop().toLowerCase()))
                        { save = false; toastr.warning('No file types allowed! Only XLS and XLSX.', 'Warning'); }

                    else if (file.size > (2 * 1024 * 1024))
                        { save = false; toastr.warning('The file size is too large! Maximum 2MB.', 'Warning'); }

                    else if (get_datas[position] != undefined && select_id == '') {
                        if (contracts.some(value => get_datas[position].includes(value)))
                            { save = false; toastr.warning('Contract with that Position has been added!', 'Warning'); }
                    }

                    if (save) { getprocess('save/attachment', new FormData($('#formAttachment')[0])); }
                }
            }]
        });
    });

    function download_attachment(id) {
        var name = window.atob(id);
        var ext = '.'+name.split('.')[1];
        var date = new Date();
        var y = date.getFullYear();
        var m = (date.getMonth()+1).toString().padStart(2, '0');
        var d = (date.getDate()).toString().padStart(2, '0');
        var a = document.createElement('a');

        a.href = '<?= base_url('./assets/document/master_contract/') ?>'+name;
        a.download = 'master_contract_'+y+m+d+ext;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function add() {
        select_id = '';

        $('#attachment').filebox('clear');
        $('#position').combobox('clear').combobox('enable');
        $('#contract').combobox('clear').combobox('enable');
        $('#dlg_add').dialog('setTitle', 'Add New').dialog('open');
    }

    function update() {
        var row = $('#dg').datagrid('getSelections');
        if (row > 1 || row == 0) { toastr.warning("Please select one of the data in the table first!", "Warning"); }
        else {
            var get = row[0];
            select_id = window.btoa(get.id);

            $('#attachment').filebox('clear');
            $('#position').combobox('setValue', get.position_id).combobox('disable');
            $('#contract').combobox('setValues', get.contract_id.split(',')).combobox('disable');
            $('#dlg_add').dialog('setTitle', 'Update data').dialog('open');
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    var data = [];

                    rows.forEach(function (value, key) { data.push(value.id); });

                    getprocess('delete', {id: window.btoa(JSON.stringify(data))});
                }
            });
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function reload() { window.location.reload(); }

    function getprocess(act = '', data = null) {
        var ajax = {
            url: '<?= base_url('employee/master_contract/'); ?>'+act,
            data: data,
            dataType: 'json',
            success: function (result) {
                $('#dg').datagrid('loaded');

                if (act == 'forms') {
                    $('#position').combobox('loadData', result.position);
                    $('#contract').combobox('loadData', result.contract);
                } else if (act == 'save/attachment') {
                    if (result.stat != 'ok') { toastr.warning('Failed upload!', 'Warning'); }
                    else {
                        var data = {attachment: window.btoa(result.attachment)};

                        if (select_id == '') {
                            data.position = window.btoa($('#position').combobox('getValue'));
                            data.contract = window.btoa($('#contract').combobox('getValues').join(','));
                        } else { data.id = select_id; }

                        getprocess('save', data);
                    }
                } else if (act == 'save') {
                    if (result.stat != 'ok') { toastr.error('Failed save!', 'Error'); }
                    else {
                        toastr.success('Success save');
                        $('#dlg_add').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                } else if (act == 'delete') {
                    if (result.stat != 'ok') { toastr.error('Failed delete!', 'Error'); }
                    else { toastr.success('Success delete'); $('#dg').datagrid('reload'); }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
                $.messager.alert("Error", jqXHR.statusText, 'error');
            }
        };

        if (act == 'save/attachment') {
            ajax.type = 'POST';
            ajax.processData = false;
            ajax.contentType = false;
        } else {
            ajax.async = false;
            ajax.method = 'POST';
        }

        $('#dg').datagrid('loading');
        $.ajax(ajax);
    }

    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }
</script>