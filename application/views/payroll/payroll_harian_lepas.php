<!-- TABLE DATAGRID -->

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">

    <thead frozen="true">

        <tr>

            <th data-options="field:'id',width:80,halign:'center',hidden:true">ID</th>

            <th data-options="field:'number',width:150,halign:'center'">Employee ID</th>

            <th data-options="field:'name',width:250,halign:'center'">Employee Name</th>

        </tr>

    </thead>

    <thead>

        <tr>

            <!-- <th rowspan="2" data-options="field:'status_resign',width:80,halign:'center', formatter:statusResign, styler:styleStatusResign">Status</th> -->

            <th data-options="field:'division_name',width:200,halign:'center'">Division</th>

            <th data-options="field:'departement_name',width:200,halign:'center'">Departement</th>

            <th data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>

            <th data-options="field:'job_type',width:100,halign:'center'">Job Type</th>

            <th data-options="field:'contract_name',width:140,halign:'center'">Employee Type</th>

            <!-- <th rowspan="2" data-options="field:'source_name',width:200,halign:'center'">Source</th> -->

            <th data-options="field:'position_name',width:100,halign:'center'">Position</th>

            <th data-options="field:'marital',width:80,halign:'center'">Marital</th>

            <th data-options="field:'ter_number',width:80,halign:'center'">TER Code</th>

            <th data-options="field:'group_name',width:100,halign:'center'">Group</th>
            
            <th data-options="field:'bank_no',width:150,halign:'center'">No. Rekening</th>

            <th data-options="field:'date_sign',width:100,halign:'center'">Join Date</th>

            <th data-options="field:'date_expired',width:100,halign:'center'">Expired Date</th>

            <th data-options="field:'resign_date',width:100,halign:'center'">Resign Date</th>

            <th data-options="field:'attandance_wd',width:150,halign:'center'">Working Day</th>

            <th data-options="field:'salary',width:150,halign:'center'">Salary</th>

            <th data-options="field:'income',width:100,halign:'center'">Income</th>

            <th data-options="field:'correction_plus',width:100,halign:'center'">Correction Plus</th>

            <th data-options="field:'bruto_income',width:100,align:'center', formatter:numberformat">Bruto Income</th>

            <th data-options="field:'correction_minus',width:100,align:'center', formatter:numberformat">Correction Minus</th>

            <th data-options="field:'ter',width:100,align:'center', formatter:numberformat">TER</th>

            <th data-options="field:'net_income',width:100,align:'center', formatter:numberformat">Net Income</th>

        </tr>

    </thead>

</table>



<!-- TOOLBAR DATAGRID -->

<div id="toolbar">

    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">

        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">

            <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">

                <legend><b>Form Filter Data</b></legend>

                <div style="width: 50%; float: left;">

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Period Date</span>

                        <input style="width:28%;" name="filter_from" id="filter_from" class="easyui-combogrid"> To

                        <input style="width:28%;" name="filter_to" id="filter_to" data-options="prompt:'Date To'" readonly class="easyui-textbox">

                    </div>

                    <div class="fitem" hidden>

                        <span style="width:30%; display:inline-block;">Locked</span>

                        <input style="width:28%;" name="filter_lock" id="filter_lock" readonly class="easyui-numberbox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Division</span>

                        <input style="width:60%;" id="filter_division" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Departement</span>

                        <input style="width:60%;" id="filter_departement" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Departement Sub</span>

                        <input style="width:60%;" id="filter_departement_sub" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;"></span>

                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>

                        <a href="javascript:;" class="easyui-linkbutton" onclick="formula()"><i class="fa fa-percent"></i> Formula</a>

                    </div>

                </div>

                <div style="width: 50%; float: left;">

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Employee</span>

                        <input style="width:60%;" id="filter_employee" class="easyui-combogrid">

                    </div>

                    <!-- <div class="fitem">

                        <span style="width:30%; display:inline-block;">Employee Type</span>

                        <input style="width:60%;" id="filter_employee_type" name="filter_employee_type" class="easyui-combobox">

                    </div>

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Group</span>

                        <input style="width:60%;" id="filter_group" name="filter_group" class="easyui-combobox">

                    </div> -->

                    <div class="fitem">

                        <span style="width:30%; display:inline-block;">Status</span>

                        <select style="width:30%;" id="filter_status" class="easyui-combobox" data-options="panelHeight:'auto'">

                            <option value="">Choose All</option>

                            <option value="0">Active</option>

                            <option value="1">Resign</option>

                        </select>

                    </div>

                </div>

            </fieldset>

        </div>

    </div>



    <?= $button ?>

    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="pdfReport()"><i class="fa fa-print"></i> Print Report</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excelReport()"><i class="fa fa-file"></i> Export Report</a>
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="printcsv()"><i class="fa fa-file"></i> Export to CSV</a>
</div>



