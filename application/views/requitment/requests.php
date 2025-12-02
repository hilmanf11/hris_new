<!-- TABLE DATAGRID -->

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">

    <thead>

        <tr>

            <th rowspan="2" field="ck" checkbox="true"></th>

            <th rowspan="2" data-options="field:'request_by',width:150,halign:'center'">Request By</th>

            <th rowspan="2" data-options="field:'request_date',width:100,align:'center'">Request Date</th>

            <th rowspan="2" data-options="field:'due_date',width:120,align:'center'">Due Date</th>

            <th rowspan="2" data-options="field:'request_form',width:100,halign:'center'">Request Form</th>

            <th rowspan="2" data-options="field:'division_name',width:200,halign:'center'">Division</th>

            <th rowspan="2" data-options="field:'departement_name',width:150,halign:'center'">Departement</th>

            <th rowspan="2" data-options="field:'departement_sub_name',width:150,halign:'center'">Departement Sub</th>

            <th rowspan="2" data-options="field:'position',width:150,halign:'center'">Position</th>

            <th rowspan="2" data-options="field:'request',width:150,halign:'center'">Request Type</th>

            <th rowspan="2" data-options="field:'qty',width:120,align:'center'">Request Qty</th>

            <th rowspan="2" data-options="field:'description',width:300,halign:'center'">Note</th>

            <th rowspan="2" data-options="field:'attachment_pmk',width:155,halign:'center',align:'center',formatter:formatBtnAttachment">PMK Form</th>

            <th rowspan="2" data-options="field:'attachment_so',width:155,halign:'center',align:'center',formatter:formatBtnAttachment">Organizational Structure</th>

            <th rowspan="2" data-options="field:'attachment_job',width:155,halign:'center',align:'center',formatter:formatBtnAttachment">Job Desc</th>

            <th rowspan="2" data-options="field:'attachment_mp',width:155,halign:'center',align:'center',formatter:formatBtnAttachment">MP Need Analysis</th>

            <th rowspan="2" data-options="field:'status_notification',width:100,align:'center',styler:approvalStyler, formatter:approvalFormatter">Approval</th>

            <th rowspan="2" data-options="field:'status_check',width:120,align:'center'">Approval By</th>

            <th rowspan="2" data-options="field:'stat_request',width:135,align:'center',styler:statusStyler">Status</th>

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

<div id="toolbar">

    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">

        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">

            <fieldset style="width: 49%; border:2px solid #d0d0d0; border-radius:4px; float: left;">

                <legend><b>Form Filter Data</b></legend>

                <div class="fitem">

                    <span style="width:35%; display:inline-block;">Request Date</span>

                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">

                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">

                </div>

                <div class="fitem">

                    <span style="width:35%; display:inline-block;">Request Name</span>

                    <input style="width:60%;" id="filter_request" class="easyui-combobox">

                </div>

                <div class="fitem">

                    <span style="width:35%; display:inline-block;">Approval</span>

                    <select style="width:60%;" id="filter_approval" class="easyui-combobox" data-options="panelHeight:'auto'">

                        <option value="">Choose All</option>

                        <option value="0">Approve</option>

                        <option value="1">Checked</option>

                    </select>

                </div>

                <div class="fitem">

                    <span style="width:35%; display:inline-block;"></span>

                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>

                </div>

            </fieldset>

            <fieldset style="width: 49%; border:2px solid #d0d0d0; border-radius:4px; float: left;">

                <legend><b>Information Request</b></legend>

                <div style="height: 146px; overflow: auto;" id="infoproposal">

                    <center>

                        <h4>No information at this time</h4>

                    </center>

                </div>

            </fieldset>

        </div>

    </div>

    <?= $button ?>

    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnPrintForm()"><i class="fa fa-print"></i> Print Form</a>

    <?php if ($button_status) { ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnUpdateStatus()"><i class="fa fa-edit"></i> Update Status</a>
    <?php } ?>
</div>

