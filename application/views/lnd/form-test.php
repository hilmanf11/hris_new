<div class="p-4 easyui-navpanel" style="margin-bottom: 50px;">
    <header>
        <div class="m-toolbar">
            <div class="m-title" ><span id="titleLable">Form <?php echo $title?></span></div>
        </div>
    </header>

    <div class="alert alert-danger mt-4" role="alert" id="alertErrorContainer">
        <center>
            <h4 id="alertErrorTitle"></h4>
            <hr>
            <p class="m-0" id="alertErrorMessage"><b></b></p>
        </center>
    </div>

    <div class="alert alert-primary mt-4" role="alert" id="alertContainer">
        <center>
            <p class="m-0" id="alertMessage"><b></b></p>
            <hr>
        </center>
    </div>

    <div id="attendanceContainer">
        <header>
            <div class="m-toolbar">
                <div class="m-title">Absence</div>
            </div>
        </header>
        <form id="formAbsen">
            <div style="margin-bottom:5px">
                <input class="easyui-combogrid" label="Employee ID" name="employeeId" id="employeeId" prompt="Employee ID" labelPosition="top" required="" style="width:100%">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-textbox" label="Full Name" prompt="Full name" name="employeeName" id="employeeName" disabled style="width:100%" labelPosition="top">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-textbox" label="Departement" prompt="Departement" name="employeeDepartement" id="employeeDepartement" disabled style="width:100%" labelPosition="top">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-textbox" label="Sub Departement" prompt="Sub Departement" name="employeeSubDept" id="employeeSubDept" disabled style="width:100%" labelPosition="top">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-textbox" label="Training Name" prompt="Training Name" name="trainingName" id="trainingName" disabled style="width:100%" labelPosition="top">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-textbox" label="Trainer Name" prompt="Trainer Name" name="trainerName" id="trainerName" disabled style="width:100%" labelPosition="top">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-datebox" label="Training Date" prompt="Training Date" name="trainingDate" id="trainingDate" labelPosition="top" required="" data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30" style="width:100%">
            </div>
            <div style="margin-bottom:5px">
                <input class="easyui-textbox" label="Location" prompt="Location" style="width:100%" name="trainingLocation" value="PT. PIRANTI TEKNIK INDONESIA" labelPosition="top">
            </div>
        <footer>
            <div class="m-toolbar">
                <div style="text-align:center;margin-top:30px">
                    <button type="submit" class="easyui-linkbutton" style="width:100%;height:40px"><span style="font-size:16px">Next</span></button>
                </div>
            </div>
        </footer>
        </form>
    
    </div>

    <div id="titleContainer" class="alert alert-primary mt-4" style="text-align:center">
        <div>
            <h5>PT. PIRANTI TEKNIK INDONESIA</h5>
        </div>
        <div>
            <span id="fullNameTitle"></span><br />
            <span id="trainingNameTitle"></span><br />
            <span id="departementTitle"></span><br />
            <span id="typeTitle"><?php echo $title; ?></span>
            <span id="finalScoreTitle">You Final Score for this test is <b><span id="scoreFinalTitle"></span>/100</b></span><br />
        </div>
        <div>
            <div style="text-align:center;margin-top:30px" id="btnStartTest">
                <button class="easyui-linkbutton" style="width:50%;height:40px" onClick="startTest()"><span style="font-size:16px">Start Test</span></button>
            </div>

            <div style="text-align:center;margin-top:30px" id="btnResultTest">
                <button class="easyui-linkbutton" style="width:50%;height:40px" onClick="resultTest()"><span style="font-size:16px">View Result</span></button>
            </div>

        </div>

    </div>

    <div id="titleFeedbackContainer" class="alert alert-primary mt-4" style="text-align:center">
        <div>
            <h5>PT. PIRANTI TEKNIK INDONESIA</h5>
        </div>
        <div>
            <h3>Questioner Feedback</h3>
        </div>
        <div>
            <span>Pilih satu jawaban untuk setiap pertanyaan.</span><br />
            <span>Setiap pertanyaan memiliki 5 pilihan jawaban dengan skala berikut:</span><br />
            <span>Sangat Baik: 10</span><br />
            <span>Baik: 8</span><br />
            <span>Cukup: 6</span><br />
            <span>Kurang Baik: 4</span><br />
            <span>Buruk: 2</span><br />
        </div>
        <div>
            <div style="text-align:center;margin-top:30px">
                <button class="easyui-linkbutton" style="width:50%;height:40px" onClick="startFeedback()"><span style="font-size:16px">Start Feedback</span></button>
            </div>
        </div>

    </div>

    <div id="titleFeedbackResultContainer" class="alert alert-primary mt-4" style="text-align:center">
        <div>
            <h5>PT. PIRANTI TEKNIK INDONESIA</h5>
        </div>
        <div>
            <h3>Go To Post Test</h3>
        </div>
        <div>
            <div style="text-align:center;margin-top:30px">
                <button class="easyui-linkbutton" style="width:50%;height:40px" onClick="startTest()"><span style="font-size:16px">Next</span></button>
            </div>
        </div>

    </div>

    <div id="preTestContainer" style="width:100%">
    PRE TEST
    </div>

    <div id="postTestContainer" style="width:100%">
    POST TEST

    </div>
    <div id="feedbackContainer" style="width:100%">
    FEEDBACK

    </div>

    <div id="resultContainer" style="width:100%">
        <h4 id="titleReviewQuestion">Form Review Pre Quesiton</h4>
        <div id="resultQuestion"></div>
    </div>
    
    <div id="resultPostContainer">
        <hr />
        <h4 >Form Review Post Quesiton</h4>
        <div id="resultPostQuestion"></div>
    </div>

    
