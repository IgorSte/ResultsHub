function init_buttons()
{
    const detailButtons = document.querySelectorAll('[data-bs-target="#runDetailsModal"]');
    
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-run-id');
            const name = this.getAttribute('data-run-name');
            
            document.getElementById('run-details-name').textContent = 'Details for run: ' + name;
            document.getElementById('run-details-loader').style.display = 'block';
            document.getElementById('run-details-content').style.display = 'none';
            
            fetch(`api/details/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('run-details-content').innerHTML = data.data;
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Connection error');
                })
                .finally(() => {
                    document.getElementById('run-details-loader').style.display = 'none';
                    document.getElementById('run-details-content').style.display = 'block';
                });
        });
    });

    const chartButtons = document.querySelectorAll('[data-bs-target="#runChartModal"]');
    
    chartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const runId = this.getAttribute('data-run-id');
            const runName = this.getAttribute('data-run-name');
            
            document.getElementById('run-chart-name').textContent = 'Chart for run: ' + runName;
            document.getElementById('run-chart-loader').style.display = 'block';
            document.getElementById('run-chart-content').style.display = 'none';
            
            fetch(`api/chart/${runId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const chartData = data.data;
                        render_chart(chartData);
                    } else {
                        showError(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Connection error');
                })
                .finally(() => {
                    document.getElementById('run-chart-loader').style.display = 'none';
                    document.getElementById('run-chart-content').style.display = 'block';
                });
        });
    });

    const chartModal = document.getElementById('runChartModal');
    
    // Очищаем контент при закрытии
    chartModal.addEventListener('hidden.bs.modal', function() {
        if (runChart)
        {
            runChart.destroy();
            runChart = null;
        }
    });


    const startButtons = document.querySelectorAll('[data-bs-target="#runStartModal"]');
    
    startButtons.forEach(button => {
        button.addEventListener('click', function() {
            const runId = this.getAttribute('data-run-id');
            const runName = this.getAttribute('data-run-name');
            
            document.getElementById('run-start-name').textContent = 'Starting run: ' + runName;
            document.getElementById('run-start-loader').style.display = 'block';
            document.getElementById('run-start-content').style.display = 'none';
            
            fetch(`api/run_start/${runId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('run_start_button').style.display = 'block';
                        document.getElementById('run-start-content').innerHTML = data.data;
                    } else {
                        showError(data.error);

                        document.getElementById('run_start_button').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Connection error');
                })
                .finally(() => {
                    document.getElementById('run-start-loader').style.display = 'none';
                    document.getElementById('run-start-content').style.display = 'block';
                });
        });
    });

    const projectEditButtons = document.querySelectorAll('[data-bs-target="#projectEditModal"]');
    
    projectEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-project-id');
            const name = this.getAttribute('data-project-name');
            
            if (id == -1) title = 'Creating Project';
            else title = 'Editing Project: ' + name;
            document.getElementById('project-edit-title').textContent = title;
            document.getElementById('project-edit-id').value = id;
            document.getElementById('project-edit-name').value = name;
            document.getElementById('project-edit-project').value = project_id;
        });
    });


    const boardEditButtons = document.querySelectorAll('[data-bs-target="#boardEditModal"]');
    
    boardEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-board-id');
            const name = this.getAttribute('data-board-name');
            const comment = this.getAttribute('data-board-comment');
            const project_id = this.getAttribute('data-board-project_id');
            
            if (id == -1) title = 'Creating Board';
            else title = 'Editing Board: ' + name;

            document.getElementById('board-edit-title').textContent = title;
            document.getElementById('board-edit-id').value = id;
            document.getElementById('board-edit-name').value = name;
            document.getElementById('board-edit-comment').value = comment;
            document.getElementById('board-edit-project').value = project_id;
        });
    });


    const sectionEditButtons = document.querySelectorAll('[data-bs-target="#sectionEditModal"]');
    
    sectionEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-section-id');
            const name = this.getAttribute('data-section-name');
            const comment = this.getAttribute('data-section-comment');
            const color = this.getAttribute('data-section-color');
            const board_id = this.getAttribute('data-section-board-id');

            if (id == -1)
            {
                title = 'Creating Section';
                document.getElementById('section-edit-board_block').style.display = 'none';
            }
            else
            {
                title = 'Editing Section: ' + name;
                document.getElementById('section-edit-board_block').style.display = 'block';
            }
            
            document.getElementById('section-edit-title').textContent = title;
            document.getElementById('section-edit-id').value = id;
            document.getElementById('section-edit-board-id').value = board_id;
            document.getElementById('section-edit-name').value = name;
            document.getElementById('section-edit-comment').value = comment;
            document.getElementById('section-edit-color').value = color;
            document.getElementById('section-edit-color').style.background = 'linear-gradient(135deg, #ffffff 0%, ' + color + ' 100%)';
            document.getElementById('section-edit-board').value = board_id;
        });
    });


    const setEditButtons = document.querySelectorAll('[data-bs-target="#setEditModal"]');
    
    setEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-set-id');
            const name = this.getAttribute('data-set-name');
            const json = this.getAttribute('data-set-json');
            const parser_id = this.getAttribute('data-set-parser-id');
            const git_url = this.getAttribute('data-set-git');
            const local_artifacts = this.getAttribute('data-set-local-artifacts');
            const commandline = this.getAttribute('data-set-commandline');
            const comment = this.getAttribute('data-set-comment');

            if (id == -1) title = 'Creating Set';
            else title = 'Editing Set: ' + name;
            
            document.getElementById('set-edit-title').textContent = title;
            document.getElementById('set-edit-id').value = id;
            document.getElementById('set-edit-name').value = name;
            document.getElementById('set-edit-parser').value = parser_id;
            document.getElementById('set-edit-comment').value = comment;
            document.getElementById('set-edit-json').value = json;
            document.getElementById('set-edit-git').value = git_url;
            document.getElementById('set-edit-local-artifacts').value = local_artifacts;
            document.getElementById('set-edit-commandline').value = commandline;
        });
    });


    const machineEditButtons = document.querySelectorAll('[data-bs-target="#machineEditModal"]');
    
    machineEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-machine-id');
            const name = this.getAttribute('data-machine-name');
            const board_id = this.getAttribute('data-machine-board-id');
            const hc_ip_port = this.getAttribute('data-machine-hc');
            const json = this.getAttribute('data-machine-json');
            const comment = this.getAttribute('data-machine-comment');

            if (id == -1)
            {
                title = 'Creating Machine';
                // document.getElementById('machine-edit-board_block').style.display = 'none';
            }
            else
            {
                title = 'Editing Machine: ' + name;
                // document.getElementById('machine-edit-board_block').style.display = 'block';
            }
            
            document.getElementById('machine-edit-title').textContent = title;
            document.getElementById('machine-edit-id').value = id;
            document.getElementById('machine-edit-name').value = name;
            document.getElementById('machine-edit-board-id').value = board_id;
            document.getElementById('machine-edit-board').value = board_id;
            document.getElementById('machine-edit-hc').value = hc_ip_port;
            document.getElementById('machine-edit-json').value = json;
            document.getElementById('machine-edit-comment').value = comment;
        });
    });


    const runEditButtons = document.querySelectorAll('[data-bs-target="#runEditModal"]');

    runEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-run-id');
            const section_id = this.getAttribute('data-run-section-id');
            const name = this.getAttribute('data-run-name');
            const tags = this.getAttribute('data-run-tags');
            const comment = this.getAttribute('data-run-comment');
            const test_filter = this.getAttribute('data-run-test-filter');
            const mode = this.getAttribute('data-run-mode');
            const scout_parser_id = this.getAttribute('data-scout-parser-id');
            const scout_base_url = this.getAttribute('data-scout-base-url');
            const scout_set_filter = this.getAttribute('data-scout-set-filter');
            const executor_set_id = this.getAttribute('data-executor-set-id');
            const manual_parser_id = this.getAttribute('data-manual-parser-id');

            if (id == -1) title = 'Creating Run';
            else title = 'Editing Run: ' + name;
            
            document.getElementById('run-edit-title').textContent = title;
            document.getElementById('run-edit-id').value = id;
            document.getElementById('run-edit-name').value = name;
            document.getElementById('run-edit-section-id').value = section_id;
            document.getElementById('run-edit-tags').value = tags;
            document.getElementById('run-edit-test-filter').value = test_filter;
            document.getElementById('run-edit-comment').value = comment;

            document.getElementById('run-edit-scout-mode').checked = false;
            document.getElementById('run-edit-executor-mode').checked = false;
            document.getElementById('run-edit-manual-mode').checked = false;
            document.getElementById('run-edit-scout-data').style.display = 'none';
            document.getElementById('run-edit-executor-data').style.display = 'none';
            document.getElementById('run-edit-manual-data').style.display = 'none';

            if (mode == 0) {
                document.getElementById('run-edit-scout-mode').checked = true;
                document.getElementById('run-edit-scout-data').style.display = 'block';
            }
            if (mode == 1) {
                document.getElementById('run-edit-executor-mode').checked = true;
                document.getElementById('run-edit-executor-data').style.display = 'block';
            }
            if (mode == 2) {
                document.getElementById('run-edit-manual-mode').checked = true;
                document.getElementById('run-edit-manual-data').style.display = 'block';
            }

            document.getElementById('run-edit-scout-parser').value = scout_parser_id;
            document.getElementById('run-edit-scout-base-url').value = scout_base_url;
            document.getElementById('run-edit-scout-set-filter').value = scout_set_filter;
            document.getElementById('run-edit-executor-set').value = executor_set_id;
            document.getElementById('run-edit-executor-machine').value = executor_machine_id;
            document.getElementById('run-edit-manual-parser').value = manual_parser_id;
        });
    });
}


document.addEventListener('DOMContentLoaded', function() {
    init_buttons();
});


function section_edit_color_preview()
{
    color = document.getElementById('section-edit-color').value;
    document.getElementById('section-edit-color').style.background = 'linear-gradient(135deg, #ffffff 0%, ' + color + ' 100%)';
}

function section_edit_color_picker(color)
{
    document.getElementById('section-edit-color').value = color;
    document.getElementById('section-edit-color').style.background = 'linear-gradient(135deg, #ffffff 0%, ' + color + ' 100%)';
}


function project_edit_save(do_delete = false)
{
    const id = document.getElementById('project-edit-id').value;
    const name = document.getElementById('project-edit-name').value;

    if (!do_delete) {
        if (name.length == 0) {
            showInfo('Name may not be empty');
            return
        }

        var method = 'POST';
        if (id >= 0) method = 'PATCH';
    }
    else {
        if (!confirm('Are you sure you want to delete?'))
            return;

        var method = 'DELETE';
    }
    
    fetch(`api/project/${id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!do_delete) {
                    // document.getElementById('run-details-content').innerHTML = data.data;
                    showSuccess('Saved')
                    setTimeout('window.location.reload()', 1000);
                }
                else {
                    document.location.href = '/';
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function board_edit_save(do_delete = false)
{
    const id = document.getElementById('board-edit-id').value;
    const name = document.getElementById('board-edit-name').value;
    const comment = document.getElementById('board-edit-comment').value;
    const project_id = document.getElementById('board-edit-project').value;

    if (!do_delete) {
        if (name.length == 0) {
            showInfo('Name may not be empty');
            return
        }

        var method = 'POST';
        if (id >= 0) method = 'PATCH';
    }
    else {
        if (!confirm('Are you sure you want to delete?'))
            return;

        var method = 'DELETE';
    }
    
    fetch(`api/board/${id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                comment: comment,
                project_id: project_id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Saved')

                if (!do_delete) {
                    if (id >= 0) {
                        // document.getElementById('board_container').innerHTML = data.data;
                        setTimeout('window.location.reload()', 1000);
                    }
                    else {
                        document.location.href = '?board_id=' + data.new_id;
                    }
                }
                else {
                    document.location.href = '/';
                }
            } else {
                showError(data.error);
            }

            const boardEditModal = bootstrap.Modal.getInstance(document.getElementById('boardEditModal'));
            if (boardEditModal) {
                if (document.activeElement && document.getElementById('boardEditModal').contains(document.activeElement)) {
                    document.activeElement.blur();
                }
                boardEditModal.hide();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function board_get_data_update(id)
{   
    fetch(`api/board_update/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data) {
                    showInfo('Data updated')
                    document.getElementById('board_container').innerHTML = data.data;
                    init_buttons();
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function section_edit_save(do_delete = false)
{
    const id = document.getElementById('section-edit-id').value;
    const name = document.getElementById('section-edit-name').value;
    const comment = document.getElementById('section-edit-comment').value;
    const color = document.getElementById('section-edit-color').value;
    const board_id = document.getElementById('section-edit-board').value;

    if (!do_delete) {
        if (name.length == 0) {
            showInfo('Name may not be empty');
            return
        }

        var method = 'POST';
        if (id >= 0) method = 'PATCH';
    }
    else {
        if (!confirm('Are you sure you want to delete?'))
            return;

        var method = 'DELETE';
    }
    
    fetch(`api/section/${id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                comment: comment,
                color: color,
                board_id: board_id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Saved')

                if (!do_delete) {
                    if (id >= 0) {
                        document.getElementById('section_div_' + id).outerHTML = data.data
                    }
                    else {
                        document.getElementById('board_container').insertAdjacentHTML('beforeend', data.data);
                    }
                    init_buttons();
                }
                else {
                    document.getElementById('section_div_' + id).style.display = 'none';
                }

                const sectionEditModal = bootstrap.Modal.getInstance(document.getElementById('sectionEditModal'));
                if (sectionEditModal) {
                    if (document.activeElement && document.getElementById('sectionEditModal').contains(document.activeElement)) {
                        document.activeElement.blur();
                    }
                    sectionEditModal.hide();
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function set_edit_save(do_delete = false)
{
    const id = document.getElementById('set-edit-id').value;
    const name = document.getElementById('set-edit-name').value;
    const parser_id = document.getElementById('set-edit-parser').value;
    const comment = document.getElementById('set-edit-comment').value;
    const json = document.getElementById('set-edit-json').value;
    const git_url = document.getElementById('set-edit-git').value;
    const local_artifacts = document.getElementById('set-edit-local-artifacts').value;
    const commandline = document.getElementById('set-edit-commandline').value;

    if (!do_delete) {
        if (name.length == 0) {
            showInfo('Name may not be empty');
            return
        }

        if (!parser_id) {
            showInfo('Parser should be selected');
            return
        }

        var method = 'POST';
        if (id >= 0) method = 'PATCH';
    }
    else {
        if (!confirm('Are you sure you want to delete?'))
            return;

        var method = 'DELETE';
    }
    
    fetch(`api/set/${id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                parser_id: parser_id,
                comment: comment,
                json: json,
                git_url: git_url,
                local_artifacts: local_artifacts,
                commandline: commandline
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Saved')

                if (!do_delete) {
                    document.getElementById('sets_div').innerHTML = data.data
                    init_buttons();
                }
                else {
                    document.getElementById('set_tr_' + id).style.display = 'none';
                }

                const setEditModal = bootstrap.Modal.getInstance(document.getElementById('setEditModal'));
                if (setEditModal) {
                    if (document.activeElement && document.getElementById('setEditModal').contains(document.activeElement)) {
                        document.activeElement.blur();
                    }
                    setEditModal.hide();
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function machine_edit_save(do_delete = false)
{
    const id = document.getElementById('machine-edit-id').value;
    const name = document.getElementById('machine-edit-name').value;
    const comment = document.getElementById('machine-edit-comment').value;
    const json = document.getElementById('machine-edit-json').value;
    const hc_ip_port = document.getElementById('machine-edit-hc').value;
    const board_id = document.getElementById('machine-edit-board').value;

    if (!do_delete) {
        if (name.length == 0) {
            showInfo('Name may not be empty');
            return
        }

        if (!board_id) {
            showInfo('A board should be selected');
            return
        }

        var method = 'POST';
        if (id >= 0) method = 'PATCH';
    }
    else {
        if (!confirm('Are you sure you want to delete?'))
            return;

        var method = 'DELETE';
    }

    fetch(`api/machine/${id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                comment: comment,
                json: json,
                hc_ip_port: hc_ip_port,
                board_id: board_id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Saved')

                if (!do_delete) {
                    if (id >= 0) {
                        document.getElementById('machine_tr_' + id).outerHTML = data.data
                    }
                    else {
                        document.getElementById('machines_container').innerHTML = data.data
                        init_buttons();
                    }
                }
                else {
                    document.getElementById('machine_tr_' + id).style.display = 'none';
                }

                const setEditModal = bootstrap.Modal.getInstance(document.getElementById('machineEditModal'));
                if (setEditModal) {
                    if (document.activeElement && document.getElementById('machineEditModal').contains(document.activeElement)) {
                        document.activeElement.blur();
                    }
                    setEditModal.hide();
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function run_edit_save(do_delete = false)
{
    const id = document.getElementById('run-edit-id').value;
    const name = document.getElementById('run-edit-name').value;
    const section_id = document.getElementById('run-edit-section-id').value;
    const tags = document.getElementById('run-edit-tags').value;
    const test_filter = document.getElementById('run-edit-test-filter').value;
    const comment = document.getElementById('run-edit-comment').value;

    const scout_parser_id = document.getElementById('run-edit-scout-parser').value;
    const scout_base_url = document.getElementById('run-edit-scout-base-url').value;
    const scout_set_filter = document.getElementById('run-edit-scout-set-filter').value;
    const executor_set_id = document.getElementById('run-edit-executor-set').value;
    const executor_machine_id = document.getElementById('run-edit-executor-machine').value;
    const manual_parser_id = document.getElementById('run-edit-manual-parser').value;

    var mode = 0;
    if (document.getElementById('run-edit-scout-mode').checked) mode = 0;
    if (document.getElementById('run-edit-executor-mode').checked) mode = 1;
    if (document.getElementById('run-edit-manual-mode').checked) mode = 2;
    
    if (!do_delete) {
        if (name.length == 0) {
            showInfo('Name may not be empty');
            return
        }

        if (mode == 1 && !executor_set_id) {
            showInfo('Set to Run may not be empty');
            return
        }

        var method = 'POST';
        if (id >= 0) method = 'PATCH';
    }
    else {
        if (!confirm('Are you sure you want to delete?'))
            return;

        var method = 'DELETE';
    }
    
    fetch(`api/run/${id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                section_id: section_id,
                tags: tags,
                test_filter: test_filter,
                comment: comment,
                mode: mode,
                scout_parser_id: scout_parser_id,
                scout_base_url: scout_base_url,
                scout_set_filter: scout_set_filter,
                executor_set_id: executor_set_id,
                executor_machine_id: executor_machine_id,
                manual_parser_id: manual_parser_id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Saved')
                if (!do_delete) {
                    if (id >= 0) {
                        document.getElementById('run_tr_' + id).outerHTML = data.data
                    }
                    else {
                        document.getElementById('section_div_' + section_id).outerHTML = data.data
                    }
                    init_buttons();
                }
                else {
                    document.getElementById('run_tr_' + id).style.display = 'none';
                }

                const runEditModal = bootstrap.Modal.getInstance(document.getElementById('runEditModal'));
                if (runEditModal) {
                    if (document.activeElement && document.getElementById('runEditModal').contains(document.activeElement)) {
                        document.activeElement.blur();
                    }
                    runEditModal.hide();
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function run_start()
{
    const run_id = document.getElementById('run-start-run-id').value;
    const set_id = document.getElementById('run-start-set-id').value;
    const machine_id = document.getElementById('run-start-machine-id').value;
    const parser_id = document.getElementById('run-start-parser-id').value;

    const set_git_url = document.getElementById('run-start-set-git').value;
    const set_local_artifacts = document.getElementById('run-start-set-local-artifacts').value;
    const set_commandline = document.getElementById('run-start-set-commandline').value;
    const set_json = document.getElementById('run-start-set-json').value;
    const machine_json = document.getElementById('run-start-machine-json').value;

    var method = 'POST';
    
    fetch(`api/run_start/${run_id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                set_id: set_id,
                machine_id: machine_id,
                parser_id: parser_id,
                set_git_url: set_git_url,
                set_local_artifacts: set_local_artifacts,
                set_commandline: set_commandline,
                set_json: set_json,
                machine_json: machine_json
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Started')
                document.getElementById('run_tr_' + run_id).outerHTML = data.data;
                init_buttons();

                const runStartModal = bootstrap.Modal.getInstance(document.getElementById('runStartModal'));
                if (runStartModal) {
                    if (document.activeElement && document.getElementById('runStartModal').contains(document.activeElement)) {
                        document.activeElement.blur();
                    }
                    runStartModal.hide();
                }
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


function run_stop(run_id)
{
    if (confirm('Are you sure you want to stop the run?')) {
        var method = 'POST';
        
        fetch(`api/run_stop/${run_id}`,
            {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showInfo('Stopping shedulled...')
                    document.getElementById('run_progress_tr_' + run_id)?.remove();
                    document.getElementById('run_tr_' + run_id).outerHTML = data.data;
                    init_buttons();
                } else {
                    showError(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Connection error');
            })
    }
}


function run_refresh(run_id)
{
    var method = 'POST';
    
    fetch(`api/run_refresh/${run_id}`,
        {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showInfo('Results refresh shedulled...')
            } else {
                showError(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Connection error');
        })
}


let runChart;
function render_chart(chartData)
{
    const ctx = document.getElementById('runChart').getContext('2d');
    
    runChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.start_times,
            datasets: [
                {
                    label: 'Pass Rate (%)',
                    data: chartData.passRates,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y-passrate'
                },
                {
                    label: 'Tests Number',
                    data: chartData.testCounts,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y-count'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            onClick: (event, elements, chart) => {
                if (elements.length > 0) {
                    const firstPoint = elements[0];
                    const index = firstPoint.index;
                    const id = chartData.ids[index];
                    window.open("/api/result/" + id, "_blank");
                }
            },
            transitions: {
                zoom: {
                    animation: {
                        duration: 10 // in ms
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#f1f5f9',
                    bodyColor: '#cbd5e1',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label.includes('Pass')) {
                                label += context.raw.toFixed(1) + '%';
                            } else {
                                label += context.raw + ' tests';
                            }
                            return label;
                        }
                    }
                },

                zoom: {
                    zoom: {
                        wheel: {
                            enabled: true,
                            speed: 0.05
                        },
                        drag: {
                            enabled: true,
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: '#3b82f6',
                            borderWidth: 2
                        },
                        pinch: {
                            enabled: true
                        },
                        mode: 'x'
                    },
                    limits: {
                        x: {
                            minRange: 10,
                            min: 'original',
                            max: 'original'
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: true,
                        color: '#e2e8f0'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 30,
                        font: {
                            size: 11,
                            family: "'Inter', sans-serif"
                        },
                        color: '#64748b'
                    },
                    offset: true
                },
                'y-passrate': {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    min: 0,
                    max: 105,
                    grid: {
                        color: '#e2e8f0',
                        drawBorder: true
                    },
                    ticks: {
                        stepSize: 5,
                        callback: function(value) {
                            return value + '%';
                        },
                        font: {
                            size: 11,
                            family: "'Inter', sans-serif"
                        },
                        color: '#10b981'
                    },
                    title: {
                        display: true,
                        text: 'Pass Rate (%)',
                        color: '#10b981',
                        font: {
                            size: 11,
                            weight: '500',
                            family: "'Inter', sans-serif"
                        }
                    }
                },
                'y-count': {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    min: 0,
                    grid: {
                        display: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value;
                        },
                        font: {
                            size: 11,
                            family: "'Inter', sans-serif"
                        },
                        color: '#3b82f6'
                    },
                    title: {
                        display: true,
                        text: 'Tests Number',
                        color: '#3b82f6',
                        font: {
                            size: 11,
                            weight: '500',
                            family: "'Inter', sans-serif"
                        }
                    }
                }
            }
        }
    });

    runChart.zoomScale(
        'x',
        {min: 0, max: 20},
        'default'
    );
    if (chartData.passRates.length > 20) {
        runChart.pan(
            {x: -1000000},
            undefined,
            'default'
        );
    }
}