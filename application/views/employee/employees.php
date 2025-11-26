<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'image_profile',width:35,halign:'center',formatter:FormatterImage">Foto</th>
            <th rowspan="2" data-options="field:'print',width:75,align:'center', formatter:btnPrint">Print Detail</th>
            <th rowspan="2" data-options="field:'details',width:50,halign:'center',formatter:FormatterDetail">Detail</th>
            <th rowspan="2" data-options="field:'id',width:50,halign:'center',formatter:FormatterCard">ID Card</th>
            <th rowspan="2" data-options="field:'number',width:120,halign:'center'">Employee ID</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Fullname</th>
            <th rowspan="2" data-options="field:'division_name',width:150,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'departement_name',width:200,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'departement_sub_name',width:200,halign:'center'">Departement Sub</th>
            <th rowspan="2" data-options="field:'position_name',width:100,halign:'center'">Position</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Job Type</th>
            <th rowspan="2" data-options="field:'contract_name',width:100,halign:'center'">Employee Type</th>
            <th rowspan="2" data-options="field:'shift_name',width:100,halign:'center'">Shift Type</th>
            <th rowspan="2" data-options="field:'date_sign',width:100,halign:'center',formatter:FormatterDate">Join Date</th>
            <th rowspan="2" data-options="field:'date_expired',width:100,halign:'center',formatter:FormatterDate">Contract<br>Expired</th>
            <th rowspan="2" data-options="field:'resign_date',width:100,halign:'center',formatter:FormatterDate">Resign Date</th>
            <th rowspan="2" data-options="field:'service',width:180,halign:'center'">Fit Of Service</th>
            <th rowspan="2" data-options="field:'bank_name',width:150,halign:'center'">Bank Name</th>
            <th rowspan="2" data-options="field:'bank_no',width:120,halign:'center'">Bank Account</th>
            <th colspan="3">Approval</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:100,align:'center',styler:statusStyler, formatter:statusFormatter"> Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'"> By</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
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
            <fieldset style="width: 99.8%; border:2px solid #d0d0d0; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width:33%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Division</span>
                        <input style="width:60%;" id="filter_divisions" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement</span>
                        <input style="width:60%;" id="filter_departements" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Departement Sub</span>
                        <input style="width:60%;" id="filter_departement_subs" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Employee Name</span>
                        <input style="width:60%;" id="filter_employees" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width:33%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Position</span>
                        <input style="width:60%;" id="filter_positions" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Employee Type</span>
                        <input style="width:60%;" id="filter_contracts" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Group</span>
                        <input style="width:60%;" id="filter_groups" class="easyui-combogrid">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Religion</span>
                        <input style="width:60%;" id="filter_religions" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Martial Status</span>
                        <input style="width:60%;" id="filter_maritals" class="easyui-combogrid">
                    </div>
                </div>
                <div style="width:33%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Join Date</span>
                        <input style="width:28%;" id="filter_from_join" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                        <input style="width:28%;" id="filter_to_join" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Shift Type</span>
                        <input style="width:60%;" id="filter_shift" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Fit For Service</span>
                        <select style="width:60%;" id="filter_services" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="1">Less than 1 Year</option>
                            <option value="2">1 - 2 Year</option>
                            <option value="5">2 - 5 Year</option>
                            <option value="8">5 - 8 Year</option>
                            <option value="10">8 - 10 Year</option>
                            <option value="11">More than 10 Year</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Contract Expired</span>
                        <select style="width:60%;" id="filter_expired" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="">Choose All</option>
                            <option value="0">Yesterday</option>
                            <option value="1">Today</option>
                            <option value="2">Tomorrow</option>
                            <option value="3">1 Weeks</option>
                            <option value="4">1 Months</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Status</span>
                        <select style="width:30%;" id="filter_status" class="easyui-combobox" data-options="panelHeight:'auto'">
                            <option value="0">Active</option>
                            <option value="1">Not Active</option>
                        </select>
                        
                        &nbsp; &nbsp; <input class="easyui-checkbox" id="filter_resign" value="1"> &nbsp; Resign
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 80%; height: 550px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Basic Information</b></legend>
            <div id="div_first" style="width: 50%; float: left;">
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Registered Before</span>
                    <input class="easyui-radiobutton" name="regis_bf" id="regis_bf_yes">
                    <input class="easyui-radiobutton" name="regis_bf" id="regis_bf_no">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;" id="label_number">Employee ID</span>
                    <input style="width:30%;" name="number" id="number" required="" class="easyui-textbox">
                    <a class="easyui-linkbutton" onclick="generate_id()"><i class="fa fa-rotate-left"></i></a>
                </div>
                <div class="fitem" id="div_emp_old">
                    <span style="width:35%; display:inline-block;">Employee ID Old</span>
                    <input style="width:60%;" id="emp_old" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Candidate ID</span>
                    <input style="width:60%;" name="candidate_id" id="candidate_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fullname</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Nickname</span>
                    <input style="width:60%;" name="nickname" id="nickname" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sign Date</span>
                    <input style="width:60%;" name="date_sign" id="date_sign" required="" data-options="{onSelect: onChangeDate, formatter: myformatter, parser: myparser, editable: false}" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fit For Services</span>
                    <input style="width:60%;" name="service" disabled id="service" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Contract Expired</span>
                    <input style="width:60%;" name="date_expired" id="date_expired" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                </div>
                <div class="fitem" id="div_division">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division_id" id="division_id" required="" class="easyui-combobox">
                </div>
            </div>
            <div id="div_second" style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <input style="width:60%;" name="departement_id" id="departement_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement Sub</span>
                    <input style="width:40%;" name="departement_sub_id" id="departement_sub_id" required="" class="easyui-combobox">
                    <select style="width:20%;" name="job_type" id="job_type" required="" class="easyui-combobox" panelHeight="auto">
                        <option value="DIRECT">DIRECT</option>
                        <option value="INDIRECT">INDIRECT</option>
                        <option value="ADM & GENERAL">ADM & GENERAL</option>
                        <option value="OTHER">OTHER</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Employee Type</span>
                    <input style="width:60%;" name="contract_id" id="contract_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Position</span>
                    <input style="width:60%;" name="position_id" id="position_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Group</span>
                    <input style="width:60%;" name="group_id" id="group_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Source</span>
                    <input style="width:60%;" name="source_id" id="source_id" class="easyui-combobox">
                </div>
                <!-- <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Employee Status</span>
                    <input class="easyui-radiobutton status_active" name="status" checked="true" value="0"> Active &nbsp;
                    <input class="easyui-radiobutton status_notactive" name="status" value="1"> Not Active
                </div>
                <div class="fitem" id="form_status_date">
                    <span style="width:35%; display:inline-block;">Inactive Date</span>
                    <input style="width:60%;" name="status_date" id="status_date" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                </div> -->
            </div>
        </fieldset>

        <div class="easyui-tabs" style="width:100%; float: left;">
            <div title="Personal Data" style="padding: 10px;">
                <fieldset style="width:50%; border:1px solid #d0d0d0; border-radius:4px; float: left;">
                    <legend style="font-weight: bold;">Personal Data</legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Address</span>
                        <input style="width:60%; height: 50px;" name="address" multiline="true" id="address" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Place of Birth</span>
                        <input style="width:60%;" name="place_birth" id="place_birth" class="easyui-textbox">
                    </div>
                    <div class="fitem" style="margin-bottom: 5px;">
                        <span style="width:35%; display:inline-block;">Birth Date</span>
                        <input style="width:60%;" name="birthday" id="birthday" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem" style="margin-bottom: 5px;">
                        <span style="width:35%; display:inline-block;">Gender</span>
                        <input class="easyui-radiobutton" name="gender" id="gender_male" checked="true" value="MALE"> Male &nbsp;
                        <input class="easyui-radiobutton" name="gender" id="gender_female" value="FEMALE"> Female
                    </div>
                    <div class="fitem" style="margin-bottom: 5px;">
                        <span style="width:35%; display:inline-block;">Blood Type</span>
                        <input class="easyui-radiobutton" name="blood" id="blood" checked="true" value="-"> - &nbsp;
                        <input class="easyui-radiobutton" name="blood" id="blood_a" value="A"> A &nbsp;
                        <input class="easyui-radiobutton" name="blood" id="blood_b" value="B"> B &nbsp;
                        <input class="easyui-radiobutton" name="blood" id="blood_ab" value="AB"> AB &nbsp;
                        <input class="easyui-radiobutton" name="blood" id="blood_o" value="O"> O &nbsp;
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Religion</span>
                        <input style="width:60%;" name="religion_id" panelHeight="auto" id="religion_id" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Marital Status</span>
                        <input style="width:60%;" name="marital_id" id="marital_id" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">National ID No</span>
                        <input style="width:60%;" name="national_id" id="national_id" required="" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Tax No (NPWP)</span>
                        <input style="width:60%;" name="tax_id" id="tax_id" required="" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">BPJS TK</span>
                        <input style="width:35%;" name="jamsostek" id="jamsostek" class="easyui-textbox">
                        <input style="width:20%;" name="jamsostek_date" id="jamsostek_date" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">JKN</span>
                        <input style="width:35%;" name="jkn" id="jkn" class="easyui-textbox">
                        <input style="width:20%;" name="jkn_date" id="jkn_date" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Number of Family (JKN) </span>
                        <select style="width:35%;" name="jkn_family" id="jkn_family" class="easyui-combobox" panelHeight="true">
                            <option value="1">1 (One)</option>
                            <option value="2">2 (Two)</option>
                            <option value="3">3 (Three)</option>
                            <option value="4">4 (Four)</option>
                            <option value="5">5 (Five)</option>
                            <option value="6">6 (Six)</option>
                        </select>
                    </div>
                </fieldset>
                <fieldset style="width:49%; border:1px solid #d0d0d0; border-radius:4px; float: left;">
                    <legend style="font-weight: bold;">Contact Information</legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Telp No</span>
                        <input style="width:60%;" name="telphone" id="telphone" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Phone No</span>
                        <input style="width:60%;" name="mobile_phone" data-options="buttonText:'+62', buttonAlign:'left'" id="mobile_phone" required="" class="easyui-numberbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Emergency No</span>
                        <input style="width:60%;" name="emergency_no" data-options="buttonText:'+62', buttonAlign:'left'" id="emergency_no" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Email</span>
                        <input style="width:60%;" name="email" id="email" required="" class="easyui-textbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Driving Licensed</span>
                        <input style="width:35%;" name="driving_no" id="driving_no" class="easyui-textbox">
                        <input style="width:20%;" name="driving_date" id="driving_date" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">STNK No</span>
                        <input style="width:35%;" name="stnk_no" id="stnk_no" class="easyui-textbox">
                        <input style="width:20%;" name="stnk_date" id="stnk_date" data-options="formatter:myformatter,parser:myparser, editable: false" class="easyui-datebox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Foto ID</span>
                        <input style="width:60%;" name="image_id" accept=".jpg, .png, .jpeg" id="image_id" class="easyui-filebox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Foto Profile</span>
                        <input style="width:60%;" name="image_profile" accept=".jpg, .png, .jpeg" id="image_profile" class="easyui-filebox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Google Maps Point</span>
                        <input style="width:60%; height: 115px;" name="maps" multiline="true" id="maps" class="easyui-textbox">
                    </div>
                </fieldset>
            </div>

            <!-- Family Information -->
            <div title="Family">
                <div id="toolbarFamily">
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="addFamily()"><i class="fa fa-plus"></i> Add</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeFamily()"><i class="fa fa-times"></i> Remove</a>
                </div>

                <table id="dgFamily" class="easyui-datagrid" style="width:100%;" toolbar="#toolbarFamily"></table>
            </div>

            <!-- Education Information -->
            <div title="Education">
                <div id="toolbarEducation">
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="addEducation()"><i class="fa fa-plus"></i> Add</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeEducation()"><i class="fa fa-times"></i> Remove</a>
                </div>

                <table id="dgEducation" class="easyui-datagrid" style="width:100%;" toolbar="#toolbarEducation"></table>
            </div>

            <!-- Working Experience -->
            <div title="Experience">
                <div id="toolbarExperience">
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="addExperience()"><i class="fa fa-plus"></i> Add</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeExperience()"><i class="fa fa-times"></i> Remove</a>
                </div>

                <table id="dgExperience" class="easyui-datagrid" style="width:100%;" toolbar="#toolbarExperience"></table>
            </div>

            <!-- Training Experience -->
            <div title="Training">
                <div id="toolbarTraining">
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="addTraining()"><i class="fa fa-plus"></i> Add</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeTraining()"><i class="fa fa-times"></i> Remove</a>
                </div>

                <table id="dgTraining" class="easyui-datagrid" style="width:100%;" toolbar="#toolbarTraining"></table>
            </div>

            <!-- Carrer Information -->
            <div title="Carrer">
                <div id="toolbarCarrer">
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="addCarrer()"><i class="fa fa-plus"></i> Add</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeCarrer()"><i class="fa fa-times"></i> Remove</a>
                </div>

                <table id="dgCarrer" class="easyui-datagrid" style="width:100%;" toolbar="#toolbarCarrer"></table>
            </div>

            <!-- Bank Information -->
            <div title="Bank" style="padding:10px">
                <fieldset style="width:50%; border:1px solid #d0d0d0; border-radius:4px; float: left;">
                    <legend style="font-weight: bold;">Bank Information</legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Branch Name </span>
                        <select style="width:60%;" name="bank_name" id="bank_name" required class="easyui-combobox">
                            <option value="-">Cash</option>
                            <option value="Bank Mandiri">Bank Mandiri</option>
                            <option value="Bank Syariah Indonesia">Bank Syariah Indonesia</option>
                            <option value="Bank Central Asia">Bank Central Asia</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Bank No</span>
                        <input style="width:60%;" name="bank_no" id="bank_no" required class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Bank Branch </span>
                        <input style="width:60%;" name="bank_branch" id="bank_branch" required class="easyui-textbox">
                    </div>
                </fieldset>
            </div>
        </div>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 90%; padding:10px; top: 20px;">
    <div style="width: 100%;">
        <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:20%; display:inline-block;">File Upload</span>
                    <input name="file_excel" style="width: 75%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:20%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="uploadData()"><i class="fa fa-upload"></i> Upload Data</a>
                </div>
            </fieldset>
        </form>
        <div style="width: 34%; float: left;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>History Upload Employee</b></legend>
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
                <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">

                </div>
            </fieldset>
        </div>
        <div style="width: 33%; float: left;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>History Upload Family</b></legend>
                <div id="p_upload_1" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_1">0</b> Of <b id="p_finish_1">0</b></center>
                <div id="p_remarks_1" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">

                </div>
            </fieldset>
        </div>
        <div style="width: 33%; float: left;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>History Upload Education</b></legend>
                <div id="p_upload_2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_2">0</b> Of <b id="p_finish_2">0</b></center>
                <div id="p_remarks_2" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">

                </div>
            </fieldset>
        </div>
        <div style="width: 34%; float: left;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>History Upload Experience</b></legend>
                <div id="p_upload_3" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_3">0</b> Of <b id="p_finish_3">0</b></center>
                <div id="p_remarks_3" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">

                </div>
            </fieldset>
        </div>
        <div style="width: 33%; float: left;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>History Upload Training</b></legend>
                <div id="p_upload_4" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_4">0</b> Of <b id="p_finish_4">0</b></center>
                <div id="p_remarks_4" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">

                </div>
            </fieldset>
        </div>
        <div style="width: 33%; float: left;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>History Upload Carrer</b></legend>
                <div id="p_upload_5" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_5">0</b> Of <b id="p_finish_5">0</b></center>
                <div id="p_remarks_5" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">

                </div>
            </fieldset>
        </div>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<iframe id="printidcard" src="" style="width: 100%;" hidden></iframe>