<div id="dlg_status_request" class="easyui-dialog" style="padding: 5px;">
    <form><table style="width: 170px;">
        <tr><td><input class="easyui-radiobutton" name="update_status" id="update_status_progress" value="progress"></td></tr>
        <tr><td><input class="easyui-radiobutton" name="update_status" id="update_status_medical_document" value="medical_document"></td></tr>
        <tr><td><input class="easyui-radiobutton" name="update_status" id="update_status_interview" value="interview"></td></tr>
        <tr><td><input class="easyui-radiobutton" name="update_status" id="update_status_psychotest" value="psychotest"></td></tr>
        <tr><td><input class="easyui-radiobutton" name="update_status" id="update_status_closed" value="closed"></td></tr>
    </table></form>
</div>

<div id="dlg_form" class="easyui-dialog" title="Print Form" data-options="{closed: true, modal: true}" style="padding-left: 60px;">
    <table style="width: 420px;">
        <tr><td colspan="2" style="height: 8px;"></td></tr>
        <tr>
            <td>Request Form</td>
            <td style="padding-left: 20px;"><form>
                <input type="radio" name="div_form" id="div_form_pkl">
                <input type="radio" name="div_form" id="div_form_magang">
                <input type="radio" name="div_form" id="div_form_other">
            </form></td>
        </tr>
        <tr><td colspan="2" style="height: 8px;"></td></tr>
        <tr>
            <td>Division</td>
            <td><input id="div_form" class="easyui-combobox"></td>
        </tr>
        <tr><td colspan="2" style="height: 8px;"></td></tr>
        <tr>
            <td>Department</td>
            <td><input id="dept_form" class="easyui-combobox"></td>
        </tr>
        <tr><td colspan="2" style="height: 8px;"></td></tr>
        <tr>
            <td>Request Date</td>
            <td><input id="req_date_form" class="easyui-combobox"></td>
        </tr>
        <tr><td colspan="2" style="height: 8px;"></td></tr>
        <tr>
            <td colspan="2" align="center">
                <a href="javascript:;" id="btn_print" class="btn btn-primary" onclick="btnForm('print')"><i class="fa fa-print"></i> Print</a>
                <a href="javascript:;" id="btn_excel" class="btn btn-success" onclick="btnForm('excel')"><i class="fa fa-file"></i> Export Excel</a>
            </td>
        </tr>
    </table>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">

    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; 
                         margin-bottom: 10px; border-radius:4px;">
            <legend><b>Form New Request</b></legend>

            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                
                <!-- Kolom kiri -->
                <div style="flex: 1; min-width: 350px;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request Form</span>
                        <div style="width:60%; display:inline-block;">
                            <label><input type="radio" name="request_form" id="request_form_pkl"></label>
                            <label><input type="radio" name="request_form" id="request_form_magang"></label>
                            <label><input type="radio" name="request_form" id="request_form_other"></label>
                        </div>
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request Date</span>
                        <input style="width:60%;" name="request_date" id="request_date" required="" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Required Date</span>
                        <input style="width:60%;" name="due_date" id="due_date" required="" data-options="formatter:myformatter,parser:myparser,editable: false" class="easyui-datebox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request By</span>
                        <input style="width:60%;" name="request_by" id="request_by" readonly="true" class="easyui-textbox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Division</span>
                        <input style="width:60%;" name="division_id" id="division_id" required="" class="easyui-combobox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement</span>
                        <input style="width:60%;" name="departement_id" id="departement_id" required="" class="easyui-combobox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement Sub</span>
                        <input style="width:60%;" name="departement_sub_id" id="departement_sub_id" required="" class="easyui-combobox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Position</span>
                        <input style="width:60%;" name="position_id" id="position_id" required="" class="easyui-combobox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request Qty</span>
                        <input style="width:30%;" name="qty" required="true" class="easyui-numberbox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Gender</span>
                        <div style="width:60%; display:inline-block;">
                            <label>
                                <input type="radio" name="gender" id="gender_male" required>
                            </label>
                            <label style="margin-left: 10px;">
                                <input type="radio" name="gender" id="gender_female" required>
                            </label>
                        </div>
                    </div>

                    <div class="fitem form_other">
                        <span style="width:35%; display:inline-block;">Education</span>
                        <input style="width:60%;" name="education" id="education" class="easyui-textbox">
                    </div>

                    <div class="fitem form_pkl">
                        <span style="width:35%; display:inline-block;">Length of Service</span>
                        <input style="width:60%;" name="length_service" id="length_service" class="easyui-textbox">
                    </div>
                </div>

                <!-- Kolom kanan -->
                <div style="flex: 1; min-width: 350px;">
                    <div class="fitem form_other">
                        <span style="width:35%; display:inline-block;">Experience</span>
                        <input style="width:60%;" name="experience" id="experience" class="easyui-textbox">
                    </div>

                    <div class="fitem form_pkl">
                        <span style="width:35%; display:inline-block;">Minimum Height</span>
                        <div style="width:60%; display:inline-block;">
                            <label>
                                <input type="radio" name="min_height" id="min_height_155">
                            </label>
                            <label style="margin-left: 10px;">
                                <input type="radio" name="min_height" id="min_height_none">
                            </label>
                        </div>
                    </div>

                    <div class="fitem form_pkl">
                        <span style="width:35%; display:inline-block;">SMA / SMK</span>
                        <input style="width:60%;" name="schools" id="school" class="easyui-textbox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request Type</span>
                        <div style="width:60%; display:inline-block;">
                            <label>
                                <input type="radio" name="request" id="request_new_request" required>
                            </label>
                            <label style="margin-left: 10px;">
                                <input type="radio" name="request" id="request_replacement" required>
                            </label>
                        </div>
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Previous Employees</span>
                        <input style="width:60%;" name="previous" id="previous" required="true" class="easyui-combobox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Prev Employees Name</span>
                        <input style="width:60%;" name="previous_emp" id="previous_emp" required="true" class="easyui-combobox">
                    </div>

                    <div class="fitem form_other">
                        <span style="width:35%; display:inline-block;">Candidate Sources</span>
                        <div style="width:60%; display:inline-block;">
                            <label>
                                <input type="radio" name="candidate_sources" id="candidate_internal">
                            </label>
                            <label style="margin-left: 10px;">
                                <input type="radio" name="candidate_sources" id="candidate_external">
                            </label>
                        </div>
                    </div>

                    <div class="fitem form_pkl">
                        <span style="width:35%; display:inline-block;">Skills</span>
                        <div style="width:60%; display:inline-block;">
                            <label>
                                <input type="radio" name="skills" id="skill_computer">
                            </label>
                            <label style="margin-left: 10px;">
                                <input type="radio" name="skills" id="skill_others">
                            </label>
                        </div>
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Additional Information</span>
                        <input style="width:60%; height: 40px;" name="description" id="description" class="easyui-textbox">
                    </div>

                    <div class="fitem form_pkl">
                        <span style="width:35%; display:inline-block;">Notes</span>
                        <input style="width:60%; height: 40px;" name="notes" id="notes" multiline="true" class="easyui-textbox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PMK Form</span>
                        <input style="width:60%;" name="attachment_pmk" id="attachment_pmk" class="easyui-filebox">
                    </div>

                    <div class="fitem form_other">
                        <span style="width:35%; display:inline-block;">Organizational Structure</span>
                        <input style="width:60%;" name="attachment_so" id="attachment_so" class="easyui-filebox">
                    </div>

                    <div class="fitem form_other">
                        <span style="width:35%; display:inline-block;">Job Desc</span>
                        <input style="width:60%;" name="attachment_job" id="attachment_job" class="easyui-filebox">
                    </div>

                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">MP Need Analysis</span>
                        <input style="width:60%;" name="attachment_mp" id="attachment_mp" class="easyui-filebox">
                    </div>
                </div>

            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->

