<div class="modal fade" id="runDetailsModal" tabindex="-1" aria-labelledby="runDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="runDetailsModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="run-details-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Main body -->
            <div id="run-details-content" class="modal-body p-4"></div>

            <!-- Empty modal placeholder -->
            <div id="run-details-loader" class="skeleton-table-loader p-3">

            <!-- Table header -->
            <div class="skeleton-header-row">
                <div class="skeleton-cell skeleton-label" style='background: none'></div>
                <?php for ($ii = 0; $ii < 5; $ii++) { ?>
                    <div class="skeleton-cell skeleton-value"></div>
                <?php } ?>
            </div>

            <?php for ($i = 0; $i < 5; $i++) { ?>
                <div class="skeleton-row">
                    <div class="skeleton-cell skeleton-label"></div>
                    <?php for ($ii = 0; $ii < 5; $ii++) { ?>
                        <div class="skeleton-cell skeleton-value"></div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-primary rounded-pill px-4" id="viewFullReportBtn">
                    <i class="bi bi-download me-2"></i>CSV
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="runChartModal" tabindex="-1" aria-labelledby="runChartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="runChartModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="run-chart-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Тело модального окна -->
            <div id="run-chart-content" class="modal-body p-4">

                <div class="run-chart-container">
                    <div class="chart-header">
                        <!-- <h5 class="chart-title">
                            <i class="bi bi-graph-up me-2"></i>
                            История тестовых прогонов
                        </h5> -->
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-line pass-rate"></span>
                                <span>Pass Rate (%)</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-line test-count"></span>
                                <span>Tests Number</span>
                            </div>
                        </div>
                    </div>

                    <div class="chart-wrapper">
                        <canvas id="runChart" width="800" height="300"></canvas>
                    </div>

                    <div class="chart-footer">
                        <!-- <span class="footer-comment">⏱️ Последние 15 прогонов</span>
                        <span class="footer-comment">📊 Усреднённые данные</span> -->
                    </div>
                </div>

            </div>

            <!-- В модальном окне, вместо пустого контента -->
            <div id="run-chart-loader" class="skeleton-table-loader p-3">
                Loading...
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="projectEditModal" tabindex="-1" aria-labelledby="projectEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="projectEditModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="project-edit-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="project-edit-content" class="modal-body p-4">
                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Project Name
                    </label>
                    <input type="text" 
                            class="form-control form-control-lg rundetails-input" 
                            id="project-edit-name" 
                            name="name"
                            placeholder="Enter the project's name...">
                </div>

                 <input type="text" id="project-edit-id" style="display: none">
            </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-auto" onclick="project_edit_save(true)">
                    <i class="bi bi-x-lg me-2"></i>Delete
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="project_edit_save" onclick="project_edit_save()">
                    <i class="bi bi-disc me-2"></i>Save
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="boardEditModal" tabindex="-1" aria-labelledby="boardEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="boardEditModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="board-edit-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="board-edit-content" class="modal-body p-4">
                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Board Name
                    </label>
                    <input type="text" 
                            class="form-control form-control-lg rundetails-input" 
                            id="board-edit-name" 
                            name="name"
                            placeholder="Enter the board's name...">
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        Board Comment
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="board-edit-comment" 
                                name="comment"
                                rows="4"
                                placeholder="Enter description of the board..."></textarea>
                    <div class="form-hint">HTML tags are allowed</div>
                </div>

                <!-- Dropdown-list -->
                <div class="form-group mb-4">
                    <label for="record-category" class="form-label">
                        <i class="bi bi-list-ul me-2"></i>
                        Project
                    </label>
                    <select class="form-select rundetails-select" id="board-edit-project" name="category">
                    <?php
                        $projects = $db->get_all('SELECT * from "projects" order by "name" asc');

                        foreach ($projects as $project)
                        {
                            echo '<option value="'.$project['id'].'">'.$project['name'].'</option>';
                        }
                    ?>
                    </select>
                </div>

                 <input type="text" id="board-edit-id" style="display: none">
            </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-auto" onclick="board_edit_save(true)">
                    <i class="bi bi-x-lg me-2"></i>Delete
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="board_edit_save" onclick="board_edit_save()">
                    <i class="bi bi-disc me-2"></i>Save
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sectionEditModal" tabindex="-1" aria-labelledby="sectionEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="sectionEditModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="section-edit-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="section-edit-content" class="modal-body p-4">
                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Section Name
                    </label>
                    <input type="text" 
                            class="form-control form-control-lg rundetails-input" 
                            id="section-edit-name" 
                            name="name"
                            placeholder="Enter the section's name...">
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        Section Comment
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="section-edit-comment" 
                                name="comment"
                                rows="4"
                                placeholder="Enter description of the secion..."></textarea>
                    <div class="form-hint">HTML tags are allowed</div>
                </div>

                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Header Color
                    </label>
                    <input type="text" 
                            class="form-control form-control rundetails-input" 
                            id="section-edit-color" 
                            name="color"
                            onkeyup="section_edit_color_preview()">
                    <div class='color-picker-block' >
                        <?php
                            $colors = ['#f8faff', '#afffaa', '#fffcaa', '#ffaaaa', '#aaffff'];

                            foreach ($colors as $color)
                            {
                                echo "<span onclick='section_edit_color_picker(\"$color\")' class='color-picker' style='background: linear-gradient(135deg, #ffffff 0%, $color 100%);'>$color</span></option>";
                            }
                        ?>
                    </div>
                </div>

                <!-- Dropdown-list -->
                <div class="form-group mb-4" id='section-edit-board_block'>
                    <label for="record-category" class="form-label">
                        <i class="bi bi-list-ul me-2"></i>
                        Board
                    </label>
                    <select class="form-select rundetails-select" id="section-edit-board" name="category">
                    <?php
                        $boards = $db->get_all('SELECT b.*, p.name as project_name from "boards" as b, "projects" as p where b.project_id = p.id order by b.name asc');

                        foreach ($boards as $board)
                        {
                            echo '<option value="'.$board['id'].'">'.$board['project_name'].' / '.$board['name'].'</option>';
                        }
                    ?>
                    </select>
                </div>

                <input type="text" id="section-edit-id" style="display: none">
                <input type="text" id="section-edit-board-id" style="display: none">
            </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-auto" onclick="section_edit_save(true)">
                    <i class="bi bi-x-lg me-2"></i>Delete
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="board_edit_save" onclick="section_edit_save()">
                    <i class="bi bi-disc me-2"></i>Save
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="setEditModal" tabindex="-1" aria-labelledby="setEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="setEditModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="set-edit-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="set-edit-content" class="modal-body p-4">
                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Set Name
                    </label>
                    <input type="text" 
                            class="form-control form-control-lg rundetails-input" 
                            id="set-edit-name" 
                            name="name"
                            placeholder="Enter the set's name...">
                </div>

                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Git URL
                    </label>
                    <input type="text" 
                            class="form-control form-control rundetails-input" 
                            id="set-edit-git" 
                            name="name"
                            placeholder="Enter the set's Git URL...">
                </div>

                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Local Artifacts URL
                    </label>
                    <input type="text" 
                            class="form-control form-control rundetails-input" 
                            id="set-edit-local-artifacts" 
                            name="name"
                            placeholder="Enter the path to local test artifacts...">
                </div>

                <!-- Dropdown-list -->
                <div class="form-group mb-4">
                    <label for="record-category" class="form-label">
                        <i class="bi bi-list-ul me-2"></i>
                        Parser for the results
                    </label>
                    <select class="form-select rundetails-select" id="set-edit-parser" name="category">
                    <?php
                        $parsers = $db->get_all('SELECT * from "parsers" where is_local = true order by name asc');

                        foreach ($parsers as $parser)
                        {
                            echo '<option value="'.$parser['id'].'">'.$parser['name'].'</option>';
                        }
                    ?>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        Set Commandline
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="set-edit-commandline" 
                                name="comment"
                                rows="2"
                                placeholder="Commandline to start the set..."></textarea>
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        set.json
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="set-edit-json" 
                                name="comment"
                                rows="7"
                                placeholder="Enter content of the set.json file..."></textarea>
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        Set Comment
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="set-edit-comment" 
                                name="comment"
                                rows="4"
                                placeholder="Enter description of the secion..."></textarea>
                    <div class="form-hint">HTML tags are allowed</div>
                </div>

                <input type="text" id="set-edit-id" style="display: none">
                <input type="text" id="set-edit-board-id" style="display: none">
            </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-auto" onclick="set_edit_save(true)">
                    <i class="bi bi-x-lg me-2"></i>Delete
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="board_edit_save" onclick="set_edit_save()">
                    <i class="bi bi-disc me-2"></i>Save
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="machineEditModal" tabindex="-1" aria-labelledby="machineEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="machineEditModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="machine-edit-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="machine-edit-content" class="modal-body p-4">
                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Machine Name
                    </label>
                    <input type="text" 
                            class="form-control form-control-lg rundetails-input" 
                            id="machine-edit-name" 
                            name="name"
                            placeholder="Enter the machine's name...">
                </div>

                <!-- Dropdown-list -->
                <div class="form-group mb-4" id='machine-edit-board_block'>
                    <label for="record-category" class="form-label">
                        <i class="bi bi-list-ul me-2"></i>
                        Board
                    </label>
                    <select class="form-select rundetails-select" id="machine-edit-board" name="category">
                    <?php
                        $boards = $db->get_all('SELECT b.*, p.name as project_name from "boards" as b, "projects" as p where b.project_id = p.id order by b.name asc');

                        foreach ($boards as $board)
                        {
                            echo '<option value="'.$board['id'].'">'.$board['project_name'].' / '.$board['name'].'</option>';
                        }
                    ?>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Healthcheck IP:port list (csv)
                    </label>
                    <input type="text" 
                            class="form-control form-control rundetails-input" 
                            id="machine-edit-hc" 
                            name="name"
                            placeholder="CSV of the IP:port pairs to be used for healthcheck.">
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        machine.json
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="machine-edit-json" 
                                name="comment"
                                rows="7"
                                placeholder="Enter content of the machine.json file..."></textarea>
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        machine Comment
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="machine-edit-comment" 
                                name="comment"
                                rows="4"
                                placeholder="Enter description of the secion..."></textarea>
                    <div class="form-hint">HTML tags are allowed</div>
                </div>

                <input type="text" id="machine-edit-id" style="display: none">
                <input type="text" id="machine-edit-board-id" style="display: none">
            </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-auto" onclick="machine_edit_save(true)">
                    <i class="bi bi-x-lg me-2"></i>Delete
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="board_edit_save" onclick="machine_edit_save()">
                    <i class="bi bi-disc me-2"></i>Save
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>                
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="runEditModal" tabindex="-1" aria-labelledby="runEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="runEditModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="run-edit-title"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div id="run-edit-content" class="modal-body p-4">
                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Run Name
                    </label>
                    <input type="text" 
                            class="form-control form-control-lg rundetails-input" 
                            id="run-edit-name" 
                            name="name"
                            placeholder="Enter the run's name...">
                </div>

                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Tags
                    </label>
                    <input type="text" 
                            class="form-control form-control rundetails-input" 
                            id="run-edit-tags"
                            name="name"
                            placeholder="Tags, space or comman separated...">
                </div>

                <div class="form-group mb-4">
                    <label for="record-name" class="form-label">
                        <i class="bi bi-tag me-2"></i>
                        Test filter
                    </label>
                    <input type="text" 
                            class="form-control form-control rundetails-input" 
                            id="run-edit-test-filter"
                            name="name"
                            placeholder="Regxep filter for the test names to be counted.">
                </div>

                <div class="form-group mb-4">
                    <label for="record-comment" class="form-label">
                        <i class="bi bi-chat me-2"></i>
                        Run Comment
                    </label>
                    <textarea class="form-control rundetails-textarea" 
                                id="run-edit-comment" 
                                name="comment"
                                rows="4"
                                placeholder="Enter description of the secion..."></textarea>
                    <div class="form-hint">HTML tags are allowed</div>
                </div>

                <div class="btn-group mb-4" role="group" aria-label="Status">
                    <input type="radio" class="btn-check" name="status" id="run-edit-scout-mode" value="true" autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="run-edit-scout-mode" onclick="
                        document.getElementById('run-edit-scout-data').style.display = 'block';
                        document.getElementById('run-edit-executor-data').style.display = 'none';
                        document.getElementById('run-edit-manual-data').style.display = 'none';
                    ">
                        <i class="bi me-1"></i>Scout Mode
                    </label>

                    <input type="radio" class="btn-check" name="status" id="run-edit-executor-mode" value="false" autocomplete="off">
                    <label class="btn btn-outline-success" for="run-edit-executor-mode" onclick="
                        document.getElementById('run-edit-scout-data').style.display = 'none';
                        document.getElementById('run-edit-executor-data').style.display = 'block';
                        document.getElementById('run-edit-manual-data').style.display = 'none';
                    ">
                        <i class="bi me-1"></i>Executor Mode
                    </label>

                    <input type="radio" class="btn-check" name="status" id="run-edit-manual-mode" value="false" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="run-edit-manual-mode" onclick="
                        document.getElementById('run-edit-scout-data').style.display = 'none';
                        document.getElementById('run-edit-executor-data').style.display = 'none';
                        document.getElementById('run-edit-manual-data').style.display = 'block';
                    ">
                        <i class="bi me-1"></i>Manual Upload
                    </label>
                </div>

                <div id="run-edit-scout-data">
                    <!-- Dropdown-list -->
                    <div class="form-group mb-4" id='run-edit-scout-parser'>
                        <label for="record-category" class="form-label">
                            <i class="bi bi-list-ul me-2"></i>
                            Parser for the results
                        </label>
                        <select class="form-select rundetails-select" id="run-edit-scout-parser" name="category">
                        <?php
                            $parsers = $db->get_all('SELECT * from "parsers" where is_local = false order by name asc');

                            foreach ($parsers as $parser)
                            {
                                echo '<option value="'.$parser['id'].'">'.$parser['name'].'</option>';
                            }
                        ?>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="record-name" class="form-label">
                            <i class="bi bi-tag me-2"></i>
                            Base URL
                        </label>
                        <input type="text" 
                                class="form-control form-control rundetails-input" 
                                id="run-edit-scout-base-url"
                                name="name"
                                placeholder="Base URL to get the results from.">
                    </div>

                    <div class="form-group mb-4">
                        <label for="record-name" class="form-label">
                            <i class="bi bi-tag me-2"></i>
                            Set Filter
                        </label>
                        <input type="text" 
                                class="form-control form-control rundetails-input" 
                                id="run-edit-scout-set-filter"
                                name="name"
                                placeholder="Filter for the results.">
                    </div>
                </div>

                <div id="run-edit-executor-data" style="display: none">
                    <!-- Dropdown-list -->
                    <div class="form-group mb-4">
                        <label for="record-category" class="form-label">
                            <i class="bi bi-list-ul me-2"></i>
                            Set to Run
                        </label>
                        <select class="form-select rundetails-select" id="run-edit-executor-set" name="category">
                        <?php
                            $sets = $db->get_all('SELECT * from "sets" order by name asc');

                            foreach ($sets as $set)
                            {
                                echo '<option value="'.$set['id'].'">'.$set['name'].'</option>';
                            }
                        ?>
                        </select>
                    </div>
                </div>

                <div id="run-edit-manual-data" style="display: none">
                    <!-- Dropdown-list -->
                    <div class="form-group mb-4" id='run-edit-manual-parser'>
                        <label for="record-category" class="form-label">
                            <i class="bi bi-list-ul me-2"></i>
                            Parser for the results
                        </label>
                        <select class="form-select rundetails-select" id="run-edit-manual-parser" name="category">
                        <?php
                            $parsers = $db->get_all('SELECT * from "parsers" where is_local = true order by name asc');

                            foreach ($parsers as $parser)
                            {
                                echo '<option value="'.$parser['id'].'">'.$parser['name'].'</option>';
                            }
                        ?>
                        </select>
                    </div>
                </div>

                <input type="text" id="run-edit-id" style="display: none">
                <input type="text" id="run-edit-section-id" style="display: none">
            </div>
            
            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-auto" onclick="run_edit_save(true)">
                    <i class="bi bi-x-lg me-2"></i>Delete
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="board_edit_save" onclick="run_edit_save()">
                    <i class="bi bi-disc me-2"></i>Save
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>            
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="runStartModal" tabindex="-1" aria-labelledby="runStartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            
            <!-- Header with gradient -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f8faff, #ffffff); border-bottom: 1px solid rgba(0,0,0,0.03); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="runStartModalLabel">
                    <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
                    <span class="fw-semibold fs-5" id="run-start-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Тело модального окна -->
            <div id="run-start-content" class="modal-body p-4">


            </div>

            <!-- Footer with buttons -->
            <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.03); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-success rounded-pill px-4" id="run_start_button" onclick="run_start()">
                    <i class="bi bi-play me-2"></i>Start
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>            
            </div>

            <!-- В модальном окне, вместо пустого контента -->
            <div id="run-start-loader" class="skeleton-table-loader p-3">
                Loading...
            </div>
        </div>
    </div>
</div>