</div>

<script type="text/javascript">

    // STAG: 1. Absence, 2. Info Test, 3. Form test, 4. Finish Test, 5. Review Test;
    let testType = `<?php echo $test_type?>`;
    let test_id = `<?php echo $test_id?>`;

    let absenceData = $('#formAbsen').serialize();
    window.originalQuestionJson = <?php echo json_encode($data); ?>;
    window.originalFeedbackQuestionJson = <?php echo json_encode($data_feedback); ?>;
    window.feedbackResult = '';

    $(function() {
        $("#alertContainer").hide();
        $("#alertErrorContainer").hide();
        $("#attendanceContainer").show();
        $("#titleContainer").hide();
        $("#preTestContainer").hide();
        $("#postTestContainer").hide();
        $("#resultContainer").hide();
        $("#feedbackContainer").hide();
        $("#titleFeedbackResultContainer").hide()
        $("#titleFeedbackContainer").hide();
        $("#resultPostContainer").hide();
        $("#titleReviewQuestion").hide();
        let data = <?php echo json_encode($data); ?>;
        let dataFeedback = <?php echo json_encode($data_feedback); ?>;
        if(data) {
            console.log("#data", data);
            
            $("#trainingName").textbox('setValue', data.trainingActivity);
            $("#trainerName").textbox('setValue', data.trainer);
            $('#trainingNameTitle').text(data.trainingActivity)
        }
        if(testType === 'REVIEW' || testType === 'REVIEW-FEEDBACK') {
            $("#resultContainer").show();
            $("#attendanceContainer").hide();
            let data = <?php echo json_encode($data); ?>;
            if(data.question_type === 'DIFFERENT') {
                $("#titleLable").text('Form Review Pre-Test, Form Review Post Test')
                if(data.json_question) {
                    $("#titleReviewQuestion").show();
                    renderFormFromJson('#resultQuestion', data.json_question)
                }
                if(data.json_postquestion) {
                    $("#resultPostContainer").show();
                    renderFormFromJson('#resultPostQuestion', data.json_postquestion)
                }
            }

            if(data.question_type === 'SAME') {
                $("#titleReviewQuestion").hide();
                $("#titleLable").text('Form Review Pre-Test & Post Test');
                if(data.json_question) {
                    renderFormFromJson('#resultQuestion', data.json_question)
                }
            }
            if(data.json_feedback) {
                renderFeedbackFromJson('#resultContainer', data.json_feedback)
            }

        }

        if(testType === 'POST_TEST') {
            let dataFeedback = <?php echo json_encode($data_feedback); ?>;
            // console.log(dataFeedback);
            // $("#attendanceContainer").show()
            // $("#titleFeedbackContainer").hide()
        }
        
        $("#employeeId").combogrid({
            url: '<?= base_url('employee/employees/readFulls') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose All',
            onSelect: onSelectEmployee,
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

        submitAbsence();
        
    })

    const baseUrl = '<?= base_url('assets/image/lnd/') ?>';
    function renderFormFromJson(containerSelector, jsonData) {
        const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        const $container = $(containerSelector);
        $container.empty();

        let html = `<form id="formQuestion" method="post" style="width:100%">`;

        data.forEach((item, index) => {
            const questionId = `question_${index}`;
            html += `<div class="question-block" style="margin-bottom:20px;">`;

            // Panel khusus untuk pertanyaan
            html += `<div class="easyui-panel" title="Question ${index + 1}" data-options="fit:false" style="padding:10px;">`;

            // Image on TOP
            const baseUrl = '<?= base_url('assets/image/lnd/') ?>';
            if (item.imagePosition === 'UP' && item.imageQuestion) {
                html += `<div><img src="${baseUrl}${item.imageQuestion}" style="max-width:100%; margin-bottom:10px;"></div>`;
            }

            // Question Text
            html += `
                <div style="margin-bottom:10px;">
                    <input class="easyui-textbox" name="q_${index}" value="${item.question}" data-options="multiline:true,readonly:true" style="width:100%;">
                </div>`;

            // Image on BOTTOM
            if (item.imagePosition === 'BELOW' && item.imageQuestion) {
                html += `<div><img src="${baseUrl}${item.imageQuestion}" style="max-width:100%; margin-top:10px;"></div>`;
            }

            // Options Panel
            html += `<div class="easyui-panel" style="border:none;">`;
            item.opsion.forEach((opt, optIndex) => {

                const radioId = `${questionId}_opt_${optIndex}`;
                html += `
                    <div style="margin:5px 0;">
                        <label class="custom-radio">
                            <input type="radio" name="${questionId}" id="${radioId}" value="${optIndex}">
                            ${opt.title}
                        </label>`;

                if (opt.image ) {
                    html += `<div><img src="${baseUrl}${opt.image}" style="max-width:150px; margin-top:5px;"></div>`;
                    // opt.image.forEach(img => {
                    // });
                }

                html += `</div>`;
            });
            html += `</div>`; // end options panel

            html += `</div>`; // end question panel
            html += `</div>`; // end question block
        });
        if(testType !== 'REVIEW') {
            html += `
                <div style="text-align:center;margin-top:30px">
                    <button type="button" id="submitBtn" class="easyui-linkbutton" onClick="submitTest()" style="width:100%;height:40px">Submit</button>
                </div>
            `;

        }


        html += `</form>`;

        // Inject ke container
        $container.html(html);

        // Re-parse EasyUI setelah konten dimasukkan
        setTimeout(() => {
            $.parser.parse(containerSelector);
        }, 100); // Delay agar elemen benar-benar terlihat
    }

    function renderFeedbackFromJson(containerSelector, jsonData) {
        const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        const $container = $(containerSelector);
        $container.empty();

        let html = `<form id="formQuestion" method="post" style="width:100%">`;

        data.forEach((item, index) => {
            const questionId = `question_${index}`;
            html += `<div class="question-block" style="margin-bottom:20px;">`;

            // Panel khusus untuk pertanyaan
            html += `<div class="easyui-panel" data-options="fit:false" style="padding:10px;">`;

            
            // Question Text
            html += `
                <div style="margin-bottom:10px;">
                    <input class="easyui-textbox" name="${questionId}" value="${item.question}" data-options="multiline:true,readonly:true" style="width:100%;">
                </div>`;

           
            // Options Panel
            html += `<div class="easyui-panel" style="border:none;">`;
            // item.opsion.forEach((opt, optIndex) => {
                const radioId = `opt_${index}`;
                html += `
                    <div style="margin:5px 0;">
                        <label class="custom-radio">
                            <input type="radio" name="${radioId}" id="${radioId}" value="10">
                            Sangat Baik
                        </label>
                        <label class="custom-radio">
                            <input type="radio" name="${radioId}" id="${radioId}" value="8">
                            Baik
                        </label>
                        <label class="custom-radio">
                            <input type="radio" name="${radioId}" id="${radioId}" value="6">
                            Cukup
                        </label>
                        <label class="custom-radio">
                            <input type="radio" name="${radioId}" id="${radioId}" value="4">
                            Kurang Baik
                        </label>
                        <label class="custom-radio">
                            <input type="radio" name="${radioId}" id="${radioId}" value="2">
                            Buruk
                        </label>`;

                html += `</div>`;
            // });
            html += `</div>`; // end options panel

            html += `</div>`; // end question panel
            html += `</div>`; // end question block
        });
        if(testType !== 'REVIEW-FEEDBACK') {
            html += `
                <div style="text-align:center;margin-top:30px">
                    <button type="button" id="submitBtn" class="easyui-linkbutton" onClick="submitFeedback()" style="width:100%;height:40px">Finish Questioner</button>
                </div>
            `;

        }


        html += `</form>`;

        // Inject ke container
        $container.html(html);

        // Re-parse EasyUI setelah konten dimasukkan
        setTimeout(() => {
            $.parser.parse(containerSelector);
        }, 100); // Delay agar elemen benar-benar terlihat
    }

    function validateTest(jsonData) {
        let isValid = true;
        let errorMessage = '';

        jsonData.forEach((item, index) => {
            if (item.selectedIndex === null || item.selectedText === null) {
                isValid = false;
                errorMessage += `Pertanyaan nomor ${index + 1} belum dijawab.`;
            }
        });

        if (!isValid) {
            $("#alertErrorMessage").text(errorMessage);
            $("#alertErrorContainer").show();
            return false;
        }

        $("#alertErrorContainer").hide();
        $("#alertErrorMessage").text('');

        return true;
    }

    function validateFeedback(jsonData) {
        let isValid = true;
        let errorMessage = '';

        jsonData.forEach((item, index) => {
            if (item.selectedValue === null || item.selectedValue === undefined) {
                isValid = false;
                errorMessage += `Pertanyaan nomor ${index + 1} belum dijawab.`;
            }
        });

        if (!isValid) {
            $("#alertErrorMessage").text(errorMessage);
            $("#alertErrorContainer").show();
            return false;
        }

        $("#alertErrorContainer").hide();
        $("#alertErrorMessage").text('');

        return true;
    }

    function checkEmployeeTestStatus() {
        let url_check = '<?= base_url('lnd/form_test/check_employee_test_status') ?>';
        $.ajax({
            url: url_check,
            method: 'GET',
            data: {
                test_id: test_id,
                employee_id: $('#employeeId').combogrid('getValue'),
                test_type: testType
            },
            dataType: 'json',
            success: function(response) {
                console.log('response', response);
                if(!response.completed) {
                    if(testType === 'POST_TEST') {
                        $("#titleFeedbackContainer").show()

                    }else{
                        $("#titleContainer").show();
                    }
                    $('#attendanceContainer').hide();
                    $("#btnResultTest").hide();
                    $("#finalScoreTitle").hide();
                    $("#alertErrorContainer").hide();
                    $("#alertErrorMessage").text('');
                    $("#alertErrorTitle").text('');
                } else {
                    $('#attendanceContainer').show();
                    $("#titleContainer").hide();
                    $("#alertErrorContainer").show();
                    $("#alertErrorMessage").text(response.message);
                    $("#alertErrorTitle").text('Warning');
                }
            },
            error: function(xhr) {
                console.error('gagal check employee test status', xhr);
            }
        })
    }



    function submitTest() {
        var jsonData;
        var result;
        if(testType === 'PRE_TEST') {
            jsonData = JSON.parse(window.originalQuestionJson.json_question);
            result = evaluateTest(jsonData, '#preTestContainer');
        }else{
            let data = <?php echo json_encode($data); ?>;
            if(data.question_type === 'SAME') {
                jsonData = JSON.parse(window.originalQuestionJson.json_question);
            }else{
                jsonData = JSON.parse(window.originalQuestionJson.json_postquestion);
            }
            result = evaluateTest(jsonData, '#postTestContainer');
        }

        let dataTraining = <?php echo json_encode($data); ?>;
        

        let payload = {
            test_id: test_id,
            training_activity: $('#trainingNameTitle').text(),
            trainer_name: $('#trainerName').textbox('getValue'),
            employee_id: $('#employeeId').combogrid('getValue'),
            type_training: testType,
            json_response_detail: JSON.stringify(jsonData),
            grade: result.totalPoint,
            test_date: $('#trainingDate').datebox('getValue'),
            test_completed_date: testType === 'POST_TEST' ? $('#trainingDate').datebox('getValue'): null,
            trainer: $('#trainerName').textbox('getValue'),
            json_result_history: JSON.stringify(result.userAnswers),
            score_pre_test: testType === 'PRE_TEST' ? result.totalPoint : null,
            score_post_test: testType === 'POST_TEST' ? result.totalPoint : null,
            history_feedback_id: typeof testType === 'POST_TEST' ? window.feedbackResult.feedbackItems[0].id : null,
            createdBy: $("#employeeName").textbox('getValue'),
            trainingTrainerId: dataTraining.trainingTrainerId.split(', ')
        };
        

        if(validateTest(result.userAnswers)) {
            let url_save = '<?= base_url('lnd/form_test/insert_data') ?>';
            $.ajax({
                url: url_save,
                method: 'POST',
                data: payload,
                dataType: 'json',
                success: function(response) {
                    if(response.code >= 200 && response.code <= 300) {
                        $("#titleContainer").show();
                        $("#resultContainer").hide();
                        if(testType === 'PRE_TEST') {
                            $("#preTestContainer").hide();

                        }else{
                            $("#postTestContainer").hide();
                        }
                        
                        $("#btnResultTest").show();
                        $("#btnStartTest").hide();
                        $("#finalScoreTitle").show();
                        $("#scoreFinalTitle").text(result.totalPoint);
                    } else {
                        
                        $("#alertErrorTitle").text(response.message);
                        $("#alertErrorMessage").text(response.errors);
                        $("#alertErrorContainer").show();
                    }
                },
                error: function(xhr) {
                    console.error('gagal submit test', xhr);
                    $("#alertErrorMessage").text(xhr.responseJSON.message);
                    $("#alertErrorContainer").show();
                }
            });

        }

        // renderTestResult(jsonData, result.userAnswers, '#resultContainer');

    }

    function submitFeedback() {
        const jsonData = JSON.parse(window.originalFeedbackQuestionJson.json_feedback); // simpan json awal di variable global sebelumnya
        const result = evaluateFeedback(jsonData, '#feedbackContainer');
        let dataFeedback = <?php echo json_encode($data_feedback); ?>;

        let payload = {
            test_id: test_id,
            feedback_id: dataFeedback.id,
            user_id: $('#employeeId').combogrid('getValue'),
            json_response: JSON.stringify(result),
            createdBy: $('#employeeId').combogrid('getValue')
        }

        if(validateFeedback(result.feedbackItems)) {
            let url_save = '<?= base_url('lnd/form_test/storeFeedback') ?>';
            $.ajax({
                url: url_save,
                method: 'POST',
                data: payload,
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    
                    if(response.code >= 200 && response.code <= 300) {
                        $("#titleFeedbackResultContainer").show();
                        $("#resultContainer").hide();
                        $("#preTestContainer").hide();
                        $("#feedbackContainer").hide();
                        
                        $("#btnResultTest").show();
                        $("#btnStartTest").hide();
                        $("#finalScoreTitle").show();
                        $("#scoreFinalTitle").text(result.totalPoint);
                    } else {
                        $("#alertErrorTitle").text(response.message);
                        $("#alertErrorMessage").text(response.errors);
                        $("#alertErrorContainer").show();
                    }
                },
                error: function(xhr) {
                    console.error('gagal submit test', xhr);
                    $("#alertErrorMessage").text(xhr.responseJSON.message);
                    $("#alertErrorContainer").show();
                }
            });

        }

    }

    function resultTest() {
        $("#resultContainer").show();
        $("#titleContainer").hide();
        if(testType === 'POST_TEST') {
            let data = <?php echo json_encode($data); ?>;
            let jsonData = '';
            if(data.question_type === 'SAME') {
                jsonData = JSON.parse(window.originalQuestionJson.json_question); 
            }else{
                jsonData = JSON.parse(window.originalQuestionJson.json_postquestion);
            }
            const result = evaluateTest(jsonData, '#postTestContainer');
            renderTestResult(jsonData, result.userAnswers, '#resultContainer');
        } else{
            const jsonData = JSON.parse(window.originalQuestionJson.json_question); // simpan json awal di variable global sebelumnya
            const result = evaluateTest(jsonData, '#preTestContainer');
            renderTestResult(jsonData, result.userAnswers, '#resultContainer');
        }
    }

    function evaluateTest(jsonData, containerSelector) {
        const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        const $container = $(containerSelector);

        let totalPoint = 0;
        let userAnswers = [];

        data.forEach((item, index) => {
            const questionId = `question_${index}`;
            const selected = $(`input[name="${questionId}"]:checked`, $container).val();

            const selectedIndex = selected !== undefined ? parseInt(selected) : null;
            const correctIndex = parseInt(item.correct_answer);

            let point = 0;
            if (selectedIndex !== null && item.opsion[selectedIndex]) {
                point = parseInt(item.opsion[selectedIndex].point || 0);
                totalPoint += point;
            }

            userAnswers.push({
                question: item.question,
                selectedIndex,
                correctIndex,
                selectedText: selectedIndex !== null ? item.opsion[selectedIndex].title : null,
                isCorrect: selectedIndex === correctIndex,
                point
            });
        });

        return {
            totalPoint,
            userAnswers
        };
    }

    function evaluateFeedback(jsonData, containerSelector) {
        // Parse JSON if needed
        const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        const $container = $(containerSelector);

        let totalScore = 0;
        let maxPossibleScore = 0;
        let feedbackItems = [];
        // Process each feedback question
        data.forEach((question, index) => {
            const questionName = `opt_${index}`;
            const selectedValue = $(`input[name="${questionName}"]:checked`, $container).val();
            
            // Calculate scores
            const point = selectedValue ? parseInt(selectedValue) : 0;
            totalScore += point;
            maxPossibleScore += 10; // Assuming 10 is max score per question
            
            // Store feedback details
            feedbackItems.push({
                question: $(`input[name="question_${index}"]`).val(),
                selectedValue: selectedValue,
                point: point,
                feedbackText: getFeedbackText(selectedValue)
            });
        });

        // Calculate percentage score
        const percentageScore = maxPossibleScore > 0 
            ? Math.round((totalScore / maxPossibleScore) * 100) 
            : 0;

        // Get overall feedback
        const overallFeedback = getOverallFeedback(percentageScore);

        return {
            id: '',
            totalScore,
            maxPossibleScore,
            percentageScore,
            feedbackItems,
            overallFeedback
        };
    }

    // Helper function to get text for each answer
    function getFeedbackText(value) {
        const feedbackMap = {
            '10': 'Sangat Baik',
            '8': 'Baik',
            '6': 'Cukup',
            '4': 'Kurang Baik',
            '2': 'Buruk'
        };
        return feedbackMap[value] || feedbackMap['undefined'];
    }

    // Helper function to get overall feedback
    function getOverallFeedback(percentage) {
        if (percentage >= 90) return 'Sangat Memuaskan';
        if (percentage >= 75) return 'Memuaskan';
        if (percentage >= 60) return 'Cukup';
        return 'Perlu Perbaikan';
    }

    function renderTestResult(jsonData, userAnswers, containerSelector) {
        const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
        const $container = $(containerSelector);
        $container.empty();

        data.forEach((item, index) => {
            const answer = userAnswers[index];
            const questionId = `question_${index}`;
            let html = `<div class="easyui-panel" title="Result for Question ${index + 1}" style="margin-bottom:15px;padding:10px;">`;

            if (item.imagePosition === 'UP' && item.imageQuestion) {
                html += `<div><img src="${baseUrl}${item.imageQuestion}" style="max-width:100%; margin-bottom:10px;"></div>`;
            }

            html += `<div style="font-weight:bold; margin-bottom:5px;">${item.question}</div>`;

            if (item.imagePosition === 'BELOW' && item.imageQuestion) {
                html += `<div><img src="${baseUrl}${item.imageQuestion}" style="max-width:100%; margin-top:10px;"></div>`;
            }

            html += `<div>`;
            item.opsion.forEach((opt, optIndex) => {
                const isSelected = answer.selectedIndex === optIndex;
                const isCorrect = parseInt(item.correct_answer) === optIndex;

                html += `
                    <div style="margin:5px 0; ${isCorrect ? 'color:blue;' : ''}">
                    <label>
                    <input type="radio" disabled ${isSelected ? 'checked' : ''}>
                        ${opt.title}
                        ${isCorrect ? '<span style="color:blue;">✅ Correct</span>' : ''}
                        ${isSelected && !isCorrect ? '<span style="color:red;">❌ Your Choice</span>' : ''}
                    </label>
                    <div>
                        ${opt.image ? `<img src="${baseUrl}${opt.image}" style="max-width:150px; margin-top:5px; margin-bottom:10px;">` : ''}
                    </div>
                    </div>
                `;
            });
            html += `</div></div>`;
            $container.append(html);
        });

        $.parser.parse(containerSelector);
        }



    function onSelectEmployee(index, data) {
        if(data) {
            $("#employeeName").textbox('setValue', data.name);
            $("#fullNameTitle").text(data.name)
            $("#employeeDepartement").textbox('setValue', data.departement_name);
            $('#departementTitle').text(data.departement_name)
            $("#employeeSubDept").textbox('setValue', data.departement_sub_name);
        }
    }

    function submitAbsence(){
        $("#formAbsen").on('submit', function (e){
            e.preventDefault();
            checkEmployeeTestStatus();
        })
    }

    function startTest() {
        $("#titleContainer").hide();
        if(testType === 'PRE_TEST') {
            $("#preTestContainer").show();
            let data = <?php echo json_encode($data); ?>;
            if(data.json_question) {
                renderFormFromJson('#preTestContainer', data.json_question)
            }

        }else if(testType === 'POST_TEST'){
            $('#titleFeedbackResultContainer').hide();
            $("#postTestContainer").show();
            
            let data = <?php echo json_encode($data); ?>;
            if(data.question_type === 'SAME') {
                renderFormFromJson('#postTestContainer', data.json_question)
            }else{
                renderFormFromJson('#postTestContainer', data.json_postquestion)
            }

        }else{
            $("#resultContainer").show();
            let data = <?php echo json_encode($data); ?>;
            if(data.json_question) {
                renderFormFromJson('#resultContainer', data.json_question)
            }

        }

    }

    function startFeedback() {
        $("#titleFeedbackContainer").hide();
        $("#feedbackContainer").show();
        let dataFeedback = <?php echo json_encode($data_feedback); ?>;
        if(dataFeedback.json_feedback) {
            renderFeedbackFromJson('#feedbackContainer', dataFeedback.json_feedback)
        }
    }
</script>