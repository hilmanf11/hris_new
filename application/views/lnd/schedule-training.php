<table id="dg" class="easyui-datagrid" style="width:100%;">
</table>

<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Click this to hide the filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width:50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Register Date</span>
                        <input style="width:60%;" id="registerDateFilter" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable: false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Training Date</span>
                        <input style="width:29%;" id="trainingDateFromFilter" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable: false">
                        To
                        <input style="width:28%;" id="trainingDateEndFilter" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable: false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Training Name</span>
                        <input style="width:60%;" id="trainingMaterialFilter" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width:50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Category</span>
                        <select style="width:60%;" name="categoryFilter" id="categoryFilter" required="" class="easyui-combobox" panelHeight="auto">
                            <option value="">Choose All</option>
                            <option value="New">New</option>
                            <option value="Mutasi">Mutasi</option>
                            <option value="Departement">Departement</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Trainee</span>
                        <input style="width:60%;" id="traineeFilter" class="easyui-combogrid">
                    </div>
                </div>
            </fieldset>
        </div>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="generateQR()"><i class="fa fa-generate"></i> Generate QR</a>
    </div>
</div>
<div id="dlg_qr" class="easyui-dialog" title="Generated QR Test" data-options="closed: true,modal:true" style="width: 560px; padding:20px; top: 20px;">
    <div style="display: flex; gap: 50px;">
        <div>
            <h1>QR Pre Test</h1>
            <div id="qrcode-pretest"></div>
            <div style="display: flex; flex-direction: column; align-items: stretch; margin-top: 10px; gap: 8px;">
                <button onclick="downloadQR('qrcode-pretest', 'pre_test_qr.png')">⬇️ Download QR</button>
                <input type="hidden" id="pretest-url">
                <button class="btn-copy" data-clipboard-target="#pretest-url">📋 Copy Link</button>
            </div>
        </div>
        <div>
            <h1>QR Post Test</h1>
            <div id="qrcode-posttest"></div>
            <div style="display: flex; flex-direction: column; align-items: stretch; margin-top: 10px; gap: 8px;">
                <button onclick="downloadQR('qrcode-posttest', 'post_test_qr.png')">⬇️ Download QR</button>
                <input type="hidden" id="posttest-url">
                <button class="btn-copy" data-clipboard-target="#posttest-url">📋 Copy Link</button>
            </div>
        </div>
        
    </div> 
