<table id="dg" class="easyui-datagrid" style="width:100%;">
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Training Name</span>
                <input style="width:60%;" id="filter_training_name" class="easyui-combogrid" data-options="
                        url: '<?= base_url('lnd/master_form_test/readsTrainings') ?>',
                        idField: 'id',
                        textField: 'name', 
                        mode: 'remote',
                        fitColumns: true,
                        panelWidth: 500,
                        prompt: 'Select Training Name',
                        columns: [[
                            {field:'name',title:'Training Name',width:200},
                            {field:'induction',title:'Induction',width:200}
                    ]]">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement</span>
                <input style="width:60%;" id="filter_department" class="easyui-combobox" data-options="
                            url: '<?= base_url('employee/departements/reads') ?>',
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Select Departement',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }]
                        ">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 750px; height:600px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate style="padding:10px">
        <div id="form_group">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Training Name</span>
                    <input style="width:60%;" id="training_name" name="training_name" required="" class="easyui-combogrid"
                    data-options="
                        url: '<?= base_url('lnd/master_form_test/readsTrainings') ?>',
                        idField: 'id',
                        textField: 'name', 
                        mode: 'remote',
                        fitColumns: true,
                        panelWidth: 500,
                        columns: [[
                            {field:'name',title:'Training Name',width:200},
                            {field:'induction',title:'Induction',width:200}
                    ]]">
                </div>
                <div class="fitem" style="display: flex">
                    <span style="width:35%; display:inline-block;">Department</span>
                    <div style="width: 60%; margin-left: 2px">
                        <input style="width:100%;" id="department" name="department" required="" class="easyui-combogrid"
                        data-options="
                            url: '<?= base_url('lnd/Master_form_test/readDept') ?>',
                            idField: 'name',
                            textField: 'name', 
                            mode: 'remote',
                            fitColumns: true,
                            multiple: true,
                            panelWidth: 500,
                            maxSelections: 2,
                            columns: [[
                                {field:'division_name',title:'Division',width:200},
                                {field:'name',title:'Dept. Name',width:200},
                            ]],
                             onSelect: function(index, row) {
                                var gridInstance = $('#department').combogrid('grid');
                                if (!gridInstance || !gridInstance.length) return;
    
                                var selections = gridInstance.datagrid('getSelections');
                                if (selections.length > 2) {
                                    setTimeout(() => {
                                        $('#department').combogrid('hidePanel');
                                        $.messager.alert('Warning','Maksimal 2 pilihan!');
                                        resetDept();
                                    }, 100);
                                }
                                if (selections.length == 2)  $('#department').combogrid('hidePanel');
                            }
                        ">
                    <div style="margin-top: 10px;">
                        <input class="easyui-checkbox" id="allDept" name="allDept" value="All Dept" data-options="onChange:function(event) {
                                toggleDepartment(event);
                        }" >
                        <label for="allDept">All Department</label>
                    </div>
                    </div>
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Question Type</span>
                    <select class="easyui-combobox" name="questionType" id="questionType" required="" style="width:60%;" data-options="onSelect: onTypeSelect, panelHeight:100">
                        <option value="DIFFERENT" selected>Pre-Test & Post Test is Different</option>
                        <option value="SAME">Pre-Test & Post Test is The Same</option>
                    </select>
                </div>
            </fieldset>
            <div>
                <span id="titleQuestion"><b>PRE-Test & POST-Test Question</b></span>
            </div>

            <div id="formQuestion">
            </div>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="addrow" onclick="addQuestion()"><i class="fa fa-plus"></i> Add Question</a>
            <div style="margin-top-20px;">
                <span id="titlePostQuestion">POST QUESTION</span>
            </div>
            <div id="formPostQuestion">
            </div>
            
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="btnAddPostQuestion" onclick="addPostQuestion()"><i class="fa fa-plus"></i> Add Post Question</a>
        </div>
    </form>
</div>

<div id="toolbarForm">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="addrow" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeCurriculum()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('employee/departements/print') ?>" style="width: 100%;" hidden></iframe>
<script src="global.js"></script>