<script>
    var url_division = '<?= base_url('employee/divisions/reads') ?>';
    var url_departement = '<?= base_url('employee/departements/reads') ?>';
    var url_departement_sub = '<?= base_url('employee/departement_subs/reads') ?>';
    var url_source = '<?= base_url('employee/sources/reads') ?>';
    var url_contract = '<?php echo base_url('employee/employees/readContracts'); ?>'
    var url_position = '<?= base_url('employee/employees/readPositions') ?>'
    var url_group = '<?= base_url('employee/employees/readGroups') ?>';

    //GENERATE ID
    function generate_id() {
        var departement_id = $("#departement_id").combobox('getValue');
        var contract_id = $("#contract_id").combobox('getValue');
        var source_id = $("#source_id").combobox('getValue');
        var date_sign = $("#date_sign").datebox('getValue');

        if (departement_id == "" || contract_id == "" || date_sign == "") {
            toastr.info("Please Choose Sign Date, Departement and Employee Type");
        } else {
            $.ajax({
                type: "post",
                url: "<?= base_url('employee/employees/readNumber') ?>",
                data: "departement_id=" + departement_id + "&contract_id=" + contract_id + "&source_id=" + source_id + "&date_sign=" + btoa(date_sign),
                dataType: "json",
                success: function(number) {
                    $("#number").textbox("setValue", number.id);
                }
            });
        }
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');

        url_save = '<?= base_url('employee/employees/create') ?>';
        $("#number").textbox("enable");
        $("#candidate_id").combogrid("enable");
        $("#date_expired").datebox("enable");
        $("#division_id").combobox("enable");
        $("#departement_id").combobox("enable");
        $("#departement_sub_id").combobox("enable");
        $("#contract_id").combobox("enable");
        $("#position_id").combogrid("enable");
        $("#group_id").combobox("enable");
        $("#blood").radiobutton({checked: true});
        $("#gender_male").radiobutton({checked: true});
        // $(".status_active").radiobutton({checked: true});
        $("#regis_bf_no").radiobutton({checked: true});
        $("#jkn_family").combobox("setValue", "1");
        $("#form_status_date").hide();

        tableFamily();
        tableEducation();
        tableExperience();
        tableTraining();
        tableCarrer();
        regiteredBFNo();
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.status_check == "" || row.status_check == null) {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                url_save = '<?= base_url('employee/employees/update') ?>?id=' + btoa(row.id);

                tableFamily(row.number);
                tableEducation(row.number);
                tableExperience(row.number);
                tableTraining(row.number);
                tableCarrer(row.number);
                regiteredBFNo();

                $("#number").textbox("disable");
                $("#candidate_id").combogrid("disable");
                $("#date_expired").datebox("disable");
                $("#division_id").combobox("disable");
                $("#departement_id").combobox("disable");
                $("#departement_sub_id").combobox("disable");
                $("#contract_id").combobox("disable");
                $("#position_id").combogrid("disable");
                $("#group_id").combobox("disable");

                $("#division_id").combobox('setValue', row.division_id);
                $("#departement_id").combobox('setValue', row.departement_id);
                $("#departement_sub_id").combobox('setValue', row.departement_sub_id);
            } else {
                toastr.error("Approval still Checked");
            }
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
                            url: '<?= base_url('employee/employees/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                if (result.theme == "success") {
                                    toastr.success(result.message, result.title);
                                } else {
                                    toastr.error(result.message, result.title);
                                }
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

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function uploadData() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('employee/employees/upload') ?>',
            onSubmit: function() {
                if ($(this).form('validate') == false) {
                    return $(this).form('validate');
                } else {
                    $.messager.progress({
                        title: 'Please Wait',
                        msg: 'Importing Excel to Database'
                    });
                }
            },
            success: function(result) {
                $.messager.progress('close');

                var json = eval('(' + result + ')');
                if (json.total == 0) {
                    uploadFamily();
                } else {
                    requestData(json.total, json);
                }

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload').progressbar('setValue', value);
                        $('#p_start').html(number);
                        $('#p_finish').html(total);

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('employee/employees/uploadCreate') ?>",
                            data: {
                                "data": json[number - 1]
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                    requestData(total, json, number + 1, value);
                                } else {
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                    requestData(total, json, number + 1, value);
                                }
                                $("#p_remarks").append(title + "<br>");
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
                                        requestData(total, json, number, value, success + 0, failed + 0);
                                    }, 5000);
                                }
                            }
                        });
                    } else {
                        uploadFamily();
                    }
                }
            }
        });
    }

    function uploadFamily() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('employee/employees/uploadFamily') ?>',
            onSubmit: function() {
                if ($(this).form('validate') == false) {
                    return $(this).form('validate');
                } else {
                    $.messager.progress({
                        title: 'Please Wait',
                        msg: 'Importing Excel to Database'
                    });
                }
            },
            success: function(result) {
                $.messager.progress('close');

                var json = eval('(' + result + ')');
                if (json.total == 0) {
                    uploadEducation();
                } else {
                    requestDataFamily(json.total, json);
                }

                function requestDataFamily(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload_1').progressbar('setValue', value);
                        $('#p_start_1').html(number);
                        $('#p_finish_1').html(total);

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('employee/employees/uploadCreateFamily') ?>",
                            data: {
                                "data": json[number - 1]
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                    requestDataFamily(total, json, number + 1, value);
                                } else {
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                    requestDataFamily(total, json, number + 1, value);
                                }
                                $("#p_remarks_1").append(title + "<br>");
                            }
                        });
                    } else {
                        uploadEducation();
                    }
                }
            }
        });
    }

    function uploadEducation() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('employee/employees/uploadEducation') ?>',
            onSubmit: function() {
                if ($(this).form('validate') == false) {
                    return $(this).form('validate');
                } else {
                    $.messager.progress({
                        title: 'Please Wait',
                        msg: 'Importing Excel to Database'
                    });
                }
            },
            success: function(result) {
                $.messager.progress('close');

                var json = eval('(' + result + ')');
                if (json.total == 0) {
                    uploadExperience();
                } else {
                    requestDataEducation(json.total, json);
                }

                function requestDataEducation(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload_2').progressbar('setValue', value);
                        $('#p_start_2').html(number);
                        $('#p_finish_2').html(total);

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('employee/employees/uploadCreateEducation') ?>",
                            data: {
                                "data": json[number - 1]
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                    requestDataEducation(total, json, number + 1, value);
                                } else {
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                    requestDataEducation(total, json, number + 1, value);
                                }
                                $("#p_remarks_2").append(title + "<br>");
                            }
                        });
                    } else {
                        uploadExperience();
                    }
                }
            }
        });
    }

    function uploadExperience() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('employee/employees/uploadExperience') ?>',
            onSubmit: function() {
                if ($(this).form('validate') == false) {
                    return $(this).form('validate');
                } else {
                    $.messager.progress({
                        title: 'Please Wait',
                        msg: 'Importing Excel to Database'
                    });
                }
            },
            success: function(result) {
                $.messager.progress('close');

                var json = eval('(' + result + ')');
                if (json.total == 0) {
                    uploadTraining();
                } else {
                    requestDataExperience(json.total, json);
                }

                function requestDataExperience(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload_3').progressbar('setValue', value);
                        $('#p_start_3').html(number);
                        $('#p_finish_3').html(total);

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('employee/employees/uploadCreateExperience') ?>",
                            data: {
                                "data": json[number - 1]
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                    requestDataExperience(total, json, number + 1, value);
                                } else {
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                    requestDataExperience(total, json, number + 1, value);
                                }
                                $("#p_remarks_3").append(title + "<br>");
                            }
                        });
                    } else {
                        uploadTraining();
                    }
                }
            }
        });
    }

    function uploadTraining() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('employee/employees/uploadTraining') ?>',
            onSubmit: function() {
                if ($(this).form('validate') == false) {
                    return $(this).form('validate');
                } else {
                    $.messager.progress({
                        title: 'Please Wait',
                        msg: 'Importing Excel to Database'
                    });
                }
            },
            success: function(result) {
                $.messager.progress('close');

                var json = eval('(' + result + ')');
                if (json.total == 0) {
                    uploadCarrer();
                } else {
                    requestDataTraining(json.total, json);
                }

                function requestDataTraining(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload_4').progressbar('setValue', value);
                        $('#p_start_4').html(number);
                        $('#p_finish_4').html(total);

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('employee/employees/uploadCreateTraining') ?>",
                            data: {
                                "data": json[number - 1]
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                    requestDataTraining(total, json, number + 1, value);
                                } else {
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                    requestDataTraining(total, json, number + 1, value);
                                }
                                $("#p_remarks_4").append(title + "<br>");
                            }
                        });
                    } else {
                        uploadCarrer();
                    }
                }
            }
        });
    }

    function uploadCarrer() {
        $('#frm_upload').form('submit', {
            url: '<?= base_url('employee/employees/uploadCarrer') ?>',
            onSubmit: function() {
                if ($(this).form('validate') == false) {
                    return $(this).form('validate');
                } else {
                    $.messager.progress({
                        title: 'Please Wait',
                        msg: 'Importing Excel to Database'
                    });
                }
            },
            success: function(result) {
                $.messager.progress('close');

                var json = eval('(' + result + ')');
                requestDataCarrer(json.total, json);

                function requestDataCarrer(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_upload_4').progressbar('setValue', value);
                        $('#p_start_4').html(number);
                        $('#p_finish_4').html(total);

                        $.ajax({
                            type: "POST",
                            async: true,
                            url: "<?= base_url('employee/employees/uploadCreateCarrer') ?>",
                            data: {
                                "data": json[number - 1]
                            },
                            cache: false,
                            dataType: "json",
                            success: function(result) {
                                if (result.theme == "success") {
                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                    requestDataCarrer(total, json, number + 1, value);
                                } else {
                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                    requestDataCarrer(total, json, number + 1, value);
                                }
                                $("#p_remarks_4").append(title + "<br>");
                            }
                        });
                    }
                }
            }
        });
    }

    //DOWNLOAD EXCEL
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_employee.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_divisions = $("#filter_divisions").combobox('getValue');
        var filter_departements = $("#filter_departements").combobox('getValue');
        var filter_departement_subs = $("#filter_departement_subs").combobox('getValue');
        var filter_employees = $("#filter_employees").combogrid('getValue');
        var filter_positions = $("#filter_positions").combogrid('getValue');
        var filter_contracts = $("#filter_contracts").combobox('getValue');
        var filter_groups = $("#filter_groups").combobox('getValue');
        var filter_religions = $("#filter_religions").combobox('getValue');
        var filter_maritals = $("#filter_maritals").combogrid('getValue');
        var filter_services = $("#filter_services").combobox('getValue');
        var filter_expired = $("#filter_expired").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_resign = $("#filter_resign").checkbox('options');
        var filter_from_join = $("#filter_from_join").datebox('getValue');
        var filter_to_join = $("#filter_to_join").datebox('getValue');

        if(filter_resign.checked == true){
            var resign = "1";
        }else{
            var resign = "0";
        }

        var url = "?filter_divisions=" + filter_divisions +
            "&filter_departements=" + filter_departements +
            "&filter_departement_subs=" + filter_departement_subs +
            "&filter_employees=" + filter_employees +
            "&filter_positions=" + filter_positions +
            "&filter_contracts=" + filter_contracts +
            "&filter_groups=" + filter_groups +
            "&filter_religions=" + filter_religions +
            "&filter_maritals=" + filter_maritals +
            "&filter_services=" + filter_services +
            "&filter_expired=" + filter_expired +
            "&filter_shift=" + filter_shift +
            "&filter_status=" + filter_status +
            "&filter_from_join=" + btoa(filter_from_join) +
            "&filter_to_join=" + btoa(filter_to_join) +
            "&filter_resign=" + resign;

        $('#dg').datagrid({
            url: '<?= base_url('employee/employees/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('employee/employees/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_divisions = $("#filter_divisions").combobox('getValue');
        var filter_departements = $("#filter_departements").combobox('getValue');
        var filter_departement_subs = $("#filter_departement_subs").combobox('getValue');
        var filter_employees = $("#filter_employees").combogrid('getValue');
        var filter_positions = $("#filter_positions").combogrid('getValue');
        var filter_contracts = $("#filter_contracts").combobox('getValue');
        var filter_groups = $("#filter_groups").combobox('getValue');
        var filter_religions = $("#filter_religions").combobox('getValue');
        var filter_maritals = $("#filter_maritals").combogrid('getValue');
        var filter_services = $("#filter_services").combobox('getValue');
        var filter_expired = $("#filter_expired").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_resign = $("#filter_resign").checkbox('options');
        var filter_from_join = $("#filter_from_join").datebox('getValue');
        var filter_to_join = $("#filter_to_join").datebox('getValue');

        if(filter_resign.checked == true){
            var resign = "1";
        }else{
            var resign = "0";
        }

        var url = "?filter_divisions=" + filter_divisions +
            "&filter_departements=" + filter_departements +
            "&filter_departement_subs=" + filter_departement_subs +
            "&filter_employees=" + filter_employees +
            "&filter_positions=" + filter_positions +
            "&filter_contracts=" + filter_contracts +
            "&filter_groups=" + filter_groups +
            "&filter_religions=" + filter_religions +
            "&filter_maritals=" + filter_maritals +
            "&filter_services=" + filter_services +
            "&filter_expired=" + filter_expired +
            "&filter_shift=" + filter_shift +
            "&filter_status=" + filter_status +
            "&filter_from_join=" + btoa(filter_from_join) +
            "&filter_to_join=" + btoa(filter_to_join) +
            "&filter_resign=" + resign;

        window.location.assign('<?= base_url('employee/employees/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    function tableFamily(number) {
        var lastIndex;
        var dgFamily = $('#dgFamily').datagrid({
            url: '<?= base_url('employee/employees/readFamilys/') ?>' + number,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Family ID",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                    }
                },{
                    field: 'name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Family Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'place',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Place of Birth",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'birthday',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Birthday",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'relation',
                    width: 150,
                    halign: 'center',
                    title: "Relation",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'profesion',
                    width: 150,
                    halign: 'center',
                    title: "Profesion",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'contact',
                    width: 150,
                    halign: 'center',
                    title: "Contact",
                    editor: {
                        type: 'numberbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dgFamily').datagrid('getEditors', rowIndex);
            },
            onClickCell: onClickCellFamily,
        });
    }

    var editIndexFamily = undefined;

    function endEditingFamily() {
        if (editIndexFamily == undefined) {
            return true
        }
        if ($('#dgFamily').datagrid('validateRow', editIndexFamily)) {
            $('#dgFamily').datagrid('endEdit', editIndexFamily);
            editIndexFamily = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellFamily(index, field) {
        if (editIndexFamily != index) {
            if (endEditingFamily()) {
                $('#dgFamily').datagrid('selectRow', index).datagrid('beginEdit', index);
                var ed = $('#dgFamily').datagrid('getEditor', {
                    index: index,
                    field: field
                });
                if (ed) {
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndexFamily = index;
            } else {
                setTimeout(function() {
                    $('#dgFamily').datagrid('selectRow', editIndexFamily);
                }, 0);
            }
        }
    }

    function addFamily() {
        if (endEditingFamily()) {
            $('#dgFamily').datagrid('appendRow', {
                qty: '0'
            });
            editIndexFamily = $('#dgFamily').datagrid('getRows').length - 1;
            $('#dgFamily').datagrid('selectRow', editIndexFamily).datagrid('beginEdit', editIndexFamily);
        }
    }

    function removeFamily(target) {
        var dg = $('#dgFamily');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexFamily,
            field: 'id'
        });

        $.ajax({
            method: 'post',
            url: "<?= base_url('employee/employees/deleteFamily') ?>",
            data: {
                id: row.id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //toastr.error(jqXHR.statusText);
            },
        });

        $('#dgFamily').datagrid('cancelEdit', editIndexFamily).datagrid('deleteRow', editIndexFamily);
        editIndexFamily = undefined;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    function tableEducation(number) {
        var lastIndex;
        var dgEducation = $('#dgEducation').datagrid({
            url: '<?= base_url('employee/employees/readEducation/') ?>' + number,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Education ID",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                    }
                },{
                    field: 'level',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Education Level",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'degree',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Degree",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'school',
                    width: 150,
                    halign: 'center',
                    title: "School/University",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'start',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Start",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'end',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "End",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'qpa',
                    width: 80,
                    halign: 'center',
                    title: "QPA",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dgEducation').datagrid('getEditors', rowIndex);
            },
            onClickCell: onClickCellEducation,
        });
    }

    var editIndexEducation = undefined;

    function endEditingEducation() {
        if (editIndexEducation == undefined) {
            return true
        }
        if ($('#dgEducation').datagrid('validateRow', editIndexEducation)) {
            $('#dgEducation').datagrid('endEdit', editIndexEducation);
            editIndexEducation = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellEducation(index, field) {
        if (editIndexEducation != index) {
            if (endEditingEducation()) {
                $('#dgEducation').datagrid('selectRow', index).datagrid('beginEdit', index);
                var ed = $('#dgEducation').datagrid('getEditor', {
                    index: index,
                    field: field
                });
                if (ed) {
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndexEducation = index;
            } else {
                setTimeout(function() {
                    $('#dgEducation').datagrid('selectRow', editIndexEducation);
                }, 0);
            }
        }
    }

    function addEducation() {
        if (endEditingEducation()) {
            $('#dgEducation').datagrid('appendRow', {
                qty: '0'
            });
            editIndexEducation = $('#dgEducation').datagrid('getRows').length - 1;
            $('#dgEducation').datagrid('selectRow', editIndexEducation).datagrid('beginEdit', editIndexEducation);
        }
    }

    function removeEducation() {
        var dg = $('#dgEducation');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexEducation,
            field: 'id'
        });

        $.ajax({
            method: 'post',
            url: "<?= base_url('employee/employees/deleteEducation') ?>",
            data: {
                id: row.id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //toastr.error(jqXHR.statusText);
            },
        });

        $('#dgEducation').datagrid('cancelEdit', editIndexEducation).datagrid('deleteRow', editIndexEducation);
        editIndexEducation = undefined;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    function tableExperience(number) {
        var lastIndex;
        var dgExperience = $('#dgExperience').datagrid({
            url: '<?= base_url('employee/employees/readExperience/') ?>' + number,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Experience ID",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                    }
                },{
                    field: 'name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Company Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'type',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Industries Type",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'start',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Start",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'end',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "End",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'position',
                    width: 150,
                    halign: 'center',
                    title: "Position",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'salary',
                    width: 150,
                    halign: 'center',
                    title: "Salary",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dgExperience').datagrid('getEditors', rowIndex);
            },
            onClickCell: onClickCellExperience,
        });
    }

    var editIndexExperience = undefined;

    function endEditingExperience() {
        if (editIndexExperience == undefined) {
            return true
        }
        if ($('#dgExperience').datagrid('validateRow', editIndexExperience)) {
            $('#dgExperience').datagrid('endEdit', editIndexExperience);
            editIndexExperience = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellExperience(index, field) {
        if (editIndexExperience != index) {
            if (endEditingExperience()) {
                $('#dgExperience').datagrid('selectRow', index).datagrid('beginEdit', index);
                var ed = $('#dgExperience').datagrid('getEditor', {
                    index: index,
                    field: field
                });
                if (ed) {
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndexExperience = index;
            } else {
                setTimeout(function() {
                    $('#dgExperience').datagrid('selectRow', editIndexExperience);
                }, 0);
            }
        }
    }

    function addExperience() {
        if (endEditingExperience()) {
            $('#dgExperience').datagrid('appendRow', {
                qty: '0'
            });
            editIndexExperience = $('#dgExperience').datagrid('getRows').length - 1;
            $('#dgExperience').datagrid('selectRow', editIndexExperience).datagrid('beginEdit', editIndexExperience);
        }
    }

    function removeExperience() {
        var dg = $('#dgExperience');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexExperience,
            field: 'id'
        });

        $.ajax({
            method: 'post',
            url: "<?= base_url('employee/employees/deleteExperience') ?>",
            data: {
                id: row.id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //toastr.error(jqXHR.statusText);
            },
        });

        $('#dgExperience').datagrid('cancelEdit', editIndexExperience).datagrid('deleteRow', editIndexExperience);
        editIndexExperience = undefined;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    function tableTraining(number) {
        var lastIndex;
        var dgTraining = $('#dgTraining').datagrid({
            url: '<?= base_url('employee/employees/readTraining/') ?>' + number,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Training ID",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                    }
                },{
                    field: 'name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Training Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'description',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'start',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Date",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'profesion',
                    width: 150,
                    halign: 'center',
                    title: "Profession",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'contact',
                    width: 150,
                    halign: 'center',
                    title: "Contact",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dgTraining').datagrid('getEditors', rowIndex);
            },
            onClickCell: onClickCellTraining,
        });
    }

    var editIndexTraining = undefined;

    function endEditingTraining() {
        if (editIndexTraining == undefined) {
            return true
        }
        if ($('#dgTraining').datagrid('validateRow', editIndexTraining)) {
            $('#dgTraining').datagrid('endEdit', editIndexTraining);
            editIndexTraining = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellTraining(index, field) {
        if (editIndexTraining != index) {
            if (endEditingTraining()) {
                $('#dgTraining').datagrid('selectRow', index).datagrid('beginEdit', index);
                var ed = $('#dgTraining').datagrid('getEditor', {
                    index: index,
                    field: field
                });
                if (ed) {
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndexTraining = index;
            } else {
                setTimeout(function() {
                    $('#dgTraining').datagrid('selectRow', editIndexTraining);
                }, 0);
            }
        }
    }

    function addTraining() {
        if (endEditingTraining()) {
            $('#dgTraining').datagrid('appendRow', {
                qty: '0'
            });
            editIndexTraining = $('#dgTraining').datagrid('getRows').length - 1;
            $('#dgTraining').datagrid('selectRow', editIndexTraining).datagrid('beginEdit', editIndexTraining);
        }
    }

    function removeTraining() {
        var dg = $('#dgTraining');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexTraining,
            field: 'id'
        });

        $.ajax({
            method: 'post',
            url: "<?= base_url('employee/employees/deleteTraining') ?>",
            data: {
                id: row.id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //toastr.error(jqXHR.statusText);
            },
        });

        $('#dgTraining').datagrid('cancelEdit', editIndexTraining).datagrid('deleteRow', editIndexTraining);
        editIndexTraining = undefined;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    function tableCarrer(number) {
        var lastIndex;
        var dgCarrer = $('#dgCarrer').datagrid({
            url: '<?= base_url('employee/employees/readCarrer/') ?>' + number,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Carreer ID",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                    }
                },{
                    field: 'name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Carrer Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'description',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Description",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'start',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Date",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'profesion',
                    width: 150,
                    halign: 'center',
                    title: "Profession",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'contact',
                    width: 150,
                    halign: 'center',
                    title: "Contact",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dgCarrer').datagrid('getEditors', rowIndex);
            },
            onClickCell: onClickCellCarrer,
        });
    }

    var editIndexCarrer = undefined;

    function endEditingCarrer() {
        if (editIndexCarrer == undefined) {
            return true
        }
        if ($('#dgCarrer').datagrid('validateRow', editIndexCarrer)) {
            $('#dgCarrer').datagrid('endEdit', editIndexCarrer);
            editIndexCarrer = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCellCarrer(index, field) {
        if (editIndexCarrer != index) {
            if (endEditingCarrer()) {
                $('#dgCarrer').datagrid('selectRow', index).datagrid('beginEdit', index);
                var ed = $('#dgCarrer').datagrid('getEditor', {
                    index: index,
                    field: field
                });
                if (ed) {
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndexCarrer = index;
            } else {
                setTimeout(function() {
                    $('#dgCarrer').datagrid('selectRow', editIndexCarrer);
                }, 0);
            }
        }
    }

    function addCarrer() {
        if (endEditingCarrer()) {
            $('#dgCarrer').datagrid('appendRow', {
                qty: '0'
            });
            editIndexCarrer = $('#dgCarrer').datagrid('getRows').length - 1;
            $('#dgCarrer').datagrid('selectRow', editIndexCarrer).datagrid('beginEdit', editIndexCarrer);
        }
    }

    function removeCarrer() {
        var dg = $('#dgCarrer');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndexCarrer,
            field: 'id'
        });

        $.ajax({
            method: 'post',
            url: "<?= base_url('employee/employees/deleteCarrer') ?>",
            data: {
                id: row.id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //toastr.error(jqXHR.statusText);
            },
        });

        $('#dgCarrer').datagrid('cancelEdit', editIndexCarrer).datagrid('deleteRow', editIndexCarrer);
        editIndexCarrer = undefined;
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('employee/employees/datatables') ?>',
            pagination: true,
            rownumbers: true,
            pageList: [20, 50, 100, 500, 1000],
            fit: true,
            pageSize: 20,
            rowStyler: function(index, row) {
                if (row.status == 1) {
                    return 'background-color:#FFDCDC;';
                }else if(row.resign_date != null){
                    return 'background-color:#FFD44D;';
                }
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() { return $(this).form('validate'); },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                var number = $('#number').textbox('getValue');

                                $('#dgFamily').datagrid('acceptChanges');
                                var rowFamily = $('#dgFamily').datagrid('getRows');
                                var totalFamily = rowFamily.length;
                                endEditingFamily();
                                for (let i = 0; i < totalFamily; i++) {
                                    if (rowFamily[i].name) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('employee/employees/createFamily') ?>',
                                            data: {
                                                number: number,
                                                id: rowFamily[i].id,
                                                name: rowFamily[i].name,
                                                place: rowFamily[i].place,
                                                birthday: rowFamily[i].birthday,
                                                relation: rowFamily[i].relation,
                                                profesion: rowFamily[i].profesion,
                                                contact: rowFamily[i].contact
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                //
                                            }
                                        });
                                    }
                                }

                                $('#dgEducation').datagrid('acceptChanges');
                                var rowEducation = $('#dgEducation').datagrid('getRows');
                                var totalEducation = rowEducation.length;
                                endEditingEducation();
                                for (let i = 0; i < totalEducation; i++) {
                                    // if (rowEducation[i].level) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('employee/employees/createEducation') ?>',
                                            data: {
                                                number: number,
                                                id: rowEducation[i].id,
                                                level: rowEducation[i].level,
                                                degree: rowEducation[i].degree,
                                                school: rowEducation[i].school,
                                                start: rowEducation[i].start,
                                                end: rowEducation[i].end,
                                                qpa: rowEducation[i].qpa
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                //
                                            }
                                        });
                                    // }
                                }

                                $('#dgExperience').datagrid('acceptChanges');
                                var rowExperience = $('#dgExperience').datagrid('getRows');
                                var totalExperience = rowExperience.length;
                                endEditingExperience();
                                for (let i = 0; i < totalExperience; i++) {
                                    if (rowExperience[i].name) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('employee/employees/createExperience') ?>',
                                            data: {
                                                number: number,
                                                id: rowExperience[i].id,
                                                name: rowExperience[i].name,
                                                type: rowExperience[i].type,
                                                start: rowExperience[i].start,
                                                end: rowExperience[i].end,
                                                position: rowExperience[i].position,
                                                salary: rowExperience[i].salary
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                //
                                            }
                                        });
                                    }
                                }

                                $('#dgTraining').datagrid('acceptChanges');
                                var rowTraining = $('#dgTraining').datagrid('getRows');
                                var totalTraining = rowTraining.length;
                                endEditingTraining();
                                for (let i = 0; i < totalTraining; i++) {
                                    if (rowTraining[i].name) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('employee/employees/createTraining') ?>',
                                            data: {
                                                number: number,
                                                id: rowTraining[i].id,
                                                name: rowTraining[i].name,
                                                description: rowTraining[i].description,
                                                start: rowTraining[i].start,
                                                profesion: rowTraining[i].profesion,
                                                contact: rowTraining[i].contact
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                //
                                            }
                                        });
                                    }
                                }

                                $('#dgCarrer').datagrid('acceptChanges');
                                var rowCarrer = $('#dgCarrer').datagrid('getRows');
                                var totalCarrer = rowCarrer.length;
                                endEditingCarrer();
                                for (let i = 0; i < totalCarrer; i++) {
                                    if (rowCarrer[i].name) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('employee/employees/createCarrer') ?>',
                                            data: {
                                                number: number,
                                                id: rowCarrer[i].id,
                                                name: rowCarrer[i].name,
                                                description: rowCarrer[i].description,
                                                start: rowCarrer[i].start,
                                                profesion: rowCarrer[i].profesion,
                                                contact: rowCarrer[i].contact
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                //
                                            }
                                        });
                                    }
                                }

                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        //Get Departement
        $('#filter_divisions').combobox({
            url: url_division,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }],
            onSelect: function(division) { $('#filter_departements').combobox('reload', url_departement+'?division_id='+division.id); }
        });

        $('#filter_departements').combobox({
            url: url_departement,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }],
            onSelect: function(departement) { $('#filter_departement_subs').combobox('reload', url_departement_sub
                +'?division_id='+$('#filter_divisions').combobox('getValue')
                +'&departement_id='+departement.id
            ); }
        });

        $('#filter_departement_subs').combobox({
            url: url_departement_sub,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });

        $('#filter_contracts').combobox({
            url: url_contract+'?regis_no=1',
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

        $('#filter_groups').combobox({
            url: url_group+'?regis_no=1',
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

        $('#filter_maritals').combogrid({
            url: '<?= base_url('employee/maritals/reads') ?>',
            panelWidth: 300,
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
                    title: 'Code',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Marital Name',
                    width: 200
                }]
            ],
        });

        $('#filter_employees').combogrid({
            url: '<?= base_url('employee/employees/readEmployees') ?>',
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

        $('#filter_positions').combogrid({
            url: url_position+'?regis_no=1',
            panelWidth: 300,
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
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'level',
                    title: 'Level',
                    width: 80
                }]
            ]
        });

        $('#filter_religions').combobox({
            url: '<?php echo base_url('employee/religions/reads'); ?>',
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

        //--------------------------------------------------------------------------------------------------------------------------------------------------------------
        //Get Candidate
        $('#candidate_id').combogrid({
            url: '<?= base_url('requitment/candidates/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'name',
            prompt: 'Select Candidate',
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'email',
                    title: 'Email',
                    width: 200
                }, {
                    field: 'mobile_phone',
                    title: 'Phone No',
                    width: 100
                }]
            ],
            onSelect: function(value, row) {
                $("#name").textbox('setValue', row.name);
                $("#nickname").textbox('setValue', row.nickname);
                $("#division_id").combobox('setValue', row.division_id);
                $("#departement_id").combobox('setValue', row.departement_id);
                $("#departement_sub_id").combobox('setValue', row.departement_sub_id);
                $("#address").textbox('setValue', row.address);
                $("#place_birth").textbox('setValue', row.place_birth);
                $("#birthday").datebox('setValue', row.birthday);
                $("#religion_id").combobox('setValue', row.religion_id);
                $("#marital_id").combogrid('setValue', row.marital_id);
                $("#national_id").textbox('setValue', row.national_id);
                $("#tax_id").textbox('setValue', row.tax_id);
                $("#telphone").numberbox('setValue', row.telphone);
                $("#mobile_phone").numberbox('setValue', row.mobile_phone);
                $("#email").textbox('setValue', row.email);
            }
        });

        //Get Departement
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
            prompt: 'Choose Department Sub',
            onSelect: function(deptsub) { $('#job_type').combobox('setValue', deptsub.type); }
        });

        $('#contract_id').combobox({
            url: url_contract,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Employee Type',
        });

        $('#position_id').combogrid({
            url: url_position,
            panelWidth: 300,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Position',
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'level',
                    title: 'Level',
                    width: 80
                }]
            ]
        });

        $('#group_id').combobox({
            url: url_group,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Group',
            onSelect: function(group) { $('#source_id').combobox('reload', url_source+'/'+group.id); }
        });

        $('#source_id').combobox({
            url: url_source,
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Source'
        });

        $('#marital_id').combogrid({
            url: '<?= base_url('employee/maritals/reads') ?>',
            panelWidth: 300,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Marital Status',
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Marital Name',
                    width: 200
                }]
            ],
        });

        $('#religion_id').combobox({
            url: '<?php echo base_url('employee/religions/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Religion'
        });

        $("#regis_bf_yes").radiobutton({
            label: 'Yes',
            value: 'Yes',
            labelPosition: 'after',
            onChange: function (checked) {
                if (checked) {
                    $('#div_division').prependTo('#div_second');
                    $('#label_number').text('Employee ID New');
                    $('#div_emp_old').show();
                    $('#emp_old').combogrid('options').required = true;
                    $('#emp_old').combogrid('textbox').validatebox('options').required = true;
                    $('#emp_old').combogrid('textbox').validatebox('validate');
                    $('#contract_id').combobox('reload', url_contract);
                    $('#position_id').combogrid('grid').datagrid('reload', url_position);
                    $('#group_id').combobox('reload', url_group);
                }
            }
        });

        $("#regis_bf_no").radiobutton({
            checked: true,
            label: 'No',
            value: 'No',
            labelPosition: 'after',
            onChange: function (checked) { if (checked) {
                $('#emp_old').combogrid('options').required = false;
                $('#emp_old').combogrid('textbox').validatebox('options').required = false;
                $('#emp_old').combogrid('textbox').validatebox('validate');
                regiteredBFNo();
            } }
        });

        $('#emp_old').combogrid({
            url: '<?= base_url('employee/employees/readEmployeeOld') ?>',
            mode: 'remote',
            fitColumns: true,
            idField: 'number',
            textField: 'number',
            prompt: 'Choose Employee ID Old',
            columns: [[
                {field: 'number', title: 'Employee ID', halign: 'center'},
                {field: 'name', title: 'Employee Name', halign: 'center'}
            ]],
            onSelect: function (index, row) {
                $('#name').textbox('setValue', row.name);
                $('#date_sign').datebox('setValue', row.sign);
                $('#date_expired').datebox('setValue', row.expired);
                $('#division_id').combobox('setValue', row.division);
                $('#departement_id').combobox('setValue', row.dept);
                $('#departement_sub_id').combobox('setValue', row.sub_dept);
                $('#contract_id').combobox('setValue', row.contract);
                $('#position_id').combogrid('setValue', row.position);
                $('#group_id').combobox('setValue', row.group_id);
                $('#source_id').combobox('setValue', row.source);

                $('#address').textbox('setValue', row.address);
                $('#place_birth').textbox('setValue', row.place_birth);
                $('#birthday').datebox('setValue', row.birthday);
                $('#gender_'+row.gender.toLowerCase()).radiobutton({checked: true});
                $('#blood_'+((row.blood == '') ? '-' : row.blood.toLowerCase())).radiobutton({checked: true});
                $('#religion_id').combobox('setValue', row.religion_id);
                $('#marital_id').combogrid('setValue', row.marital_id);
                $('#national_id').textbox('setValue', row.national_id);
                $('#tax_id').textbox('setValue', row.tax_id);
                $('#jamsostek').textbox('setValue', row.jamsostek);
                $('#jamsostek_date').datebox('setValue', row.jamsostek_date);
                $('#jkn').textbox('setValue', row.jkn);
                $('#jkn_date').datebox('setValue', row.jkn_date);
                $('#jkn_family').combobox('setValue', row.jkn_family);
                $('#telphone').numberbox('setValue', row.telphone);
                $('#mobile_phone').numberbox('setValue', row.mobile_phone);
                $('#email').textbox('setValue', row.email);

                $('#bank_name').combobox('setValue', row.bank_name);
                $('#bank_no').textbox('setValue', row.bank_no);
                $('#bank_branch').textbox('setValue', row.bank_branch);

                tableFamily(row.number);
                tableEducation(row.number);
                tableExperience(row.number);
                tableTraining(row.number);
                tableCarrer(row.number);
                onChangeDate(new Date(row.sign));
            }
        });

        $("#form_status_date").hide();

        // $('.status_active').radiobutton({
            // onChange: function(val) {
            //     if (val === false) {
            //         $("#form_status_date").show();
            //     } else {
            //         $("#form_status_date").hide();
            //     }
            // }
        // });
    });

    function regiteredBFNo() {
        $('#contract_id').combobox('reload', url_contract+'?regis_no=1');
        $('#position_id').combogrid('grid').datagrid('reload', url_position+'?regis_no=1');
        $('#group_id').combobox('reload', url_group+'?regis_no=1');
        $('#div_division').appendTo('#div_first');
        $('#label_number').text('Employee ID');
        $('#div_emp_old').hide();
    }

    function onChangeDate(value) {
        $.post(
            '<?= base_url('employee/employees/readService') ?>',
            { date: myformatter(value) },
            function(e) { $('#service').textbox('setValue', e); }
        );
    }

    function FormatterImage(val) {
        if (val == "" || val == null) {
            return '<img style="width: 100%;" src="<?= base_url('assets/image/users/default.png'); ?>" />';
        } else {
            return '<img style="width: 100%;" src="' + val + '" />';
        }
    };

    function FormatterDetail(val, row) {
        return '<a class="button-green" style="width:100%;" href="<?= base_url('employee/employees/details/') ?>' + row.id + '/<?= $id_menu ?>"><i class="fa fa-user"></i></a>';
    };

    function FormatterCard(value, row, index) {
        return '<a class="btn btn-info w-100" style="width: 100%; pointer-events: visible; opacity: 1;" href="#" onClick="print_employee_card('+"'"+window.btoa(row.id)+"'"+')"><i class="fa fa-id-card-clip"></i></a>';
    }

    function btnPrint(val, row) {
        var print = "print_employee_detail('" + row.number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_employee_detail(employee_number) {
        window.open("<?= base_url('employee/employees/print_employee_detail/') ?>" + window.btoa(employee_number), "_blank", "width=1200,height=600");
    }

    function print_employee_card(id) {
        $("#printidcard").attr("src", "<?= base_url('employee/employees/cards/') ?>"+id);
        setTimeout(function () { $("#printidcard").attr("src", ""); }, 1000);
    }

    function FormatterDate(val) {
        if (val == "0000-00-00") {
            return '-';
        } else {
            return val;
        }
    };

    //CELLSTYLE STATUS
    function statusStyler(value, row, index) {
        if (value == "" || value == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function statusFormatter(value) {
        if (value == "" || value == null) {
            return 'Approved';
        } else {
            return 'Checked';
        }
    };

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