</div>
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height:450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Register Date</span>
                <input style="width:60%;" id="registerDate" name="registerDate" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable: false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block; vertical-align: top;">Training Dates (max 3)</span>
                <div id="trainingDatesWrapper" style="width:60%; display: inline-block;">
                    <table id="trainingDatesTable" style="width:100%;">
                        <tr>
                            <td style="padding-bottom:5px;">
                                <input class="easyui-datebox training-date" style="width:120px;">
                            </td>
                            <td style="padding-bottom:5px;">
                                <select class="easyui-combobox batch-count" style="width:100px;">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> Batch<?= $i > 1 ? 'es' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td class="week-label" style="padding-left:5px;">Week: -</td>
                            <td style="padding-left:5px;">
                                <a href="javascript:void(0);" class="easyui-linkbutton" onclick="addTrainingDate()">+</a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <input type="hidden" name="training_dates" id="training_dates_json">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Induction</span>
                <select id="inductionSelect" style="width:60%;" name="induction" required="" class="easyui-combobox" panelHeight="auto">
                    <option value="Basic Requirement">Basic Requirement</option>
                    <option value="L&D Program: Upgrade Competence">L&D Program: Upgrade Competence</option>
                    <option value="L&D Program: Refresh Training">L&D Program: Refresh Training</option>
                    <option value="L&D Program: Training Activities">L&D Program: Training Activities</option>
                </select>
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Training Name</span>
                <input style="width:60%;" name="trainingName" id="trainingName" required="" class="easyui-textbox">
                <input type="hidden" name="trainer" id="trainer_hidden">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Category</span>
                <select style="width:60%;" name="category" id="category" required="" class="easyui-combobox" panelHeight="auto">
                    <option value="New">New</option>
                    <option value="Mutasi">Mutasi</option>
                    <option value="Departement">Departement</option>
                </select>             
            </div>
            <div id="trainers-wrapper">
                <div class="fitem" id="trainerRow_1">
                    <span style="width:35%; display:inline-block;">Trainer Name</span>
                    <input style="width:60%;" name="trainerName[]" id="trainerName_1" required class="easyui-combogrid">
                    <input type="hidden" name="trainingTrainerId[]" id="trainingTrainerId_1" value="">
                    <a href="javascript:void(0)" class="easyui-linkbutton" style="margin-left:5px;" onclick="addTrainer()">+</a>
                </div>
            </div>
			
            <div class="fitem" id="trainee-container">
                <span style="width:35%; display:inline-block;">Trainee</span>
                <input style="width:60%;" name="trainee" id="trainee" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Total Trainee</span>
                <input style="width:60%;" name="totalTrainee" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Duration</span>
                <input style="width:60%;" name="duration" class="easyui-textbox" required="">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<iframe id="printout" src="<?= base_url('lnd/schedule_training/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    window.onload = function() {
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            console.log('Teks berhasil disalin:', e.text);
            toastr.success("Link berhasil disalin ke clipboard!"); // Tetap gunakan toastr
            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            console.error('Gagal menyalin teks:', e.action);
            toastr.error("Gagal menyalin link.");
        });
        
        // Initial setup for trainerName_1 (default internal)
        $(`#trainerName_1`).combogrid({
            url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Trainer Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'name',
                    title: 'Employee Name',
                    width: 120
                }, {
                    field: 'positionName',
                    title: 'Position',
                    width: 200
                }]
            ],
        });
		// Optionally, ensure default value is properly synced at form show
		// $('#dlg_insert').on('open', syncTrainerHidden);

        $('#trainingMaterialFilter').combogrid({
            url: '<?= base_url('lnd/training_activity/list') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'trainingActivity',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Training Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'competenceName',
                    title: 'Competence Standard',
                    width: 120
                }, {
                    field: 'trainingActivity',
                    title: 'Training Activity Name',
                    width: 200
                }]
            ],
        });

        $('#traineeFilter').combogrid({
            url: '<?= base_url('lnd/schedule_training/readsDepartements') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Department Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [
                    {
                        field: 'division',
                        title: 'Division Name',
                        width: 120
                    },
                    {
                        field: 'name',
                        title: 'Department Name',
                        width: 120
                    }]
            ],
        });
    }

    function add() {
        $('#dlg_insert').dialog('open');

        $('#frm_insert').form('clear');
        url_save = '<?= base_url('lnd/schedule_training/create_data') ?>';
        method = 'POST';

        // === Clear dynamically added training dates (keep the first row only) ===
        $('#trainingDatesTable tr:not(:first)').remove(); // remove all extra rows

        // Clear the first row fields
        $('#trainingDatesTable .training-date').datebox('setValue', '');
        $('#trainingDatesTable .batch-count').combobox('clear');
        $('#trainingDatesTable .week-label').text('Week: -');

        // === Reset trainer fields to default combogrid ===
        const wrapper = $('#trainers-wrapper');
        wrapper.empty(); // Remove all existing trainer fields

        // Add default empty trainer row (combogrid)
        handleTrainerInputType('Internal', '', '');
    }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log("#row", row);
        
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('lnd/schedule_training/update_data/') ?>' + row.id_training;
            method = 'PUT';
			// row.trainer = row.trainer_type

            populateFormFields(row);
            populateTrainerFields(row); // Set trainers dynamically (handles External/Internal)
            const trainingDates = extractTrainingDates(row);

            // Clear all but first row
            $('#trainingDatesTable tr:not(:first)').remove();

            trainingDates.forEach((item, index) => {
                if (index === 0) {
                    $('#trainingDatesTable .training-date').datebox('setValue', item.date);
                    $('#trainingDatesTable .batch-count').combobox('setValue', item.batch);
                    updateWeekLabel($('#trainingDatesTable .training-date'), $('#trainingDatesTable .week-label'));
                }
                else if (index < 4) {
                    addTrainingDate(item.date, item.batch);
                }
            });

        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function updateWeekLabel($dateInput, $label) {
        const dateStr = $dateInput.datebox('getValue');
        if (dateStr) {
            const date = new Date(dateStr);
            const getDay = date.getDate();
            const week = getWeekByDay(getDay);
            $label.text(`Week: ${week}`);
        } else {
            $label.text('Week: -');
        }
    }

    function extractTrainingDates(row) {
        const trainingDates = [];
        let batch = 1;

        for (const key in row) {
            // Match keys like "May_2025_W2", "Jun_2025_W1", etc.
            const match = key.match(/^([A-Za-z]+)_(\d{4})_W\d$/);

            if (match && row[key]) {
                const [, , year] = match; // Destructure month (ignored), year
                const value = row[key]; // e.g., "15 May", "02 Jun"

                // Combine the date string with the year
                const fullDateStr = `${value} ${year}`; // e.g., "15 May 2025"

                // Parse and convert to YYYY-MM-DD
                const parsedDate = new Date(fullDateStr);
                if (!isNaN(parsedDate)) {
                    const yyyy = parsedDate.getFullYear();
                    const mm = String(parsedDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(parsedDate.getDate()).padStart(2, '0');
                    const formattedDate = `${yyyy}-${mm}-${dd}`;

                    trainingDates.push({
                        date: formattedDate,
                        batch: batch++
                    });
                }
            }
        }

        return trainingDates;
    }

    function populateFormFields(row) {
        // Set induction value
        $('#inductionSelect').combobox('setValue', row.induction);
        $('#category').combobox('setValue', row.category);

		$('#trainer_hidden').val(row.trainer)
        // Setup trainingName combogrid with existing values
        // Note: The actual trainer type from the training data will be retrieved in onLoadSuccess/onSelect of trainingName combogrid
        setupTrainingNameComboGrid(
            row.induction,
            row.trainingActivityId, 
            row.trainingName 
        );

        // Setup trainee combogrid if 'Departement'
        setupTraineeCombogridTraineeType(
            row.category,
            row.departementId, 
            row.trainee
        );
    }
    
    /**
     * Mengubah input Trainer Name menjadi textbox (untuk 'External') atau combogrid (untuk internal).
     * @param {string} trainerType - Nilai 'External' atau nilai lain (misal: 'Internal').
     * @param {string} trainerNameValue - Nilai nama trainer yang akan diisi ke input.
     * @param {string} trainerUuid - UUID trainer yang akan diisi ke hidden input (kosongkan jika External).
     */
    function handleTrainerInputType(trainerType, trainerNameValue = '', trainerUuid = '') {
        const wrapper = $('#trainers-wrapper');
        wrapper.empty(); // Hapus semua input trainer yang ada

        const defaultTrainerId = 1;
        const currentId = nextTrainerId++;
        
        if (trainerType === 'External') {
            const trainerRow = $(`
                <div class="fitem" id="trainerRow_${currentId}">
                    <span style="width:35%; display:inline-block;">Trainer Name</span>
                    <input style="width:60%;" name="trainerName[]" id="trainerName_${currentId}" required class="easyui-textbox" value="${trainerNameValue}">
                    <input type="hidden" name="trainingTrainerId[]" id="trainingTrainerId_${currentId}" value=""> <a href="javascript:void(0)" class="easyui-linkbutton" style="margin-left:5px;" onclick="addTrainer(null, true)">+</a>
                </div>
            `);
            wrapper.append(trainerRow);
            
            // Inisialisasi EasyUI TextBox dan LinkButton
            $.parser.parse(trainerRow);
            // Hapus class 'validatebox-text' yang ditambahkan oleh parser untuk menjaga gaya yang konsisten
            $(`#trainerName_${currentId}`).next('.textbox').find('input.textbox-text').removeClass('validatebox-text');

            // Atur ulang nextTrainerId agar penambahan berikutnya memiliki ID yang unik
            nextTrainerId = currentId + 1;

        } else {
            // Logika untuk Trainer Internal (combogrid)
            const trainerRow = $(`
                <div class="fitem" id="trainerRow_${currentId}">
                    <span style="width:35%; display:inline-block;">Trainer Name</span>
                    <input style="width:60%;" name="trainerName[]" id="trainerName_${currentId}" required class="easyui-combogrid">
                    <input type="hidden" name="trainingTrainerId[]" id="trainingTrainerId_${currentId}" value="${trainerUuid}">
                    <a href="javascript:void(0)" class="easyui-linkbutton" style="margin-left:5px;" onclick="addTrainer()">+</a>
                </div>
            `);
            wrapper.append(trainerRow);

            // Re-initialize EasyUI combogrid on the default trainer
            $(`#trainerName_${currentId}`).combogrid({
                url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
                panelWidth: 450,
                idField: 'id',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Trainer Name',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function (e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                columns: [[
                    { field: 'name', title: 'Employee Name', width: 120 },
                    { field: 'positionName', title: 'Position', width: 200 }
                ]]
            });
            
            // Set nilai jika ada (biasanya di mode 'add' akan kosong, di 'update' akan diisi)
            if (trainerNameValue) {
                $(`#trainerName_${currentId}`).combogrid('setValue', trainerUuid);
                $(`#trainerName_${currentId}`).combogrid('setText', trainerNameValue);
            }

            $.parser.parse(trainerRow);
            // Atur ulang nextTrainerId
            nextTrainerId = currentId + 1;
        }
    }


    function populateTrainerFields(row) {
        const trainerType = row.trainer;
        const trainerNames = (row.trainerName || '').split(',').map(name => name.trim()).filter(Boolean);
        const trainerIds = (row.trainingTrainerId || '').split(',').map(id => id.trim()).filter(Boolean);
        const wrapper = $('#trainers-wrapper');

		console.log("#trainerType", trainerType);
		

        wrapper.empty(); // Clear existing trainers
        
        // Reset global counter before repopulating
        nextTrainerId = 1;

        if (trainerType === 'External') {
            // Jika External, kita hanya menampilkan satu textbox dengan nilai gabungan atau nilai pertama
            const externalTrainerName = trainerNames.join(', '); // Gabungkan jika ada banyak
            handleTrainerInputType('External', externalTrainerName); // Panggil fungsi dengan tipe 'External'
        } else {
            // Jika Internal, tampilkan combogrid untuk setiap trainer yang ada di list
            if (trainerNames.length > 0) {
                 trainerNames.forEach((name, index) => {
                    const trainerId = nextTrainerId++; // Gunakan nextTrainerId sebagai ID unik
                    const trainerUuid = trainerIds[index] || '';

                    const buttonHtml = trainerId === 1
                        ? `<a href="javascript:void(0)" class="easyui-linkbutton" style="margin-left:5px;" onclick="addTrainer()">+</a>`
                        : `<a href="javascript:void(0)" class="easyui-linkbutton" style="margin-left:5px;" onclick="removeTrainer(${trainerId})">−</a>`;

                    const trainerRow = $(`
                        <div class="fitem" id="trainerRow_${trainerId}">
                            <span style="width:35%; display:inline-block;">Trainer Name</span>
                            <input style="width:60%;" name="trainerName[]" id="trainerName_${trainerId}" required class="easyui-combogrid">
                            <input type="hidden" name="trainingTrainerId[]" id="trainingTrainerId_${trainerId}" value="${trainerUuid}">
                            ${buttonHtml}
                        </div>
                    `);

                    wrapper.append(trainerRow);
                    
                    // Inisialisasi EasyUI combogrid
                    $(`#trainerName_${trainerId}`).combogrid({
                        url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
                        panelWidth: 450,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: 'Choose Trainer Name',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function (e) {
                                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                            }
                        }],
                        columns: [[
                            { field: 'name', title: 'Employee Name', width: 120 },
                            { field: 'positionName', title: 'Position', width: 200 }
                        ]]
                    });

                    // Set the value (ID) and text (Name)
                    $(`#trainerName_${trainerId}`).combogrid('setValue', trainerUuid);
                    $(`#trainerName_${trainerId}`).combogrid('setText', name);
                    $.parser.parse(trainerRow); // Parse the newly added row
                });
            } else {
                // Default ke satu input combogrid kosong jika data trainer kosong
                handleTrainerInputType('Internal', '', '');
            }
        }
    }


    function setupTrainingNameComboGrid(inductionVal, selectedTrainingId, selectedTrainingText) {
        if (inductionVal) {
            $('#trainingName').combogrid({
                url: '<?= base_url('lnd/schedule_training/readsTrainingActivity'); ?>?induction=' + encodeURIComponent(inductionVal),
                panelWidth: 450,
                idField: 'id',
                textField: 'trainingActivity',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Training Name',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function (e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                columns: [[
                    { field: 'competenceName', title: 'Competence Standard', width: 120 },
                    { field: 'trainingActivity', title: 'Training Activity Name', width: 200 }
                ]],
                // === Tambahkan event onSelect untuk menangani perubahan saat dipilih ===
                onSelect: function (index, row) {
                    // 'row' di sini adalah data Training Activity yang dipilih
                    const trainerType = row.trainer; // Asumsi field 'trainer' ada di data Training Activity ('External' atau lainnya)
                    const trainerName = row.trainer_name || ''; // Asumsi field 'trainer_name' ada di data Training Activity
                    
					$("#trainer").combobox('setValue', trainerType);
					$("#trainer_hidden").val(trainerType);
                    handleTrainerInputType(trainerType, trainerName, ''); // Panggil logika penggantian input
                },
                // ======================================================================
                onLoadSuccess: function (data) {
                    // Set value setelah options dimuat (untuk mode Update)
                    if (selectedTrainingId && selectedTrainingText) {
                        $('#trainingName').combogrid('setValue', selectedTrainingId);
                        $('#trainingName').combogrid('setText', selectedTrainingText);

                        // Panggil logika pengubahan tipe input di mode UPDATE
                        const rowData = data.rows.find(row => row.id === selectedTrainingId);
                        if (rowData) {
                            // Asumsi rowData dari datagrid memiliki field 'trainer' dan 'trainer_name'
                            // Note: Jika di mode Update, populateTrainerFields sudah dipanggil sebelumnya di function update()
                            // dan sudah mengatur tampilan trainer fields. Di sini kita hanya perlu memastikan
                            // combogrid 'trainingName' terisi dengan benar.
                        }
                    }
                }
            });
        } else {
            if ($('#trainingName').data('combogrid')) {
                $('#trainingName').combogrid('clear');
            }
        }
    }

    function setupTraineeCombogridTraineeType(value, selectedTraineeId, selectedTraineeName) {
        if (value === 'Departement') {
            $('#trainee').combogrid({
                url: '<?= base_url('lnd/schedule_training/readsDepartements') ?>',
                panelWidth: 450,
                idField: 'id',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Department Name',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                columns: [[
                    { field: 'division', title: 'Division Name', width: 120 },
                    { field: 'name', title: 'Department Name', width: 120 }
                ]],
                onLoadSuccess: function () {
                    if (selectedTraineeId && selectedTraineeName) {
                        $('#trainee').combogrid('setValue', selectedTraineeId);
                        $('#trainee').combogrid('setText', selectedTraineeName);
                    }
                }
            });
        } else {
            if($('#trainee').data('combogrid')) {
                $('#trainee').combogrid('setValue', "New/Mutasi").combogrid('disable');
                $('#trainee').combogrid('setText', "New/Mutasi").combogrid('disable');
            }
        }
    }

    function generateQR() {
        var row = $('#dg').datagrid('getSelected');
        console.log("#row", row);
        
        if (row) {
            getDetailData(row.id_training);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function getDetailData(id){
        let url = '<?= base_url('lnd/schedule_training/get_form_test/') ?>' + id;
        fetch(url)
        .then(response => response.json())
        .then(response => {
            console.log("#res", response);
            
            if (response.code === 200) {
                $('#dlg_qr').dialog('open');
                let id = response.data.id;
                let rawTrainingActivity = response.data.trainingActivity;
                let trainingActivity = rawTrainingActivity.replace(/\s+/g, '_').toLowerCase();
                let url = '<?= base_url('lnd/form_test/pre_test/') ?>' + trainingActivity + '/' + id;
                let url_post = '<?= base_url('lnd/form_test/post_test/') ?>' + trainingActivity + '/' + id;
                $('#qrcode-pretest').empty();
                var qrcode = new QRCode("qrcode-pretest", {
                    text: url,
                    width: 228,
                    height: 228,
                    correctLevel: QRCode.CorrectLevel['M']
                });

                console.log("#pre", url, url_post);
                

                $('#qrcode-posttest').empty();
                var qrcodepost = new QRCode("qrcode-posttest", {
                    text: url_post,
                    width: 228,
                    height: 228,
                    correctLevel: QRCode.CorrectLevel['M']
                });
                $('#pretest-url').val(url);
                $('#posttest-url').val(url_post);
            } else {
                toastr.warning("Gagal mengambil data. Data training tidak di temukan.", "Warning");
            }
        })
        .catch(error => {
            console.error("Error fetching data:", error);
        });
    }

    function downloadQR(qrElementId, filename) {
        const canvas = document.querySelector(`#${qrElementId} canvas`);
        if (!canvas) return alert("QR code not ready yet.");

        const link = document.createElement("a");
        link.href = canvas.toDataURL("image/png");
        link.download = filename;
        link.click();
    }

    function copyLink(inputId) {
        var clipboard = new ClipboardJS(inputId);

        clipboard.on('success', function(e) {
            console.log('Teks berhasil disalin:', e.text);
            toastr.success("Link berhasil disalin ke clipboard!"); // Tetap gunakan toastr
            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            console.error('Gagal menyalin teks:', e.action);
            toastr.error("Gagal menyalin link.");
        });

        const linkInput = document.getElementById(inputId);
        if (!linkInput) {
            toastr.error("Input link tidak ditemukan.");
            return;
        }
        const link = linkInput.value;

        // Cek apakah navigator.clipboard dan writeText tersedia
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(link).then(() => {
                toastr.success("Link berhasil disalin ke clipboard!");
            }).catch(() => {
                toastr.error("Gagal menyalin link.");
            });
        } else {
            // Fallback untuk browser yang tidak support navigator.clipboard
            try {
                linkInput.select();
                linkInput.setSelectionRange(0, 99999); // Untuk mobile
                document.execCommand('copy');
                toastr.success("Link berhasil disalin ke clipboard!");
            } catch (err) {
                toastr.error("Fitur salin tidak didukung di browser ini.");
            }
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        fetch('<?= base_url('lnd/schedule_training/delete_data/') ?>'+row.id_training, {
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

    function reload() {
        window.location.reload();
    }

    //DOWNLOAD EXCEL TEMPLATE
    function download_excel() {
        window.location.assign('<?= base_url('lnd/schedule_training/download_template_schedule_training') ?>');
    }

    function sendDataToServer(requestData) {
        // Buat body dengan format x-www-form-urlencoded (query string)
        const formData = new URLSearchParams(requestData).toString();

        fetch(url_save, {
            method: method, // Metode POST atau PUT
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded' // Header penting
            },
            body: formData // Data body
        })
        .then(response => {
            return response.json()}) // Ubah response ke JSON
        .then(data => {
            if(data.code >= 200 && data.code <= 300) {
                toastr.success(data.message, 'Success');
                $('#dg').datagrid('reload');
                $('#dlg_insert').dialog('close');
                
            }
        })
        .catch(error => {
            toastr.error('Something Error', 'Error');
            console.error('Terjadi kesalahan:', error);
        });
    }

    // Ambil tahun berjalan secara dinamis
    const currentYear = new Date().getFullYear();
    const startDate = `${currentYear}-01-01`;
    const endDate = `${currentYear}-12-31`;
    const calendarColumns = generateCalendarColumns(startDate, endDate);

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('lnd/schedule_training/datatables') ?>',
            columns: [[
                {field: 'ck', rowspan:'2', checkbox: true},
                {field: 'induction', rowspan:'2', width:280, title:'Induction', halign: 'center'},
                {field: 'trainingName', rowspan:'2', width:150, title:'Training Name', halign: 'center'},
                {field: 'trainerName', rowspan:'2', width:150, title:'Trainer', halign: 'center'},
                {field: 'trainee', rowspan:'2', width:150, title:'Trainee', halign: 'center'},
                {field: 'remarks', rowspan:'2', width:100, title:'Remarks', halign: 'center'},
                {field: 'totalTrainee', rowspan:'2', width:80, title:'Total Trainee', halign: 'center'},
                {field: 'duration', rowspan:'2', width:80, title:'Duration (Minute)', halign: 'center'},
                {field: 'registerDate', rowspan:'2', width:100, title:'Date', halign: 'center',
                    formatter: function() {
                        return '<div style="text-align:center;">Plan<hr style="margin:4px 0;">Actual</div>';
                    },
                    styler: function() {
                        return 'white-space: normal;';
                    }
                },
                ...calendarColumns.filter(col => !Array.isArray(col)), // top headers

                {field: '', colspan:2, title:'Created', width:80, align: 'center'},
                {field: '', colspan:2, title:'Updated', width:80, align: 'center'},
            ],[
                ...calendarColumns
                    .filter(col => Array.isArray(col)) // week fields
                    .flat(),
                {field: 'createdBy', title:'By', width:100, align: 'center'},
                {field: 'createdTime', title:'Date', width:150, align: 'center'},
                {field: 'updatedBy', title:'By', width:100, align: 'center'},
                {field: 'updatedTime', title:'Date', width:150, align: 'center'},
            ]],
            toolbar: '#toolbar',
            pagination: true,
            rownumbers: true,
            fit: true,
            nowrap: false,
            // remoteFilter: true,
            // sortName: 'index',
            // sortOrder: 'asc'
            singleSelect:true,
            pageList: [40, 50, 100, 500, 1000],
            pageSize: 40,
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    if ($('#frm_insert').form('validate')) {
                        const trainingDates = getTrainingDateRows();
                        
                        if (trainingDates.length === 0) {
                            toastr.error("Please select at least one training date.", "Error");
                            return;
                        }

                        if (trainingDates.length === 0) {
                            toastr.error("Please select at least one training date.", "Error");
                            return;
                        }

                        // Set the value of the hidden input
                        $('#training_dates_json').val(JSON.stringify(trainingDates));

                        let formData = $('#frm_insert').serialize();
						console.log("#formData", formData);
						
                        sendDataToServer(formData);
                    }
                }
            }]
        });

        $('#category').combobox({
            onSelect: function (record) {
                const value = record.value;

                // Clear and rebuild the #trainee input
                let html = '';
                if (value === 'New' || value === 'Mutasi') {
                    html = `
                        <span style="width:35%; display:inline-block;">Trainee</span>
                        <input style="width:60%;" name="trainee" id="trainee" value="New MP/Mutasi" required class="easyui-textbox" disabled>
                    `;
                } else if (value === 'Departement') {
                    html = `
                        <span style="width:35%; display:inline-block;">Trainee</span>
                        <input style="width:60%;" name="trainee" id="trainee" required class="easyui-combobox">
                    `;
                }

                // Replace the content inside the container
                $('#trainee-container').html(html);

                // Re-parse the newly added element
                $.parser.parse('#trainee-container');

                // If Departement selected, initialize combobox
                if (value === 'Departement') {
                    $('#trainee').combogrid({
                        url: '<?= base_url('lnd/schedule_training/readsDepartements') ?>',
                        panelWidth: 450,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: 'Choose Department Name',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                            }
                        }],
                        columns: [
                            [
                                {
                                    field: 'division',
                                    title: 'Division Name',
                                    width: 120
                                },
                                {
                                    field: 'name',
                                    title: 'Department Name',
                                    width: 120
                                }]
                        ],
                    });
                }
            }
        });

        // Handle change on induction combobox (to update Training Name Combogrid URL)
        $('#inductionSelect').combobox({
            onChange: function (newVal) {
                if (newVal) {
                    setupTrainingNameComboGrid(newVal, null, null);
                } else {
                    if ($('#trainingName').data('combogrid')) {
                        $('#trainingName').combogrid('clear');
                    }
                }
            }
        });
        bindDatePickers();
    });

    /**
     * Fungsi ini menghasilkan definisi kolom untuk kalender/tabel.
     * @param {string} startDateStr Tanggal mulai dalam format 'YYYY-MM-DD'.
     * @param {string} endDateStr Tanggal akhir dalam format 'YYYY-MM-DD'.
     * @returns {Array<Object>} Array definisi kolom untuk Jeasyui DataGrid (atau sejenisnya).
     */
    function generateCalendarColumns(startDateStr, endDateStr) {

    // Helper parsing tanggal yyyy-mm-dd agar konsisten di semua browser (termasuk Firefox)
    function parseDateYMD(str) {
        if (typeof str !== 'string') return null;
        
        const match = str.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) return null;
        
        const year = Number(match[1]);
        const month = Number(match[2]) - 1; // Bulan di JS dimulai dari 0
        const day = Number(match[3]);

        // Kunci Perbaikan Firefox: Gunakan constructor Y, M-1, D, 12, 0, 0 untuk zona waktu lokal
        const d = new Date(year, month, day, 12, 0, 0); 

        if (d.getFullYear() === year && d.getMonth() === month && d.getDate() === day) {
            return d;
        }
        return null;
    }

    function cloneDate(date) {
        return new Date(date.getTime());
    }

    function safeParseDate(str) {
        if (!str) return null;
        if (Object.prototype.toString.call(str) === '[object Date]') return str;
        
        let d = parseDateYMD(str);
        if (d && !isNaN(d.getTime())) return d;
        
        if (typeof str === 'string') {
            const alt = str.replace(/\//g, '-');
            d = parseDateYMD(alt);
            if (d && !isNaN(d.getTime())) return d;
        }

        // Fallback terakhir ke Date(string) yang tidak konsisten
        d = new Date(str);
        if (!isNaN(d.getTime())) return d;
        
        return null;
    }

    // --- Eksekusi Utama ---

    const startDate = safeParseDate(startDateStr);
    const endDate = safeParseDate(endDateStr);
    const columns = [];

    if (!startDate || !endDate || isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        return columns;
    }

    let iterDate = cloneDate(startDate);

    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const monthShortNames = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    while (iterDate <= endDate) {
        const year = iterDate.getFullYear();
        const month = iterDate.getMonth(); 
        const monthName = monthNames[month];
        const weeks = ['W1', 'W2', 'W3', 'W4'];

        const children = weeks.map((w, i) => ({
            // Field ini digunakan Jeasyui untuk mencari data sel, 
            field: `${monthName}_${year}_W${i + 1}`, 
            title: w,
            width: 80,
            align: 'center',
            
            // !!! PERUBAHAN KUNCI: Akses rowData untuk mengambil tanggal asli YYYY-MM-DD
            formatter: function (value, rowData, rowIndex) {
                // 1. Cek 'value' (misal: "5 May") sebagai penanda bahwa ada data.
                if (!value) {
                    return ''; // Kosongkan sel jika tidak ada data pelatihan di minggu ini
                }

                // 2. Ambil string tanggal YYYY-MM-DD yang valid dari kolom referensi.
                const dateStr = rowData.originalTrainingDate; 
                
                // 3. Pastikan dateStr valid dan parsing tanggal.
                if (dateStr && typeof dateStr === 'string') {
                    // Gunakan safeParseDate untuk parsing yang konsisten
                    const dateObj = safeParseDate(dateStr);
                    
                    if (dateObj && !isNaN(dateObj.getTime())) {
                        const day = dateObj.getDate();
                        const monthShort = monthShortNames[dateObj.getMonth()];
                        
                        // Kembalikan Hari BulanPendek yang sudah diformat dari objek Date yang valid
                        return `${day} ${monthShort}`; 
                    }
                }
                // Jika parsing 'originalTrainingDate' gagal, kembalikan string kosong.
                return ``; 
            },
            styler: function () {
                return 'white-space: normal;'; 
            }
        }));

        // Tambahkan header kolom (bulan)
        columns.push({
            title: `${monthName} ${year}`,
            colspan: weeks.length,
            align: 'center'
        });

        // Tambahkan kolom anak (minggu)
        columns.push(children);

        // Pindah ke bulan berikutnya
        iterDate.setDate(1); 
        iterDate.setMonth(iterDate.getMonth() + 1);
    }

    return columns;
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

    let maxDates = 3;

    function addTrainingDate(dateValue = '', batchValue = '') {
        const rowCount = $('#trainingDatesTable tr').length;
        if (rowCount >= 4) return; // 1 original row + 3 more = max 4 rows

        const newRow = `
        <tr>
            <td style="padding-bottom:5px;">
                <input class="easyui-datebox training-date" style="width:120px;">
            </td>
            <td style="padding-bottom:5px;">
                <select class="easyui-combobox batch-count" style="width:100px;">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> Batch<?= $i > 1 ? 'es' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </td>
            <td class="week-label" style="padding-left:5px;">Week: -</td>
            <td style="padding-left:5px;">
                <a href="javascript:void(0);" class="easyui-linkbutton" onclick="removeRow(this)">−</a>
            </td>
        </tr>
    `;

        $('#trainingDatesTable').append(newRow);

        // Apply EasyUI
        const lastRow = $('#trainingDatesTable tr:last');
        const dateInput = lastRow.find('.training-date');
        const batchSelect = lastRow.find('.batch-count');
        const weekLabel = lastRow.find('.week-label');

        dateInput.datebox({
            formatter: myformatter,
            parser: myparser,
            onSelect: function(date) {
                updateWeekLabel($(this), weekLabel);
            }
        }).datebox('setValue', dateValue);

        batchSelect.combobox();
        batchSelect.combobox('setValue', batchValue);
        if(dateValue === '' && batchValue === '') {
            bindDatePickers()
        } else {
            updateWeekLabel(dateInput, weekLabel);
        }
    }

    function removeRow(link) {
        $(link).closest('tr').remove();
    }

    function updateWeek(input) {
        const value = $(input).datebox('getValue'); // Get value as a string (format: yyyy-mm-dd)
        if (!value) return;

        const parts = value.split('-');
        if (parts.length !== 3) return;

        const day = parseInt(parts[2]);

        let week = '-';
        if (day >= 1 && day <= 8) week = 'W1';
        else if (day >= 9 && day <= 16) week = 'W2';
        else if (day >= 17 && day <= 24) week = 'W3';
        else if (day >= 25 && day <= 31) week = 'W4';

        // Find the <td class="week-label"> in the same row and update it
        $(input).closest('tr').find('.week-label').text('Week: ' + week);
    }

    function getWeekByDay(day) {
        if (day >= 1 && day <= 8) return 'W1';
        if (day >= 9 && day <= 16) return 'W2';
        if (day >= 17 && day <= 24) return 'W3';
        if (day >= 25 && day <= 31) return 'W4';
        return '-';
    }

    // Initialize all existing dateboxes
    function bindDatePickers() {
        $('.training-date').each(function () {
            // prevent rebinding the same input again
            if (!$(this).data('bound')) {
                $(this).datebox({
                    formatter: myformatter,
                    parser: myparser,
                    editable: false,
                    onSelect: function (date) {
                        const day = date.getDate();
                        const week = getWeekByDay(day);
                        $(this).closest('tr').find('.week-label').text('Week: ' + week);
                    }
                });
                $(this).data('bound', true); // mark as bound
            }
        });
    }

    function getTrainingDateRows() {
        const rows = $('#trainingDatesTable tr');
        const result = [];

        rows.each(function () {
            const dateInput = $(this).find('input.training-date');
            const batchSelect = $(this).find('select.batch-count');
            const weekLabel = $(this).find('.week-label').text().replace('Week: ', '').trim();

            const trainingDate = dateInput.datebox('getValue'); // get EasyUI datebox value
            const batchCount = batchSelect.combobox('getValue'); // get EasyUI combobox value

            if (trainingDate) {
                result.push({
                    training_date: trainingDate,
                    batch_count: batchCount || null,
                    week: weekLabel || "-"

                });
            }
        });

        return result;
    }

    let trainerCount = 1;
    let nextTrainerId = 2; // ensures unique IDs for each new row

    /**
     * Menambahkan baris input trainer baru.
     * @param {string} name - Nilai nama trainer (opsional).
     * @param {boolean} isExternal - True jika tipe input harus textbox (External).
     */
    function addTrainer(name = '', isExternal = false) {
        const wrapper = document.getElementById('trainers-wrapper');
        const currentId = nextTrainerId++;
        const inputClass = isExternal ? 'easyui-textbox' : 'easyui-combogrid';
        const spanText = ''; 

        const div = document.createElement('div');
        div.className = 'fitem';
        div.id = `trainerRow_${currentId}`;
        div.innerHTML = `
            <span style="width:35%; display:inline-block;">${spanText}</span>
            <input style="width:60%;" name="trainerName[]" id="trainerName_${currentId}" required class="${inputClass}" value="${isExternal ? name ? name: '' : ''}">
            <input type="hidden" name="trainingTrainerId[]" id="trainingTrainerId_${currentId}" value="">
            <a href="javascript:void(0)" class="easyui-linkbutton" style="margin-left:5px;" onclick="removeTrainer(${currentId})">−</a>
        `;
        wrapper.appendChild(div);

        // Reinitialize EasyUI components
        if (isExternal) {
            $(`#trainerName_${currentId}`).textbox();
             // Hapus class 'validatebox-text' yang ditambahkan oleh parser untuk menjaga gaya yang konsisten
            $(`#trainerName_${currentId}`).next('.textbox').find('input.textbox-text').removeClass('validatebox-text');
        } else {
            // Reinitialize EasyUI combogrid
            $(`#trainerName_${currentId}`).combogrid({
                url: '<?= base_url('lnd/request_training/readsEmployeesStaffUp') ?>',
                panelWidth: 450,
                idField: 'id',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Trainer Name',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                columns: [
                    [{
                        field: 'name',
                        title: 'Employee Name',
                        width: 120
                    }, {
                        field: 'positionName',
                        title: 'Position',
                        width: 200
                    }]
                ],
            });
        }

        // Parse the new row to apply EasyUI styling
        $.parser.parse(div);
    }

    function removeTrainer(id) {
        const row = document.getElementById(`trainerRow_${id}`);
        if (row) row.remove();
    }

    function filter() {
        var trainingMaterialFilter = $("#trainingMaterialFilter").combogrid('getValue');
        var registerDateFilter = $("#registerDateFilter").datebox('getValue');
        var categoryFilter = $("#categoryFilter").combogrid('getValue');
        var traineeFilter = $("#traineeFilter").combogrid('getValue');
        var trainingDateFromFilter = $("#trainingDateFromFilter").datebox('getValue');
        var trainingDateEndFilter = $("#trainingDateEndFilter").datebox('getValue');

        // debug_to_console(curiculum_id);
        var params = "?trainingName=" + trainingMaterialFilter +
            "&registerDate=" + registerDateFilter +
            "&category=" + categoryFilter +
            "&trainee=" + traineeFilter +
            "&trainingDateFrom=" + trainingDateFromFilter +
            "&trainingDateEnd=" + trainingDateEndFilter
        ;

        $('#dg').datagrid({
            url: '<?= base_url('lnd/schedule_training/datatables') ?>' + params
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('employee/departements/print') ?>' + params);
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('lnd/schedule_training/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('lnd/schedule_training/upload') ?>',
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

                        //Clear File
                        $.ajax({
                            url: "<?= base_url('lnd/schedule_training/uploadclearFailed') ?>"
                        });

                        var json = eval('(' + result + ')');
                        console.log("#json", json);

                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('lnd/schedule_training/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        console.log(result);
                                        if (result.theme == "success") {
                                            $('#p_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('lnd/schedule_training/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });

                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        $("#p_remarks").append(title + "<br>");
                                    },
                                    fail: function(jqXHR, textStatus) {
                                        console.log("#textStatus", textStatus, jqXHR);

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
                            }
                        }
                    }
                });
            }
        }]
    });

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('lnd/schedule_training/print/excel') ?>');
    }
</script>