<script type="text/javascript">

    var isUpdate = false;

    function toggleDepartment(event) {
        if (event) {
            $('#department').combogrid('setValue', 'All Dept');
            $('#department').combogrid('disable');
        } else {
            $('#department').combogrid('clear');
            $('#department').combogrid('enable');
        }
    }
    function templateQuestion(index, type, initValue='') {
        const prefix = `${type}[${index}]`
        const html = `
                    <div class="form-group" id="${type}_${index}" data-index='${index}'>
                        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                            <legend><b class="label-${type}">${toCappital(type)} ${index+1}</b> <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeQuestion(${index}, '${type}')"><i class="fa fa-times"></i> remove</a></legend>
                            <div class="fitem">
                                <span style="width:35%; display:inline-block;">Question</span>
                                <input style="width:60%;" name="${prefix}.question" value="${initValue?.question || ''}" required="" class="easyui-textbox">
                            </div>
                            <div class="fitem">
                                <span style="width:35%; display:inline-block;">Image Question</span>
                                <input style="width:60%;" name="${prefix}.imageQuestion" value="${initValue?.imageQuestion || ''}" class="easyui-filebox" data-options="accept: 'image/*'">
                            </div>
                            <div class="fitem">
                                <span style="width:35%; display:inline-block;"></span>
                                <label><input class="easyui-radiobutton" name="${prefix}.imagePosition" id="imagePosition" checked="${initValue?.imagePosition === 'UP'}" value="UP" style="margin-right:10px;"> Up Question </label>
                                <label><input class="easyui-radiobutton" name="${prefix}.imagePosition" value="BELOW" checked="${initValue?.imagePosition === 'BELOW'}"> Below Question </label>
                            </div>
                            <hr />
                            <div id="answer_${type}_${index}">
                                
                            </div>
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="addrow" onclick="addOpsion(${index}, '${type}')"><i class="fa fa-plus"></i> Add Opsion</a>
                        
                        </fieldset>
                    </div>`;

        return html;
    }

    function templateOpsion(parentIndex, index, type, parentValue='', initValue='') {

        
        const isCorrectAnswer = (parentValue.correct_answer !== undefined && parseInt(parentValue.correct_answer) === index);
        const prefix = `${type}[${parentIndex}].opsion[${index}]`;
        const radioDataOptions = `label:'' ${isCorrectAnswer ? ',checked:true' : ''}`;
        const template = `<div class="form-group" id="${type}_${parentIndex}_opsion_${index}" data-parent-index='${parentIndex}' data-index='${index}'>
                            <div class="fitem">
                                <span style="width:35%; display:inline-block;" class="label-opsion_${parentIndex}">Opsion ${index+1}</span>
                                <input style="width:45%;" name="${prefix}.title" value="${initValue?.title || ''}" required="" data-options="prompt:'Title Opsion ${index+1}'" placeholder="title" class="easyui-textbox">
                                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeOpsion(${parentIndex}, ${index}, '${type}')"><i class="fa fa-times"></i> Remove Opsion</a>
                            </div>
                            <div class="fitem">
                                <span style="width:35%; display:inline-block;"></span>
                                <input style="width:60%;" name="${prefix}.image" value="${initValue?.image || ''}" class="easyui-filebox" data-options="accept: 'image/*', prompt:'Image Opsion ${index+1}'">
                            </div>
                            <div class="fitem">
                                <span style="width:35%; display:inline-block;"></span>
                                <input style="width:20%;" name="${prefix}.point" value="${initValue?.point || ''}" class="easyui-numberspinner" required=""> Point
                                <label><input class="easyui-radiobutton" name="${type}[${parentIndex}].correct_answer" value="${index}" data-options="${radioDataOptions}"> Correct Answer</label>
                            </div>
                        </div>`;
        return template;
    }

    function toCappital(text) {
        var titleCase = text.replace('_', ' ')
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
        return titleCase
    }

    function onTypeSelect(record) {
        if(!record.value) {
            $('#formPostQuestion').hide();
            $('#btnAddPostQuestion').hide();
            $('#titleQuestion').text(`PRE-Test & POST-Test Question`);
            return
        }

        if (record.value === "SAME") {
            $('#formPostQuestion').hide();
            $('#btnAddPostQuestion').hide();
            $('#titleQuestion').text(`PRE-Test & POST-Test Question`);
            $('#titlePostQuestion').hide();
            $('#formPostQuestion').empty();
        } else {
            $('#formPostQuestion').show();
            $('#btnAddPostQuestion').show();
            $('#titlePostQuestion').show();
            $('#titleQuestion').text(`PRE-Test Question`);
        }
    }
    function getDetailData(curriculumId){
        let url = '<?= base_url('lnd/master_form_test/get_detail/') ?>' + curriculumId;
        fetch(url)
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                renderForm(response.data);
            } else {
                alert("Gagal mengambil data.");
            }
        })
        .catch(error => {
            console.error("Error fetching data:", error);
        });
    }

    function resetDept(){
        $('#department').combogrid('setValue', '');
    }

    function renderForm(data) {
        
        $('#training_name').combogrid('setValue', data.training_name);
        $('#department').combogrid('setValue', data.department);
        $('#questionType').combobox('setValue', data.question_type);
        if(data.department === 'All Dept') {
            toggleDepartment(true);
            $('#allDept').checkbox('check');
        }
        
        const questionJson = JSON.parse(data.json_question);
        questionJson.forEach((value, index) => {
            
            $('#formQuestion').append(templateQuestion(index, 'question', value));
            $.parser.parse(`#question_${index}`);
            setTimeout(() => {
                value.opsion.forEach((opt, optIndex) => {
                    var trainingContainer = $(`#answer_question_${index}`)
                    addOpsion(index, 'question', value, opt)
                });
            }, 300);
        });
        
        if (data.json_postquestion) {
            const postJson = JSON.parse(data.json_postquestion);
            postJson.forEach((val, index) => {
                $('#formPostQuestion').append(templateQuestion(index, 'post_question', val));
                $.parser.parse(`#post_question_${index}`);
                if(val.opsion) {
                    setTimeout(() => {
                        val.opsion.forEach((opt, optIndex) => {
                            var trainingContainer = $(`#answer_question_${index}`)
                            addOpsion(index, 'post_question', val, opt)
                        });
                    }, 300);
                }
            });
        }
    }


    function add() {
        $('#dlg_insert').dialog('open');
        $('#dlg_insert').dialog('setTitle', `Add Master Form`)
        
        url_save = '<?= base_url('lnd/master_form_test/storeData_v2') ?>';
        method = 'POST';
        $('#frm_insert').form('clear');
        $("#addrow").show();
        $('#formQuestion').empty();
        $('#formPostQuestion').empty();
        isUpdate = false;

        var formContainer = $('#formQuestion');
        var totalData = formContainer.children().length;
        if(formContainer.children().length === 0) addQuestion();
    }

    function addQuestion() {
        var formQuestion = $('#formQuestion');
        var totalData = formQuestion.children().length;

        var template = templateQuestion(totalData, 'question');
        formQuestion.append(template);
        $.parser.parse(`#question_${totalData}`);


        var trainingContainer = $(`#answer_question_${totalData}`)
        if(trainingContainer.children().length === 0) addOpsion(totalData, 'question')
    }

    function addPostQuestion() {
        var formQuestion = $('#formPostQuestion');
        var totalData = formQuestion.children().length;

        var template = templateQuestion(totalData, 'post_question');
        formQuestion.append(template);
        $.parser.parse(`#post_question_${totalData}`);


        var trainingContainer = $(`#answer_post_question_${totalData}`)
        if(trainingContainer.children().length === 0) addOpsion(totalData, 'post_question')
    }
    function removeQuestion(index, type){
        var formData = $(`#${type}_${index}`);
        if (index >= 0) {
            formData.remove();
            rerenderTemplate(type)
        } else {
            toastr.error('Index tidak valid!', 'Error');
        }
    }
    function addOpsion(parentIndex, type, parentValue='', initValue=''){
        
        var formOpsion = $(`#answer_${type}_${parentIndex}`);
        var totalData = formOpsion.children().length;

        var template = templateOpsion(parentIndex, totalData, type, parentValue, initValue);
        formOpsion.append(template);
        $.parser.parse(`#${type}_${parentIndex}_opsion_${totalData}`);
        
    }

    function removeOpsion(parentIndex, opsionIndex, type) {
        const $parentEl = $(`#answer_${type}_${parentIndex}`);
        if ($parentEl.length === 0) {
            console.warn(`Elemen #answer_${type}_${parentIndex} tidak ditemukan.`);
            return;
        }

        // Remove opsion
        $parentEl.find(`.form-group[data-index="${opsionIndex}"]`).remove();

        // Reindex ulang
        $parentEl.find('.form-group').each(function(newIndex) {
            $(this).attr('data-index', newIndex);
            $(this).attr('id', `${type}_${parentIndex}_opsion_${newIndex}`);

            // Update label
            $(this).find('.label-opsion_0').text(`Opsion ${newIndex + 1}`);

            // Update name/textboxname/radiobuttonname
            $(this).find('[name], [textboxname], [radiobuttonname], [numberboxname], [spinnername]').each(function () {
            const attrs = ['name', 'textboxname', 'radiobuttonname', 'numberboxname', 'spinnername'];
            for (const attr of attrs) {
                const val = $(this).attr(attr);
                if (val) {
                const updated = val.replace(/\[.*?\]\.opsion\[\d+\]/g, `[${parentIndex}].opsion[${newIndex}]`);
                $(this).attr(attr, updated);
                }
            }

            // Update radio value
            if ($(this).attr('type') === 'radio') {
                $(this).val(newIndex);
            }
            });
        });

        // Re-parse if needed
        $.parser.parse($parentEl);
    }

    function rerenderTemplate(type){
        var formData = $('#formQuestion .form-group');
        formData.each(function(index) {
            $(this).attr('data-index', index); // Mengatur data-index sesuai urutan
            $(this).attr('id', `${type}_${index}`)
            $(this).find(`.label-${type}`).text(`Question ${index+1}`)
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dgForm').datagrid('validateRow', editIndex)) {
            $('#dgForm').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }
    
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#formQuestion').empty();
            $('#formPostQuestion').empty();
            
            // getDetailData(row.id)
            
            $('#allDept').checkbox('uncheck');
            
            $('#dlg_insert').dialog('open');
            $('#dlg_insert').dialog('setTitle', `Edit Master Form Test ${row.id}`)
            getDetailData(row.id);
            // $('#frm_insert').form('load', row);
            url_save = '<?= base_url('lnd/master_form_test/update_data_v2/') ?>' + row.id;
            method = 'POST';
            isUpdate = true;
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        fetch('<?= base_url('lnd/master_form_test/delete_data/') ?>'+row.id, {
                            method: 'DELETE', // Metode DELETE
                        })
                        .then(response => response.json()) // Konversi response ke JSON
                        .then(data => {
                            if (data.code === 200) {
                                $('#dg').datagrid('reload');
                                toastr.success(data.message, 'Success');
                            } else {
                                toastr.success("Something Wrong", 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Terjadi kesalahan:', error);
                            toastr.success("Something Wrong", 'error');
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function formArrayToNestedJson(arrayData) {
        let result = {};

        arrayData.forEach(item => {
            let keys = item.name.replace(/\]/g, '').split(/[\.\[]/);
            let value = item.value;
            let ref = result;

            for (let i = 0; i < keys.length; i++) {
                let key = keys[i];

                // Jika index terakhir, assign value
                if (i === keys.length - 1) {
                    ref[key] = value;
                } else {
                    // Jika belum ada key, buat array atau object
                    if (!ref[key]) {
                        if (!isNaN(keys[i + 1])) {
                            ref[key] = [];
                        } else {
                            ref[key] = {};
                        }
                    }

                    // Kalau array
                    if (Array.isArray(ref[key])) {
                        let index = parseInt(keys[i + 1]);
                        if (!ref[key][index]) {
                            ref[key][index] = {};
                        }
                        ref = ref[key][index];
                        i++; // lewati index array
                    } else {
                        ref = ref[key];
                    }
                }
            }
        });

        return result;
    }


    function filter() {
        var filter_training_name = $("#filter_training_name").combogrid('getValue');
        var filter_department = $("#filter_department").combobox('getValue');
        


        var params = '';
        if (filter_training_name) {
            params += '?filter_training_name=' + encodeURIComponent(filter_training_name);
        }
        if (filter_department) {
            params += (params ? '&' : '?') + 'filter_department=' + encodeURIComponent(filter_department);
        }

        $('#dg').datagrid({
            url: '<?= base_url('lnd/master_form_test/datatables') ?>' + params
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('employee/departements/print') ?>' + params);
    }

    function generatedSubDept(dept_id) {
        $('#sub_departement_id').combobox({
            url: '<?php echo base_url('employee/departement_subs/reads'); ?>?departement_id=' + dept_id,
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
    }



    function generatedDepList(){
        $('#departement_id').combogrid({
            url: '<?= base_url('lnd/Master_form_test/readDept') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            valueField: 'id',
            prompt: "Choose Departement",
            columns: [
                [{
                    field: 'number',
                    title: 'Departement No',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Departement Name',
                    width: 250
                }, ]
            ],
            onSelect: function(dept) {
                var departement_id = $('#departement_id').combogrid('getValue');
                generatedSubDept(departement_id)
            }
        });
    }

    function sendDataToServer(requestData, finalFormData) {
        // Buat body dengan format x-www-form-urlencoded (query string)
        const payloadArr = formArrayToNestedJson(requestData.serializeArray());
        // const formData = new URLSearchParams(requestData).toString();
        if(validateNestedJson(payloadArr).length === 0) {
            $.ajax({
                url: url_save,
                method: method,
                data: finalFormData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.code >= 200 && response.code <= 300) {
                        toastr.success(response.message, 'Success');
                        $('#dg').datagrid('reload');
                        $('#dlg_insert').dialog('close');      
                    }
                },
                error: function(xhr) {
                    console.error('Upload gagal', xhr);
                }
            });
        }else{
            toastr.error('Mohon lengkapi data', 'Error');
        }

    }

    function initDgForm() {
        var dg = $('#dgForm').datagrid({
            columns: [
                [{
                    field: 'competenceId',
                    width: 250,
                    halign: 'center',
                    title: "Competence Standard",
                }, {
                    field: 'trainingActivityId',
                    width: 100,
                    halign: 'center',
                    title: "Training Activities",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'indicators',
                    width: 100,
                    halign: 'center',
                    title: "Indicators",
                    editor: {
                        type: 'textbox',
                    }
                }]
            ],
            fit: true,
            singleSelect: true,
            method: 'get',
            onClickCell: function(index, field){
                if (editIndex != index){
                    if (endEditing()){
                        $(this).datagrid('selectRow', index)
                                .datagrid('beginEdit', index);
                        var ed = $(this).datagrid('getEditor', {index:index,field:field});
                        if (ed){
                            ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                        }
                        editIndex = index;
                    } else {
                        setTimeout(function(){
                            $(this).datagrid('selectRow', editIndex);
                        },0);
                    }
                }
            },
            onEndEdit: function(index, row){
                var ed = $(this).datagrid('getEditor', {
                    index: index,
                    field: 'competenceId'
                });
                row.productname = $(ed.target).combobox('getText');
            },
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
        });
    }

    function onClickCell(index, field){
        if (editIndex != index){
            if (endEditing()){
                $('#dgForm').datagrid('selectRow', index)
                        .datagrid('beginEdit', index);
                var ed = $('#dgForm').datagrid('getEditor', {index:index,field:field});
                if (ed){
                    ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                }
                editIndex = index;
            } else {
                setTimeout(function(){
                    $('#dgForm').datagrid('selectRow', editIndex);
                },0);
            }
        }
    }

    function onEndEdit(index, row){
        var ed = $(this).datagrid('getEditor', {
            index: index,
            field: 'competenceId'
        });
        row.productname = $(ed.target).combobox('getText');
    }

    function reload() {
        window.location.reload();
    }

    function validateNestedJson(data, path = '') {
        let errors = [];

        for (let key in data) {
            if (!data.hasOwnProperty(key)) continue;

            const value = data[key];
            const currentPath = path ? `${path}.${key}` : key;

            if (typeof value === 'object' && value !== null) {
                if (Array.isArray(value)) {
                    value.forEach((item, index) => {
                        const arrayPath = `${currentPath}[${index}]`;
                        if (typeof item === 'object' && item !== null) {
                            errors = errors.concat(validateNestedJson(item, arrayPath));
                        } else {
                            if (item === '' || item === null || item === undefined) {
                                errors.push(`Field "${arrayPath}" is empty`);
                            }
                        }
                    });
                } else {
                    errors = errors.concat(validateNestedJson(value, currentPath));
                }
            } else {
                if (value === '' || value === null || value === undefined) {
                    errors.push(`Field "${currentPath}" is empty`);
                }
            }
        }

        return errors;
    }

    
    $(function() {
        //SETTING DATAGRID EASYUI
        // initDgForm()
        $('#dg').datagrid({
            url: '<?= base_url('lnd/master_form_test/datatables') ?>',
            columns: [[
                {field: 'ck', rowspan:'2', checkbox: true},
                {field: 'name', rowspan:'2', width:250, title:'Training Name', align: 'left'},
                {field: 'department', rowspan:'2', width:150, title:'Departement', width:150, align: 'left'},
                {field: 'type', rowspan:'2', width:250, title:'Question Type', align: 'left'},
                {field: 'action', 
                    formatter: function(value,row,index) {
                    return '<a class="button-blue" target="_blank" href="<?= base_url('lnd/form_test/review/') ?>' + row.id + '" style="width:100%;"><i class="fa fa-eye"></i> View</a>';;
                }, rowspan:'2', width:150, title:'View', width:100, align: 'left'},
                {field: '', colspan:2, title:'Created', width:150, halign: 'center'},
                {field: '', colspan:2, title:'Updated', width:80, halign: 'center'},
            ],[
                {field: 'createdBy', title:'By', width:100, align: 'center'},
                {field: 'createdTime', title:'Date', width:150, align: 'center'},
                {field: 'updatedBy', title:'By', width:100, align: 'center'},
                {field: 'updatedTime', title:'Date', width:150, align: 'center'},
            ]],
            toolbar: '#toolbar',
            singleSelect: true,
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
                    if($(this).form('validate')) {
                        const form = document.querySelector('#frm_insert');
                        const formData = new FormData(form);
                        const { json, files } = formDataToNestedJsonWithFiles_v2(formData);
                        
                        const data = formDataToNestedJsonWithFiles(formData);

                        const payload = new FormData();
                        payload.append('data', JSON.stringify(json)); // Data JSON masuk ke $_POST['data']

                        for (const key in files) {
                            payload.append(key, files[key]); // Nama key harus seperti question[0][imageQuestion]
                        }

                        sendDataToServer($('#frm_insert'), payload);
                    }
                }
            }]
        });

    });

    function sanitizeEmptyFileFields(json) {
        if (Array.isArray(json)) {
            return json.map(sanitizeEmptyFileFields);
        } else if (typeof json === 'object' && json !== null) {
            const result = {};
            for (const key in json) {
                if (json.hasOwnProperty(key)) {
                    const value = json[key];

                    // Jika value adalah objek kosong (dari file kosong), ganti jadi string kosong
                    if (
                        typeof value === 'object' &&
                        value !== null &&
                        Object.keys(value).length === 0
                    ) {
                        result[key] = '';
                    } else {
                        result[key] = sanitizeEmptyFileFields(value);
                    }
                }
            }
            
            return result;
        }
        
        return json;
    }

    function formDataToNestedJsonWithFiles_v2(formData) {
        const json = {};
        const files = {};

        for (let [name, value] of formData.entries()) {
            const keys = name.match(/[^\[\]]+/g);
            let ref = json;

            // Abaikan file kosong (tanpa nama)
            if (value instanceof File && !value.name) continue;

            for (let i = 0; i < keys.length; i++) {
                let key = keys[i].startsWith('.') ? keys[i].substring(1) : keys[i];
                const nextKey = keys[i + 1];

                if (i === keys.length - 1) {
                    // Jangan simpan nama file jika tidak ada file
                    if (value instanceof File) {
                        if (value.name) {
                            ref[key] = value.name;
                            const fileFieldName = keys.reduce((str, k, idx) => {
                                k = k.startsWith('.') ? k.substring(1) : k;
                                return idx === 0 ? k : `${str}[${k}]`;
                            }, '');
                            files[fileFieldName] = value;
                        }
                    } else {
                        // Jika value sudah ada, jadikan array
                        if (ref[key] !== undefined) {
                            if (!Array.isArray(ref[key])) {
                                ref[key] = [ref[key]];
                            }
                            ref[key].push(value);
                        } else {
                            ref[key] = value;
                        }
                    }
                } else {
                    if (!ref[key]) {
                        ref[key] = /^\d+$/.test(nextKey) ? [] : {};
                    }
                    ref = ref[key];
                }
            }
        }

        return { json, files };
    }

    function formDataToNestedJsonWithFiles(formData) {
        const json = {};
        const files = {};

        for (let [name, value] of formData.entries()) {
            // Skip empty file input
            if (value instanceof File && value.name === '') {
                continue;
            }

            // Check if this field already exists in JSON
            const existing = _.get(json, name);

            if (value instanceof File) {
                // Save file name reference in JSON
                if (existing !== undefined) {
                    if (!Array.isArray(existing)) {
                        _.set(json, name, [existing, value.name]);
                    } else {
                        existing.push(value.name);
                        _.set(json, name, existing);
                    }
                } else {
                    _.set(json, name, value.name);
                }

                // Store actual file
                files[name] = value;

            } else {
                // Normal field handling (support multiple values)
                if (existing !== undefined) {
                    if (!Array.isArray(existing)) {
                        _.set(json, name, [existing, value]);
                    } else {
                        existing.push(value);
                        _.set(json, name, existing);
                    }
                } else {
                    _.set(json, name, value);
                }
            }
        }

        return { json, files };
    }

    
</script>