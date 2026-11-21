CREATE table "projects" (
    "id" serial primary key,
    "name" varchar(255)
);
INSERT into "projects" ("name") values ('Demo Project');


CREATE table "boards" (
    "id" serial primary key,
    "name" varchar(255),
    "project_id" int default 0,
    "icon" varchar(255) default 'grid-1x2',
    "comment" text default null,
    "is_archived" boolean default false -- is ignored by scout
);
INSERT into "boards" ("name", "comment", "project_id") values 
    ('Demo Board', 'HTML is <b>supported</b> in the comments.', 1),
    ('Board two', 'Some long text that may describe the<br> <b>dashboards</b>', 1),
    ('Board three', 'Some long text that may describe the<br> <b>dashboards</b>', 1);


CREATE table "sections" (
    "id" serial primary key,
    "board_id" int,
    "name" varchar(255),
    "comment" text default null,
    "color" varchar(255) default '#f8faff',
    "sort_order" int default 0,
    "is_archived" boolean default false, -- is ignored by scout
    foreign key (board_id) references boards(id) on delete restrict on update cascade
);
INSERT into "sections" ("board_id", "name", "comment", "color", "sort_order") values
    (1, 'Demo Section', 'The demo test cases', '#ffffaa', 0),
    (1, 'Demo two', 'some comment', '#cbffcb', 1);


CREATE table "executors" (
    "id" serial primary key,
    "state" int default null, -- 1 - queued, 2 - starting, 3 - running, 4 - processing, 5 - done
    "run_id" int,
    "set_id" int,
    "machine_id" int,
    "parser_id" int,
    "commandline" text default null,
    "git_url" text default null,
    "local_artifacts" text default null,
    "set_json" text default null,
    "machine_json" text default null,
    "pid" int,
    "working_dir" text default null,
    "log_file_path" text default null,
    "approximate_duration" int default null,
    "start_time" timestamptz default current_timestamp,
    "finish_time" timestamptz
);


CREATE table "runs" (
    "id" serial primary key,
    "section_id" int,
    "name" varchar(255),
    "tags" varchar(255),
    "comment" text default null,
    "test_filter" varchar(255) default null, -- to filter by the test
    
    "mode" int default 0, -- 0: scout, 1: executor, 2: manual upload

    -- scout mode
    "scout_parser_id" int default 1, -- parser to be used
    "scout_base_url" text default null,
    "scout_set_filter" varchar(255) default null,
    "scout_last_build_checked" int default 0, -- autopopulated
    "scout_run_in_progress" boolean default false, -- autopopulated
    "scout_run_in_progress_url" text default null, -- autopopulated

    -- executor mode
    "executor_set_id" int default null,
    "executor_id" int default null, -- autopopulated

    -- manual mode
    "manual_parser_id" int default 2, -- parser to be used

    "sort_order" int default 0,
    foreign key (section_id) references sections(id) on delete restrict on update cascade,
    foreign key (executor_id) references executors(id) on delete set null on update cascade
);
INSERT into "runs" ("section_id", "name", "tags", "comment") values
    (1, 'Demo Run', 'tag one,two', 'some comment'),
    (1, 'Demo Smoke', 'tag one,two', 'some comment'),
    (2, 'Demo Two', 'tag one,two', 'some comment');


CREATE table "parsers" (
    "id" serial primary key,
    "name" varchar(255),
    "is_local" boolean default false,
    "class_name" varchar(255),
    "credentials_name" varchar(255)
);
INSERT into "parsers" ("name", "is_local", "class_name", "credentials_name") values
    ('Allure TestOps', false, 'AllureTestOps', 'allure_testops'),
    ('Allure local', true, 'AllureLocal', '');


CREATE table "users" (
    "id" serial primary key,

    "full_name" varchar(255) default null,
    "avatar" bytea default null,
    "email" varchar(255),
    "password" varchar(255) default null,

    "yandex_id" varchar(255) default null,
    "google_id" varchar(255) default null,
    
    "register_time" timestamptz default current_timestamp,
    "login_time" timestamptz default current_timestamp
);
CREATE index email_index on users (email);
CREATE index yandex_id_index on users (yandex_id);
CREATE index google_id_index on users (google_id);


CREATE table "scout" (
    "id" int primary key,
    "start_time" timestamptz default current_timestamp,
    "end_time" timestamptz default current_timestamp
);
INSERT into "scout" values (0, '2026-03-08 14:30:00', '2026-03-08 14:31:00');


CREATE table "machines" (
    "id" serial primary key,
    "name" varchar(255),
    "board_id" int,
    "hc_ip_port" text default null,
    "json" text default null,
    "comment" text default null,

    "status" int default 0, -- 0 - unknown, 1 - up, 2 - down
    "executor_id" int default null,

    foreign key (board_id) references boards(id) on delete restrict on update cascade,
    foreign key (executor_id) references executors(id) on delete set null on update cascade
);
INSERT into "machines" ("name", "board_id", "hc_ip_port", "comment") values 
    ('Machine 1', 1, 'localhost:22', 'A machine placeholder'),
    ('Machine 2', 1, 'localhost:8000', 'A machine placeholder'),
    ('Machine 3', 2, 'localhost:8000', 'A machine placeholder');


CREATE table "sets" (
    "id" serial primary key,
    "name" varchar(255),
    "git_url" text default null,
    "local_artifacts" text default null,
    "commandline" text default null,
    "json" text default null,
    "parser_id" int default 2,
    "comment" text default null
);
INSERT into "sets" ("name", "git_url", "local_artifacts", "comment", "commandline") values
    ('Set 1', '', './local_artifacts/test_one.py', 'Default set', 
        'poetry run python3 -m pytest test_one.py -v --capture=tee-sys --alluredir ./allure-results --clean-alluredir'),
    ('Set 2', 'https://github.com/IgorSte/ResultsHub', '', 'Set 2', '');