<div id="dlg_generate" class="easyui-dialog" title="Generating Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">

    <div class="alert alert-warning" role="alert">

        Please wait until the generate process is complete

    </div>

    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>

    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>

    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">

        <p>History Generate</p>

        <ul id="remarks">



        </ul>

    </div>

</div>


<div id="dlg_csv" class="easyui-dialog" title="Export CSV" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_csv" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Export CSV</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account No</span>
                <input style="width:60%;" name="payroll_bank_id" id="payroll_bank_id" required="" class="easyui-combogrid">
            </div>
            
        </fieldset>
        <div style="text-align:right; padding-top:10px;">
            <a href="javascript:void(0)"
               class="easyui-linkbutton"
               iconCls="icon-file"
               onclick="exportCSV()">
                Export CSV
            </a>
        </div>
    </form>
</div>


<!-- PDF -->

<iframe id="printout" src="<?= base_url('payroll/payroll_harian_lepas/print') ?>" style="width: 100%;" hidden></iframe>



<script>

    function formula(){

        Swal.fire({

            width: 600,

            html: `<div style="text-align:left;"><center><b style="font-size:16px !important;">Perhitungan Payroll</b><hr></center>

                <b>Ketentuan Allowence</b>

                <ul>

                    <li>Jika Calculate Daily (YES) dan Type (TEMPORARY) Maka (Amount * Working Day)</li>

                    <li>Jika Calculate Daily (NO) Maka (Amount)</li>

                </ul>

                <b>Ketentuan BPJS Employee</b>

                <ul>

                    <li>Jika BPJS TK dan JKN tidak di isi maka tidak ada perhitungan</li>

                    <li>Jika JKN di isi dan Setup Salary (bpjs) = 0 maka (Salary + Allowence x BPJS (% Employee) x Employee List (JKN Family))</li>

                    <li>Jika JKN di isi dan Setup Salary (bpjs) > 0 maka (Salary (bpjs) x BPJS (% Employee) x Employee List (JKN Family))</li>

                    <li>Jika BPJS TK di isi maka (Salary + Allowence x BPJS (% Employee))</li>

                </ul>

                <b>Ketentuan BPJS Company</b>

                <ul>

                    <li>Jika Gaji lebih besar dari Maximal Gaji BPJS maka yang di ambil Max Gaji BPJS untuk perhitungan</li>

                    <li>Jika BPJS TK dan JKN tidak di isi maka tidak ada perhitungan</li>

                    <li>Jika JKN di isi dan Setup Salary (bpjs) = 0 maka (Salary + Allowence x BPJS (% Company) x Employee List (JKN Family))</li>

                    <li>Jika JKN di isi dan Setup Salary (bpjs) > 0 maka (Salary (bpjs) x BPJS (% Company))</li>

                    <li>Jika BPJS TK di isi maka (Salary + Allowence x BPJS (% Company))</li>

                </ul>

                <b>Ketentuan Potongan Permit (Deduction)</b>

                <ul>

                    <li>Jika Group (Magang) maka (Salary / HKW) x Total Permit</li>

                    <li>Jika Group (Karyawan) maka (Salary + Allowence / HKW) * Total Permit</li>

                </ul>

                <b>Ketentuan Potongan Absensi</b>

                <ul>

                    <li>Jika Group (Magang) maka (Salary / HKW) x Total tidak masuk kerja</li>

                    <li>Jika Group (Karyawan) maka (Salary + Allowence / HKW) x Total tidak masuk kerja</li>

                </ul>

                <b>Rumus Bruto Income</b>

                <ul>

                    <li>Salary + Allowence + BPJS (Company) + Correction Plus</li>

                </ul>

                <b>Rumus Income</b>

                <ul>

                    <li>Bruto Income - Deduction - BPJS (Company)</li>

                </ul>

                <b>Rumus TER</b>

                <ul>

                    <li>Bruto Income x (% Range TER Category pada Bruto Income)</li>

                </ul>

                <b>Rumus Net Income</b>

                <ul>

                    <li>Income - TER - Deduction - Loan - BPJS (Employee)</li>

                </ul>`,

            showClass: {

                popup: `animate__animated animate__fadeInUp animate__faster`

            },

            hideClass: {

                popup: `animate__animated animate__fadeOutDown animate__faster`

            }

        });

    }

    

    function add() {

        var filter_from = $("#filter_from").combogrid('getValue');

        var filter_to = $("#filter_to").textbox('getValue');

        var filter_lock = $("#filter_lock").numberbox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_employee = $("#filter_employee").combogrid('getValue');

        // var filter_employee_type = $("#filter_employee_type").combobox('getValue');

        // var filter_group = $("#filter_group").combobox('getValue');



        if (filter_from == "" || filter_to == "") {

            toastr.warning("Please Enter Cut Off Period", "Filter Date");

        } else if(filter_lock == 1){

            Swal.fire(

                'Locked Payroll',

                'Payroll is locked for this period, if you want to repeat generate payroll please confirmation to your Manager',

                'error'

            );

        } else {

            $.messager.confirm('Warning', 'Are you sure you want to generate payroll?', function(x) {

                if (x) {

                    $.ajax({

                        url: "<?= base_url('payroll/payroll_harian_lepas/read') ?>",

                        type: 'get',

                        data: 'filter_from=' + filter_from,

                        success: function(message) {

                            var message_read = eval('(' + message + ')');



                            if (message_read['status'] != "1") {

                                if (message_read['generate'] == "EXIST") {

                                    $.messager.confirm('Warning', 'Are you sure you want to repeat generate data?', function(r) {

                                        if (r) {

                                            $.ajax({

                                                method: 'post',

                                                url: '<?= base_url('payroll/payroll_harian_lepas/delete') ?>',

                                                data: {

                                                    filter_from: filter_from,

                                                    filter_to: filter_to,

                                                    filter_division: filter_division,

                                                    filter_departement: filter_departement,

                                                    filter_departement_sub: filter_departement_sub,

                                                    filter_employee: filter_employee

                                                    // filter_employee_type: filter_employee_type,

                                                    // filter_group: filter_group

                                                },

                                                success: function(deleted) {

                                                    Swal.fire({

                                                        title: 'Please Wait for Generating Data',

                                                        showConfirmButton: false,

                                                        allowOutsideClick: false,

                                                        allowEscapeKey: false,

                                                        didOpen: () => {

                                                            Swal.showLoading();

                                                        },

                                                    });



                                                    var url = "filter_division=" + filter_division +

                                                        "&filter_departement=" + filter_departement +

                                                        "&filter_departement_sub=" + filter_departement_sub +

                                                        '&filter_from=' + filter_from +

                                                        '&filter_to=' + filter_to +

                                                        // '&filter_employee_type=' + filter_employee_type +

                                                        // '&filter_group=' + filter_group +

                                                        '&filter_employee=' + filter_employee;

                                                    $.ajax({

                                                        url: "<?= base_url('payroll/payroll_harian_lepas/generatePayroll') ?>",

                                                        type: 'get',

                                                        data: url,

                                                        success: function(result) {

                                                            $('#dlg_generate').dialog('open');

                                                            Swal.close();

                                                            var json = eval('(' + result + ')');
                                                            requestData(json.total, json);



                                                            function requestData(total, json, number = 1, value = 0) {

                                                                if (value < 100) {

                                                                    value = Math.floor((number / total) * 100);

                                                                    $('#p_upload').progressbar('setValue', value);

                                                                    $('#p_start').html(number);

                                                                    $('#p_finish').html(total);



                                                                    $.ajax({

                                                                        url: "<?= base_url('payroll/payroll_harian_lepas/create') ?>?" + url,

                                                                        type: 'post',

                                                                        data: json['rows'][number - 1],

                                                                        success: function(note) {

                                                                            var result = eval('(' + note + ')');

                                                                            if (result.theme == "success") {

                                                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                                            } else {

                                                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                                            }

                                                                            $("#p_remarks").append(title + "<br>");

                                                                            requestData(total, json, number + 1, value);



                                                                            if (number == total) {

                                                                                $('#dlg_generate').dialog('close');

                                                                                Swal.fire(

                                                                                    'Generate Completed',

                                                                                    'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',

                                                                                    'success'

                                                                                );

                                                                            }

                                                                        },

                                                                        fail: function(jqXHR, textStatus) {

                                                                            if (textStatus == "error") {

                                                                                Swal.fire({

                                                                                    title: 'Connection Time Out, Check Your Connection',

                                                                                    showConfirmButton: false,

                                                                                    allowOutsideClick: false,

                                                                                    allowEscapeKey: false,

                                                                                    didOpen: () => {

                                                                                        Swal.showLoading();

                                                                                    },

                                                                                });



                                                                                setTimeout(function() {

                                                                                    requestData(total, json, number, value);

                                                                                }, 5000);

                                                                            }

                                                                        }

                                                                    });

                                                                }

                                                            }

                                                        }

                                                    });

                                                },

                                            });

                                        }

                                    });

                                } else {

                                    Swal.fire({

                                        title: 'Please Wait for Generating Data',

                                        showConfirmButton: false,

                                        allowOutsideClick: false,

                                        allowEscapeKey: false,

                                        didOpen: () => {

                                            Swal.showLoading();

                                        },

                                    });



                                    var url = "filter_division=" + filter_division +

                                        "&filter_departement=" + filter_departement +

                                        "&filter_departement_sub=" + filter_departement_sub +

                                        '&filter_from=' + filter_from +

                                        '&filter_to=' + filter_to +

                                        // '&filter_employee_type=' + filter_employee_type +

                                        // '&filter_group=' + filter_group +

                                        '&filter_employee=' + filter_employee;

                                    $.ajax({

                                        url: "<?= base_url('payroll/payroll_harian_lepas/generatePayroll') ?>",

                                        type: 'get',

                                        data: url,

                                        success: function(result) {

                                            $('#dlg_generate').dialog('open');

                                            Swal.close();

                                            var json = eval('(' + result + ')');
                                            requestData(json.total, json);



                                            function requestData(total, json, number = 1, value = 0) {

                                                if (value < 100) {

                                                    value = Math.floor((number / total) * 100);

                                                    $('#p_upload').progressbar('setValue', value);

                                                    $('#p_start').html(number);

                                                    $('#p_finish').html(total);



                                                    // Validate that the row data exists
                                                    if (!json.rows || !json.rows[number - 1]) {
                                                        console.error('Row data missing for index:', number - 1);
                                                        console.error('Available rows:', json.rows);
                                                        var errorTitle = "<b style='color: red;'>Error</b> | Missing data for employee " + number + " of " + total;
                                                        $("#p_remarks").append(errorTitle + "<br>");
                                                        // Continue with next record
                                                        requestData(total, json, number + 1, value);
                                                        return;
                                                    }

                                                    $.ajax({

                                                        url: "<?= base_url('payroll/payroll_harian_lepas/create') ?>?" + url,

                                                        type: 'post',

                                                        data: json['rows'][number - 1],

                                                        success: function(note) {

                                                            var result = eval('(' + note + ')');

                                                            if (result.theme == "success") {

                                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                            } else {

                                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                            }

                                                            $("#p_remarks").append(title + "<br>");

                                                            requestData(total, json, number + 1, value);



                                                            if (number == total) {

                                                                $('#dlg_generate').dialog('close');

                                                                Swal.fire(

                                                                    'Generate Completed',

                                                                    'Generate payroll has been completed, please check the generated payroll data, if an error occurs, please generate it again before the data is approved',

                                                                    'success'

                                                                );

                                                            }

                                                        },

                                                        error: function(xhr, status, error) {

                                                            console.error('AJAX Error:', status, error);

                                                            console.error('Response:', xhr.responseText);

                                                            console.error('Data being sent:', json['rows'][number - 1]);

                                                            console.error('Current number:', number, 'Total:', total);
                                                            
                                                            // Show error in UI

                                                            var errorTitle = "<b style='color: red;'>Error</b> | Failed to process employee " + number + " of " + total;

                                                            $("#p_remarks").append(errorTitle + "<br>");
                                                            
                                                            // Continue with next record

                                                            requestData(total, json, number + 1, value);
                                                        },

                                                        fail: function(jqXHR, textStatus) {

                                                            if (textStatus == "error") {

                                                                Swal.fire({

                                                                    title: 'Connection Time Out, Check Your Connection',

                                                                    showConfirmButton: false,

                                                                    allowOutsideClick: false,

                                                                    allowEscapeKey: false,

                                                                    didOpen: () => {

                                                                        Swal.showLoading();

                                                                    },

                                                                });



                                                                setTimeout(function() {

                                                                    requestData(total, json, number, value);

                                                                }, 5000);

                                                            }

                                                        }

                                                    });

                                                }

                                            }

                                        }

                                    });

                                }

                            } else {

                                Swal.fire(

                                    'Cannot Generate!',

                                    'the payroll data that you entered has been approved, please contact the relevant admin if you want to repeat generate payroll again',

                                    'warning'

                                );

                            }

                        }

                    });

                }

            });

        }

    }



    function filter() {

        var filter_from = $("#filter_from").combogrid('getValue');

        var filter_to = $("#filter_to").textbox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_employee = $("#filter_employee").combogrid('getValue');

        // var filter_employee_type = $("#filter_employee_type").combobox('getValue');

        // var filter_group = $("#filter_group").combobox('getValue');

        var filter_status = $("#filter_status").combobox('getValue');



        var url = "?filter_from=" + filter_from +

            "&filter_to=" + filter_to +

            "&filter_division=" + filter_division +

            "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub +

            "&filter_employee=" + filter_employee +

            // "&filter_employee_type=" + filter_employee_type +

            "&filter_status=" + filter_status;

            // "&filter_group=" + filter_group;



        if (filter_from == "" || filter_to == "") {

            toastr.warning("Please Enter Cut Off Period");

        } else {



            $('#dg').datagrid({

                url: '<?= base_url('payroll/payroll_harian_lepas/datatables') ?>' + url

            });



            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

            $("#printout").attr('src', '<?= base_url('payroll/payroll_harian_lepas/print') ?>' + url);

        }

    }



    //PRINT PDF

    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }

    //PRINT EXCEL

    function excel() {

        var filter_from = $("#filter_from").combogrid('getValue');

        var filter_to = $("#filter_to").textbox('getValue');

        var filter_division = $("#filter_division").combobox('getValue');

        var filter_departement = $("#filter_departement").combobox('getValue');

        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');

        var filter_employee = $("#filter_employee").combogrid('getValue');

        // var filter_employee_type = $("#filter_employee_type").combobox('getValue');

        // var filter_group = $("#filter_group").combobox('getValue');

        var filter_status = $("#filter_status").combobox('getValue');



        var url = "?filter_from=" + filter_from +

            "&filter_to=" + filter_to +

            "&filter_division=" + filter_division +

            "&filter_departement=" + filter_departement +

            "&filter_departement_sub=" + filter_departement_sub +

            "&filter_employee=" + filter_employee +

            // "&filter_employee_type=" + filter_employee_type +

            "&filter_status=" + filter_status;

            // "&filter_group=" + filter_group;



        if (filter_from == "" || filter_to == "") {

            toastr.warning("Please Enter Cut Off Period");

        } else {

            window.location.assign('<?= base_url('payroll/payroll_harian_lepas/print/excel') ?>' + url);

        }

    }

    //RELOAD

    function reload() {

        window.location.reload();

    }

    //DELETE DATA BY ID

    function deleted() {

        var rows = $('#dg').datagrid('getSelections');

        if (rows.length > 0) {

            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {

                if (r) {

                    for (var i = 0; i < rows.length; i++) {

                        var row = rows[i];

                        $.ajax({

                            method: 'post',

                            url: '<?= base_url('payroll/payroll_harian_lepas/delete_id') ?>',

                            data: {

                                id: row.id

                            },

                            dataType: 'json',

                            success: function(result) {

                                console.log('Response:', result);

                                if (result.status === 'success') {

                                    if (typeof toastr !== 'undefined') {

                                        toastr.success(result.message, 'Success');

                                    } else {

                                        alert('Success: ' + result.message);

                                    }

                                } else {

                                    if (typeof toastr !== 'undefined') {

                                        toastr.error(result.message, 'Error');

                                    } else {

                                        alert('Error: ' + result.message);

                                    }

                                }

                            },

                            error: function(jqXHR, textStatus, errorThrown) {

                                console.error('Error:', jqXHR.responseText);

                                if (typeof toastr !== 'undefined') {

                                    toastr.error(jqXHR.statusText);

                                } else {

                                    alert('Error: ' + jqXHR.statusText);

                                }

                            },

                            complete: function(data) {

                                $('#dg').datagrid('reload');

                            }

                        });

                    }

                }

            });

        } else {

            if (typeof toastr !== 'undefined') {

                toastr.warning("Please select one of the data in the table first!", "Information");

            } else {

                alert("Please select one of the data in the table first!");

            }

        }

    }



    $(function() {

        $("#add").html("Generate Payroll");

        $('#dg').datagrid({

            // url: '<?= base_url('payroll/payroll_harian_lepas/datatables') ?>',

            pagination: true,

            rownumbers: true,

            fit: true,

            pageList: [20, 50, 100, 500, 1000],

            pageSize: 20,

        });



        //Filter Cutoff

        $('#filter_from').combogrid({

            url: '<?= base_url('payroll/cutoff/reads') ?>',

            panelWidth: 300,

            idField: 'start',

            textField: 'start',

            mode: 'remote',

            fitColumns: true,

            prompt: 'Date From',

            columns: [

                [{

                    field: 'start',

                    title: 'Date From',

                    width: 120

                }, {

                    field: 'finish',

                    title: 'Date To',

                    width: 120

                }]

            ],

            onSelect: function(val, row) {

                $("#filter_to").textbox('setValue', row.finish);

                $("#filter_lock").numberbox('setValue', row.payroll);

            }

        });



        //Get Departement

        $('#filter_division').combobox({

            url: '<?php echo base_url('employee/divisions/reads'); ?>',

            valueField: 'id',

            textField: 'name',

            prompt: 'Choose All',

            icons: [{

                iconCls: 'icon-clear',

                handler: function(e) {

                    $(e.data.target).combobox('clear').combobox('textbox').focus();

                }

            }],

            onSelect: function(division) {

                $('#filter_departement').combobox({

                    url: '<?php echo base_url('employee/departements/reads'); ?>?division_id=' + division.id,

                    valueField: 'id',

                    textField: 'name',

                    prompt: 'Choose All',

                    icons: [{

                        iconCls: 'icon-clear',

                        handler: function(e) {

                            $(e.data.target).combobox('clear').combobox('textbox').focus();

                        }

                    }],

                    onSelect: function(departement) {

                        $('#filter_departement_sub').combobox({

                            url: '<?php echo base_url('employee/departement_subs/reads'); ?>?division_id=' + division.id + '&departement_id=' + departement.id,

                            valueField: 'id',

                            textField: 'name',

                            prompt: 'Choose All',

                            icons: [{

                                iconCls: 'icon-clear',

                                handler: function(e) {

                                    $(e.data.target).combobox('clear').combobox('textbox').focus();

                                }

                            }],

                            onSelect: function(departement_sub) {

                                $('#filter_employee').combogrid({

                                    url: '<?= base_url('employee/employee_harian_lepas/reads?departement_sub_id=') ?>' + departement_sub.id,

                                    panelWidth: 450,

                                    idField: 'id',

                                    textField: 'name',

                                    mode: 'remote',

                                    fitColumns: true,

                                    prompt: 'Choose All',

                                    icons: [{

                                        iconCls: 'icon-clear',

                                        handler: function(e) {

                                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();

                                        }

                                    }],

                                    columns: [

                                        [{

                                            field: 'number',

                                            title: 'Employee ID',

                                            width: 120

                                        }, {

                                            field: 'name',

                                            title: 'Employee Name',

                                            width: 200

                                        }]

                                    ],

                                });

                            }

                        });

                    }

                });

            }

        });



        $('#filter_employee').combogrid({

            url: '<?= base_url('employee/employee_harian_lepas/reads') ?>',

            panelWidth: 450,

            idField: 'id',

            textField: 'name',

            mode: 'remote',

            fitColumns: true,

            prompt: 'Choose All',

            icons: [{

                iconCls: 'icon-clear',

                handler: function(e) {

                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();

                }

            }],

            columns: [

                [{

                    field: 'number',

                    title: 'Employee ID',

                    width: 120

                }, {

                    field: 'name',

                    title: 'Employee Name',

                    width: 200

                }]

            ],

        });



        // $("#filter_employee_type").combobox({

        //     url: '<?= base_url('employee/contracts/reads') ?>',

        //     valueField: 'id',

        //     textField: 'name',

        //     prompt: "Choose All",

        //     icons: [{

        //         iconCls: 'icon-clear',

        //         handler: function(e) {

        //             $(e.data.target).combobox('clear').combobox('textbox').focus();

        //         }

        //     }],

        // });



        // $("#filter_group").combobox({

        //     url: '<?= base_url('admin/privilege_groups/reads') ?>',

        //     valueField: 'id',

        //     textField: 'name',

        //     prompt: "Choose All",

        //     icons: [{

        //         iconCls: 'icon-clear',

        //         handler: function(e) {

        //             $(e.data.target).combobox('clear').combobox('textbox').focus();

        //         }

        //     }],

        // });

    });



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



    function numberformat(value, row) {

        const formatter = new Intl.NumberFormat('id-ID');



        return "<b>" + formatter.format(value) + "</b>";

    }



    // function numberformatDecimal(value, row) {

    //     var digits = 2;

    //     var currency = 'IDR';

    //     var format = "id-ID";



    //     if (value != null) {

    //         const formatter = new Intl.NumberFormat(format, {

    //             style: 'currency',

    //             currency: currency,

    //             minimumFractionDigits: digits

    //         });

    //         return "<b>" + formatter.format(value) + "</b>";

    //     }

    // }



    function statusResign(value, row){

        if(value == null){

            return "ACTIVE";

        }else{

            return "RESIGN";

        }

    }



    function styleStatusResign(value, row, index) {

        if(value == null){

            return 'background: #53D636; color:white;';

        }else{

            return 'background: #FFC41B; color:white;';

        }

    }

    function excelReport() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        // var filter_employee_type = $("#filter_employee_type").combobox('getValue');
        // var filter_group = $("#filter_group").combobox('getValues');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from
            + "&filter_to=" + filter_to
            + "&filter_division=" + filter_division
            + "&filter_departement=" + filter_departement
            + "&filter_departement_sub=" + filter_departement_sub
            + "&filter_employee=" + filter_employee
            // + "&filter_employee_type=" + filter_employee_type
            + "&filter_status=" + filter_status
            // + "&filter_group=" + filter_group
        ;

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {
            window.location.assign('<?= base_url('payroll/payroll_harian_lepas/excelReport') ?>' + url);
        }
    }

    function printcsv() {
        let filter_from = $("#filter_from").combogrid('getValue');
        let filter_to = $("#filter_to").textbox('getValue');

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Enter Cut Off Period");
        } else {
            $('#dlg_csv').dialog('open');    
    
            $('#payroll_bank_id').combogrid({
                url: "<?= base_url('payroll/payroll_account/datatables') ?>",
                panelWidth: 600,
                idField: 'id',
                textField: 'accoun',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Account No',
                columns: [
                    [{
                        field: 'bank',
                        title: 'Bank',
                        width: 200
                    }, {
                        field: 'account',
                        title: 'Account No',
                        width: 200
                    }]
                ],
            });
        }
    }

    function exportCSV() {
        var filter_from = $("#filter_from").combogrid('getValue');
        var filter_to = $("#filter_to").textbox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_departement = $("#filter_departement").combobox('getValue');
        var filter_departement_sub = $("#filter_departement_sub").combobox('getValue');
        var filter_employee = $("#filter_employee").combogrid('getValue');
        // var filter_employee_type = $("#filter_employee_type").combobox('getValue');
        // var filter_group = $("#filter_group").combobox('getValues');
        var filter_status = $("#filter_status").combobox('getValue');
        var payroll_bank_id = $("#payroll_bank_id").combobox('getValue');

        var url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_division=" + filter_division +
            "&filter_departement=" + filter_departement +
            "&filter_departement_sub=" + filter_departement_sub +
            "&filter_employee=" + filter_employee +
            // "&filter_employee_type=" + filter_employee_type +
            "&filter_status=" + filter_status +
            // "&filter_group=" + filter_group +
            "&payroll_bank_id=" + payroll_bank_id;

        if (payroll_bank_id == "") {
            toastr.warning("Please Enter Account No");
        } else {
            window.location.assign('<?= base_url('payroll/payroll_harian_lepas/printcsv') ?>' + url);
        }
    }

</script>