<iframe id="printout" src="<?= base_url('requitment/requests/print') ?>" style="width: 100%;" hidden></iframe>

<iframe id="print_request" style="width: 100%;" hidden></iframe>

<script>
    var stat_add = true;

    //ADD DATA

    function add() {
        url_save = '<?= base_url('requitment/requests/create') ?>';
        stat_add = true;

        $('#dlg_insert').dialog('open');

        $('#frm_insert').form('clear');

        $('#request_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $('#due_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $('#request_by').textbox('setValue', '<?= $this->session->name ?>');
        $('#request_form_other').radiobutton({checked: true});
        $('#previous_emp').combobox({disabled: true, required: false});
        $('#attachment_pmk').filebox('disable');
        $('#attachment_so').filebox('disable');
        $('#attachment_job').filebox('disable');
        $('#attachment_mp').filebox('disable');

        checkFormOther();
        prevEmpVali();
    }

    //EDIT DATA

    function update() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            url_save = '<?= base_url('requitment/requests/update') ?>?id=' + btoa(row.id);
            stat_add = false;

            $('#dlg_insert').dialog('open');

            $('#frm_insert').form('load', row);

            $('#attachment_pmk').filebox('clear');
            $('#attachment_so').filebox('clear');
            $('#attachment_job').filebox('clear');
            $('#attachment_mp').filebox('clear');

            if (row.request_form == 'PKL') { checkFormPkl(); }
            else { checkFormOther(); }
            filesVali();
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
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

                            url: '<?= base_url('requitment/requests/delete') ?>',

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

    //FILTER DATA

    function filter() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_request = $("#filter_request").combobox('getValue');

        var filter_approval = $("#filter_approval").combobox('getValue');



        var url = "?filter_from=" + filter_from +

            "&filter_to=" + filter_to +

            "&filter_request=" + filter_request +

            "&filter_approval=" + filter_approval;



        $('#dg').datagrid('reload', '<?= base_url('requitment/requests/datatables') ?>'+url);



        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

        $("#printout").attr('src', '<?= base_url('requitment/requests/print') ?>' + url);

    }

    //PRINT PDF

    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }

    //PRINT EXCEL

    function excel() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_request = $("#filter_request").combobox('getValue');

        var filter_approval = $("#filter_approval").combobox('getValue');



        var url = "?filter_from=" + filter_from +

            "&filter_to=" + filter_to +

            "&filter_request=" + filter_request +

            "&filter_approval=" + filter_approval;



        window.location.assign('<?= base_url('requitment/requests/print/excel') ?>' + url);

    }

    //RELOAD

    function reload() {

        window.location.reload();

    }

    var id_selected = '';
    var url_division = '<?= base_url('employee/divisions/reads') ?>';
    var url_departement = '<?= base_url('employee/departements/reads') ?>';
    var url_departement_sub = '<?= base_url('employee/departement_subs/reads') ?>';
    var data_form = [];
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('requitment/requests/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageSize: 20,
            pageList: [20, 50, 100, 500, 1000],
            onLoadSuccess: function (data) { $('.easyui-linkbutton').linkbutton(); }
        });

        $('#filter_request').combobox({
            url: '<?php echo base_url('requitment/requests/readRequests'); ?>',
            valueField: 'request_by',
            textField: 'request_by',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }],
        });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var id_files = [];
                    var files = [];

                    if ($('input[name="request"]:checked').val() == 'Replacement') {
                        id_files.push('attachment_pmk');
                        files.push($('#attachment_pmk').filebox('getValue'));
                    } else {
                        id_files.push('attachment_mp');
                        files.push($('#attachment_mp').filebox('getValue'));

                        if ($('input[name="request_form"]:checked').val() == 'Others') {
                            id_files.push('attachment_so');
                            id_files.push('attachment_job');

                            files.push($('#attachment_so').filebox('getValue'));
                            files.push($('#attachment_job').filebox('getValue'));
                        }
                    }

                    if ((stat_add) ? !files.some(value => value == '') : true) {
                        var field_files = files.some(value => value != '');
                        var msg = '';

                        if (field_files) {
                            id_files.forEach(function (value, key) {
                                if (msg != '') { return; }

                                var input = $('#'+value).next().find('input[type=file]')[0];
                                if (input.files && input.files.length > 0) {
                                    var file = input.files[0];
                                    if (!['pdf', 'xls', 'xlsx'].includes(file.name.split('.').pop().toLowerCase()))
                                        { msg = 'No file types allowed! Only PDF, XLS, and XLSX.'; }
                                    else if (file.size > (2 * 1024 * 1024))
                                        { msg = 'The file size is too large! Maximum 2MB.'; }
                                }
                            });

                            if (msg == '') {
                                $.ajax({
                                    url: '<?= base_url('requitment/requests/upload'); ?>',
                                    data: new FormData($('#frm_insert')[0]),
                                    type: 'POST',
                                    dataType: 'json',
                                    processData: false,
                                    contentType: false,
                                    beforeSend: function () { $.messager.progress({title:'Process Upload', msg:'Please wait...'}); },
                                    success: function (result) { $.messager.progress('close'); saveInput(result); },
                                    error: function() { $.messager.progress('close'); toastr.error('Failed upload!', 'Error'); }
                                });
                            } else { toastr.warning(msg, 'Warning'); }
                        } else { saveInput(); }
                    } else { toastr.warning('Please fill in all required fields!', 'Warning'); }
                }
            }]
        });

        $('#request_form_pkl').radiobutton({
            label: 'PKL',
            value: 'PKL',
            labelWidth: 40,
            labelPosition: 'after',
            onChange: function (checked) { if (checked) { checkFormPkl(); } }
        });

        $('#request_form_magang').radiobutton({
            label: 'Magang',
            value: 'Magang',
            labelWidth: 70,
            labelPosition: 'after',
            onChange: function (checked) { if (checked) { checkFormOther(); } }
        });

        $('#request_form_other').radiobutton({
            checked: true,
            label: 'Others',
            value: 'Others',
            labelWidth: 60,
            labelPosition: 'after',
            onChange: function (checked) { if (checked) { checkFormOther(); } }
        });

        $('#division_id').combobox({
            url: url_division,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Division',
            onSelect: function(division) { $('#departement_id').combobox('reload', url_departement+'?division_id='+division.id); }
        });

        $('#departement_id').combobox({
            url: url_departement,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Departement',
            onSelect: function(departement) { $('#departement_sub_id').combobox('reload', url_departement_sub
                +'?division_id='+$('#division_id').combobox('getValue')
                +'&departement_id='+departement.id
            ); }
        });

        $('#departement_sub_id').combobox({
            url: url_departement_sub,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Departement Sub'
        });

        $('#position_id').combobox({
            url: '<?php echo base_url('employee/positions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Position',
            panelHeight: '200',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });

        $('#gender_male').radiobutton({value: 'Male', label: 'Male', labelPosition: 'after'});
        $('#gender_female').radiobutton({value: 'Female', label: 'Female', labelPosition: 'after'});

        $('#min_height_155').radiobutton({label: 'Min. 155 cm', value: 'Min. 155 cm', labelPosition: 'after'});
        $('#min_height_none').radiobutton({label: 'None', value: 'None', labelPosition: 'after'});

        $('#candidate_internal').radiobutton({value: 'Internal', label: 'Internal', labelPosition: 'after'});
        $('#candidate_external').radiobutton({value: 'External', label: 'External', labelPosition: 'after'});

        $('#skill_computer').radiobutton({label: 'Computer', value: 'Computer', labelPosition: 'after'});
        $('#skill_others').radiobutton({label: 'Others', value: 'Others', labelPosition: 'after'});

        $('#request_new_request').radiobutton({
            label: 'New Request',
            value: 'New Request',
            labelPosition: 'after',
            labelWidth: 90,
            onChange: function (checked) { if (checked) { filesVali(); prevEmpVali(); } }
        });
        $('#request_replacement').radiobutton({
            label: 'Replacement',
            value: 'Replacement',
            labelPosition: 'after',
            labelWidth: 90,
            onChange: function (checked) { if (checked) { filesVali(); prevEmpVali(); } }
        });

        $('#previous').combobox({
            textField: 'value',
            valueField: 'value',
            panelHeight: 'auto',
            prompt: 'Choose Previous Status'
        });

        $('#previous_emp').combobox({
            url: '<?= base_url('requitment/requests/readPrevEmployee') ?>',
            prompt: 'Choose Previous Employee Name',
            valueField: 'id',
            textField: 'name'
        });

        $('#description').textbox({
            required: true,
            multiline: true,
            prompt: 'Please fill in with the job description or job classification.',
        })

        $.ajax({
            type: "post",
            url: "<?= base_url('requitment/requests/readInformation') ?>",
            dataType: "html",
            success: function(response) { $("#infoproposal").html(response); }
        });

        $('#dlg_form').dialog('close');

        $('#div_form_pkl').radiobutton({
            label: 'PKL',
            value: 'PKL',
            labelWidth: 40,
            labelPosition: 'after',
            onChange: function (checked) { if (checked) { formDiv('PKL'); } }
        });

        $('#div_form_magang').radiobutton({
            label: 'Magang',
            value: 'Magang',
            labelWidth: 70,
            labelPosition: 'after',
            onChange: function (checked) { if (checked) { formDiv('Magang'); } }
        });

        $('#div_form_other').radiobutton({
            label: 'Others',
            value: 'Others',
            labelWidth: 60,
            labelPosition: 'after',
            onChange: function (checked) { if (checked) { formDiv('Others'); } }
        });

        $('#div_form').combobox({
            required: true,
            valueField: 'id',
            textField: 'name',
            onSelect: function (rec) {
                var filter = data_form.filter(value => value.div_id == rec.id);
                var data = [];

                for (var i = 0; i < filter.length; i++) {
                    if (!data.some(value => value.id == filter[i].dept_id))
                        { data.push({id: filter[i].dept_id, name: filter[i].dept_name}); }
                }

                $('#req_date_form').combobox('clear');
                $('#dept_form')
                    .combobox('clear')
                    .combobox('enable')
                    .combobox('loadData', data);
            },
            onLoadSuccess: function (data) { if (data.length == 1) { $(this).combobox('setValue', data[0].id); } }
        });

        $('#dept_form').combobox({
            required: true,
            valueField: 'id',
            textField: 'name',
            onSelect: function (rec) {
                var filter = data_form.filter(value => value.dept_id == rec.id);
                var data = [];

                for (var i = 0; i < filter.length; i++) {
                    if (!data.some(value => value.date == filter[i].date))
                        { data.push({date: filter[i].date}); }
                }

                $('#req_date_form')
                    .combobox('clear')
                    .combobox('enable')
                    .combobox('loadData', data);
            },
            onLoadSuccess: function (data) { if (data.length == 1) { $(this).combobox('setValue', data[0].id); } }
        });

        $('#req_date_form').combobox({
            required: true,
            textField: 'date',
            valueField: 'date',
            onSelect: function (rec) {
                $('#btn_print').prop('style', 'pointer-events: visible; opacity: 1;');
                $('#btn_excel').prop('style', 'pointer-events: visible; opacity: 1;');
            },
            onLoadSuccess: function (data) { if (data.length == 1) { $(this).combobox('setValue', data[0].date); } }
        });

        $("#print_request").attr('src', '');

        $('#dlg_status_request').dialog({
            title: 'Update Status',
            closed: true,
            modal: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var stat = $('input[name="update_status"]:checked').val();

                    if (stat == undefined) { toastr.warning('Please select one status!', 'Update Status'); }
                    else {
                        $('#dlg_status_request').dialog('close');
                        $('#dg').datagrid('loading');
                        $.ajax({
                            url: '<?= base_url('requitment/requests/updateStatus'); ?>',
                            data: {id: id_selected, status: btoa(stat)},
                            async: false,
                            method: 'POST',
                            dataType: 'json',
                            success: function (result) {
                                $('#dg').datagrid('loaded');
                                if (result.status != '') {
                                    toastr.error('Failed', 'Update Status');
                                    $('#dlg_status_request').dialog('open');
                                } else {
                                    toastr.success('Success', 'Update Status');
                                    $('#dlg_status_request').dialog('close');
                                    $('#dg').datagrid('reload');
                                }
                            }
                        });
                    }
                }
            }]
        });
        [
            {progress: "Progress"},
            {medical_document: "Medical & Document"},
            {interview: "Interview"},
            {psychotest: "Psychotest"},
            {closed: "Closed"}
        ].forEach(function (value, key) {
            var obj = Object.entries(value)[0];
            $('#update_status_'+obj[0]).radiobutton({
                label: obj[1],
                value: obj[0],
                labelWidth: 140
            });
        });
    });

    function btnPrintForm() {
        $('#div_form_pkl').radiobutton('uncheck');
        $('#div_form_magang').radiobutton('uncheck');
        $('#div_form_other').radiobutton('uncheck');
        $('#div_form').combobox('clear').combobox('disable');
        $('#dept_form').combobox('clear').combobox('disable');
        $('#req_date_form').combobox('clear').combobox('disable');
        $('#btn_print').prop('style', '');
        $('#btn_excel').prop('style', '');
        $('#dlg_form').dialog('open');

        $('#dg').datagrid('loading');
        $.ajax({
            url: '<?= base_url('requitment/requests/readForm'); ?>',
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                data_form = result;
                $('#dg').datagrid('loaded');
            }
        });
    }

    function formDiv(form) {
        var filter = data_form.filter(value => value.div_form == form);
        var data = [];

        for (var i = 0; i < filter.length; i++) {
            if (!data.some(value => value.id == filter[i].div_id))
                { data.push({id: filter[i].div_id, name: filter[i].div_name}); }
        }

        $('#dept_form').combobox('clear');
        $('#req_date_form').combobox('clear');
        $('#div_form').combobox('clear').combobox('loadData', data).combobox('enable');
    }

    function btnForm(act) {
        var form = window.btoa($('input[name="div_form"]:checked').val());
        var div = window.btoa($('#div_form').combobox('getValue'));
        var dept = window.btoa($('#dept_form').combobox('getValue'));
        var req_date = window.btoa($('#req_date_form').combobox('getValue'));
        var url = '?form='+form+'&div='+div+'&dept='+dept+'&req_date='+req_date;

        if ([form, div, dept, req_date].some(value => value == '')) { toastr.warning("Please fill in all fields!", "Information"); }
        else if (act == 'print') { $("#print_request").attr('src', '<?= base_url('requitment/requests/printRequest') ?>'+url); }
        else if (act == 'excel') { window.location.assign('<?= base_url('requitment/requests/printRequest/excel') ?>'+url); }
        else { toastr.warning("No action!", "Information"); }
    }

    function btnUpdateStatus() {
        var dg = $('#dg').datagrid('getSelections');

        if (dg.length != 1) { toastr.warning('Please select one data!', 'WARNING'); }
        else {
            id_selected = btoa(dg[0].id);
            ['progress', 'medical_document', 'interview', 'psychotest', 'closed'].forEach(function (value, key) {
                $('#update_status_'+value).radiobutton((value == dg[0].status_request) ? 'check' : 'uncheck');
            });
            $('#dlg_status_request').dialog('open');
        }
    }

    function saveInput(upload = []) {
        $('#frm_insert').form('submit', {
            url: url_save,
            onSubmit: function(param) {
                if (upload.length != 0) { upload.forEach(function (value, key) {
                    param[value.index] = value.value;
                }); }

                if ($('input[name="request_form"]:checked').val() != 'Others') {
                    if (param.attachment_so != undefined) { delete param.attachment_so; }
                    if (param.attachment_job != undefined) { delete param.attachment_job; }
                }

                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');

                if (result.theme == "success")
                    { toastr.success(result.message, result.title); }
                else { toastr.error(result.message, result.title); }

                $('#dlg_insert').dialog('close');
                $('#dg').datagrid('reload');
            }

        });
    }

    var previous_data = [
        [{value: 'Vacant Position'}, {value: 'Internship Period Ended'}, {value: 'Mutation'}, {value: 'Did Not Return'}, {value: 'Not Potential'}],
        [{value: 'Vacant Position'}, {value: 'Termination'}, {value: 'Resign'}, {value: 'Mutation'}, {value: 'Promotion'}, {value: 'Demotion'}]
    ];
    function checkFormPkl() {
        $('.form_pkl').show();
        $('.form_other').hide();

        $('#education').textbox({required: false});
        $('#experience').textbox({required: false});
        $('#candidate_internal').radiobutton({required: false});
        $('#candidate_external').radiobutton({required: false});

        $('#length_service').textbox({required: true});
        $('#school').textbox({required: true});
        $('#notes').textbox({required: true});
        $('#skill_others').radiobutton({required: true});
        $('#skill_computer').radiobutton({required: true});

        $('#previous').combobox('loadData', previous_data[0]);
    }

    function checkFormOther() {
        $('.form_pkl').hide();
        $('.form_other').show();

        $('#education').textbox({required: true});
        $('#experience').textbox({required: true});
        $('#candidate_internal').radiobutton({required: true});
        $('#candidate_external').radiobutton({required: true});

        $('#length_service').textbox({required: false});
        $('#school').textbox({required: false});
        $('#notes').textbox({required: false});
        $('#skill_others').radiobutton({required: false});
        $('#skill_computer').radiobutton({required: false});

        $('#previous').combobox('loadData', previous_data[1]);
    }

    function prevEmpVali() {
        var options = {disabled: true, required: false, value: ''};

        if ($('input[name="request"]:checked').val() == 'Replacement') {
            options.disabled = false;
            options.required = true;
            options.value = $('#previous_emp').combobox('getValue');
        }
        $('#previous_emp').combobox(options);
    }

    function filesVali() {
        var options = [{required: true, disabled: false}, {required: false, disabled: true}];
        var stat = $('input[name="request"]:checked').val() == 'Replacement';
        var form = $('input[name="request_form"]:checked').val() == 'Others';

        $('#attachment_pmk').filebox((stat) ? options[0] : options[1]);
        if (form) {
            $('#attachment_so').filebox((stat) ? options[1] : options[0]);
            $('#attachment_job').filebox((stat) ? options[1] : options[0]);
        }
        $('#attachment_mp').filebox((stat) ? options[1] : options[0]);

        if (!stat_add) {
            if ($('#dg').datagrid('getSelected').request == 'Replacement')
                { $('#attachment_pmk').filebox({required: false}); }
            else {
                if (form) {
                    $('#attachment_so').filebox({required: false});
                    $('#attachment_job').filebox({required: false});
                }
                $('#attachment_mp').filebox({required: false});
            }
        }
    }

    //CELLSTYLE APPROVAL

    function approvalStyler(value, row, index) {

        if (value == "" || value == null) {

            return 'background: #53D636; color:white;';

        } else {

            return 'background: #FF5F5F; color:white;';

        }

    }

    //FORMATTER APPROVAL

    function approvalFormatter(value) {

        if (value == "" || value == null) {

            return 'Approved';

        } else {

            return 'Checked';

        }

    };



    //CELLSTYLE STATUS

    function statusStyler(value, row, index) {
        var style = null;

        if (value != 'Submission') {
            style = 'color: white; background: '+(
                (value == 'Closed') ? '#53D636' : 'orange'
            )+';';
        }

        return style;
    }

    //Formatter Attachment
    function formatBtnAttachment(value, row, index) { return (value == '' || value == null) ? '' : '<a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="btnAttachment('+"'"+window.btoa(value)+"','"+this.field+"'"+')"><i class="fa fa-download"></i> Download</a>'; }
    function btnAttachment(file, field) {
        var name = window.atob(file);
        var ext = '.'+name.split('.')[1];
        var date = new Date();
        var y = date.getFullYear();
        var m = (date.getMonth()+1).toString().padStart(2, '0');
        var d = (date.getDate()).toString().padStart(2, '0');
        var a = document.createElement('a');

        a.href = '<?= base_url('assets/document/reqruitment/attachment/') ?>'+name;
        a.download = field+'_'+y+m+d+ext;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    //Format Datepicker

    function myformatter(date) {

        var y = date.getFullYear();

        var m = date.getMonth() + 1;

        var d = date.getDate();

        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);

    }



    //Format Datepicker

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