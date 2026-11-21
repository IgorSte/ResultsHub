CREATE table "results" (
    "id" serial primary key,
    "name" text,
    "parser_id" int,
    "is_stored_locally" boolean default false,

    "build_id" int,
    "scout_base_url" text,
    "full_url" text,
    "set" text default null,
    "machine" varchar(255) default null,

    "start_time" timestamptz,
    "duration_sec" int,
    
    "tests_total" int,
    "tests_passed" int,
    "tests_skipped" int,
    "tests_failed" int,

    "execution_log" text,
    "additional_data" text,

    "time" timestamptz default current_timestamp
);

INSERT into "results" ("name", "parser_id", "scout_base_url", "full_url", "start_time", "duration_sec", "tests_total", "tests_passed", "tests_failed", "tests_skipped") values
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/100/', '2026-03-11 14:30:00', 60, 3, 2, 1, 0),
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/100/', '2026-03-11 14:30:00', 60, 3, 2, 1, 0),
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/100/', '2026-03-11 14:30:00', 60, 3, 2, 1, 0),
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/100/', '2026-03-12 14:30:00', 60, 3, 2, 1, 0),
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/100/', '2026-03-13 14:30:00', 60, 3, 2, 1, 0),
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/100/', '2026-03-14 14:30:00', 500, 10, 8, 2, 0),
    ('Demo test run', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/101/', '2026-03-15 14:30:00', 5000, 15, 14, 1, 0),
    ('Demo test run 2', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/10/', '2026-03-15 14:30:00', 510, 10, 9, 1, 0),
    ('Demo test run 2', 0, 'http://jenkins.local/Demo/', 'http://jenkins.local/Demo/10/', '2026-03-15 14:30:00', 1357, 10, 9, 0, 1);


CREATE table "associations" (
    "id" serial primary key,
    "result_id" int,
    "run_id" int,
    foreign key (result_id) references results(id) on delete cascade on update cascade
);
CREATE index run_id_index on associations (run_id);

INSERT into "associations" ("result_id", "run_id") values
    (1, 1),
    (2, 1),
    (3, 1),
    (4, 1),
    (5, 1),
    (6, 1),
    (7, 1),
    (8, 1),
    (9, 2);


CREATE table "tests" (
    "id" serial primary key,
    "result_id" int,

    "identifier" varchar(255), -- unique name/id to identify the test across the runs
    "priority" int default 2, -- 0: Smoke, 1: P1, 2: usual
    "tags" text default null,
    "name" text,
    "description" text default null,
    "script_path" text default null, -- test-case script path
    "state" int, -- 0: failed, 1: passed, 2: skipped, -1: unknown
    "full_url" text,
    "failure_reason" text default null,
    "duration_sec" int,
    foreign key (result_id) references results(id) on delete cascade on update cascade
);
CREATE index identifier_index on tests (identifier);
CREATE index result_id_index on tests (result_id);

INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec") values 
    ('test1', 1, 'Test one', 1, 10),
    ('test2', 1, 'Test two', 0, 11),
    ('test3', 1, 'Test three', 1, 10),
    ('test1', 2, 'Test one', 1, 10),
    ('test2', 2, 'Test two', 0, 11),
    ('test3', 2, 'Test three', 1, 10),
    ('test1', 3, 'Test one', 1, 10),
    ('test2', 3, 'Test two', 0, 11),
    ('test3', 3, 'Test three', 1, 10),
    ('test1', 4, 'Test one', 1, 10),
    ('test2', 4, 'Test two', 0, 11),
    ('test3', 4, 'Test three', 1, 10),
    ('test1', 5, 'Test one', 1, 10),
    ('test2', 5, 'Test two', 0, 11),
    ('test3', 5, 'Test three', 1, 10),
    ('test1', 6, 'Test one', 1, 10),
    ('test2', 6, 'Test two', 1, 11),
    ('test3', 6, 'Test three', 1, 10),
    ('test4', 6, 'Test four', 1, 5),
    ('test5', 6, 'Test five', 1, 10),
    ('test6', 6, 'Test six', 1, 8),
    ('test7', 6, 'Test seven', 1, 125),
    ('test8', 6, 'Test eight', 0, 10),
    ('test9', 6, 'Test nine', 0, 10),
    ('test10', 6, 'Test ten', 1, 10);

INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec", "tags", "priority") values ('test1', 7, 'Test one', 1, 10, 'important,basic', 0);
INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec") values ('test2', 7, 'Test two', 1, 11);
INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec", "priority") values ('test3', 7, 'Test three', 1, 10, 1);

INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec") values
    ('test4', 7, 'Test four', 1, 5),
    ('test5', 7, 'Test five', 1, 10),
    ('test6', 7, 'Test six', 1, 8),
    ('test7', 7, 'Test seven', 1, 125),
    ('test8', 7, 'Test eight', 1, 10),
    ('test9', 7, 'Test nine', 0, 10),
    ('test10', 7, 'Test ten', 1, 10),
    ('test11', 7, 'Test eleven', 1, 10),
    ('test12', 7, 'Test twelve', 1, 10),
    ('test13', 7, 'Test thirteen', 1, 10),
    ('test14', 7, 'Test forteen', 1, 10),
    ('test15', 7, 'Test fifteen', 1, 10);

INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec", "tags", "priority") values ('test1', 8, 'Test one', 1, 10, 'important,basic', 0);

INSERT into "tests" ("identifier", "result_id", "name", "state", "duration_sec") values 
    ('test2', 8, 'Test two', 1, 11),
    ('test3', 8, 'Test three', 1, 10),
    ('test4', 8, 'Test four', 1, 5),
    ('test5', 8, 'Test five', 1, 10),
    ('test6', 8, 'Test six', 1, 8),
    ('test7', 8, 'Test seven', 1, 125),
    ('test8', 8, 'Test eight', 0, 10),
    ('test9', 8, 'Test nine', 1, 10),
    ('test10', 8, 'Test ten', 1, 10);