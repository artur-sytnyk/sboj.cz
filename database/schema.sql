PRAGMA foreign_keys = ON;

CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_type INTEGER NOT NULL CHECK (user_type IN (1, 2, 3)),
    active INTEGER NOT NULL DEFAULT 1,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    fullname TEXT NOT NULL,
    birth_date TEXT,
    specialization TEXT NOT NULL DEFAULT '',
    description TEXT,
    file_path TEXT NOT NULL DEFAULT ''
);

CREATE TABLE job (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_owner INTEGER NOT NULL,
    company_name TEXT NOT NULL,
    title TEXT NOT NULL,
    address TEXT NOT NULL,
    description TEXT NOT NULL,
    benefits TEXT,
    logo_path TEXT NOT NULL DEFAULT '',
    homepage_link TEXT,
    FOREIGN KEY (id_owner) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE rel_user_job (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_user INTEGER NOT NULL,
    id_job INTEGER NOT NULL,
    UNIQUE (id_user, id_job),
    FOREIGN KEY (id_user) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_job) REFERENCES job(id) ON DELETE CASCADE
);

CREATE INDEX idx_job_owner ON job(id_owner);
CREATE INDEX idx_rel_user_job_job ON rel_user_job(id_job);

INSERT INTO user (
    user_type, active, username, password, email, fullname,
    birth_date, specialization, description, file_path
) VALUES (
    2, 1, 'demo-company',
    '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
    'demo@example.test', 'Demo Company', NULL,
    'Software and web development',
    'Local demonstration account for the Sboj.cz application.', ''
);

INSERT INTO job (
    id_owner, company_name, title, address, description,
    benefits, logo_path, homepage_link
) VALUES
    (1, 'Demo Company', 'Junior PHP Developer', 'Prague',
     'Help build and maintain PHP web applications.',
     'Flexible hours, mentoring, hybrid work', '', 'https://www.example.com/php'),
    (1, 'Demo Company', 'Frontend Developer', 'Brno',
     'Create accessible and responsive user interfaces.',
     'Training budget, flexible hours', '', 'https://www.example.com/frontend');
